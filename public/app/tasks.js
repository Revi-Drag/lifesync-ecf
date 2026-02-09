const meEl = document.getElementById("me");
const tasksEl = document.getElementById("tasks");
const listMsg = document.getElementById("listMsg");
const refreshBtn = document.getElementById("refreshBtn");

const createForm = document.getElementById("createForm");
const createMsg = document.getElementById("createMsg");


function formatDate(iso) {
    if (!iso) return "-";
    return new Date(iso).toLocaleString();
}

function renderUserLine(label, user, date) {
    if (!user && !date) return "";
    return `
      <div class="task-userline muted">
        <strong>${label}</strong>
        ${user ?? "-"}
        ${date ? `— ${formatDate(date)}` : ""}
      </div>
    `;
}

async function api(path, options = {}) {
    const res = await fetch(path, {
        credentials: "include",
        ...options,
    });
    const data = await res.json().catch(() => null);
    return { res, data };
}


function renderTasks(tasks) {
    if (!tasks.length) {
        tasksEl.innerHTML = `<p class="muted">Aucune tâche.</p>`;
        return;
    }

    const rows = tasks.map(t => {

        // lignes started/done
        const startedLine = renderUserLine(
            "Commencé par :",
            t.startedBy,
            t.startedAt
        );

        const doneLine = renderUserLine(
            "Fait par :",
            t.doneBy,
            t.doneAt
        );

        return `
      <div class="task">
        <div class="task-main">
          <div class="task-title">
            ${escapeHtml(t.title)}
            <span class="badge badge-${t.status.toLowerCase().replace("_", "")}">${t.status}</span>
          </div>

          <div class="task-meta muted">
            Diff: ${t.difficulty}
            — Durée: ${t.durationMinutes} min
            — Créée: ${t.createdAt ? new Date(t.createdAt).toLocaleString() : "-"}
          </div>

          ${t.description ? `<div class="task-desc">${escapeHtml(t.description)}</div>` : ""}

          <!--  Ajout started/done -->
          <div class="task-history">
            ${startedLine}
            ${doneLine}
          </div>

        </div>

        <div class="task-actions">
          <button data-action="todo" data-id="${t.id}" class="btn secondary">TODO</button>
          <button data-action="progress" data-id="${t.id}" class="btn secondary">IN_PROGRESS</button>
          <button data-action="done" data-id="${t.id}" class="btn secondary">DONE</button>
          <button data-action="delete" data-id="${t.id}" class="btn danger">Supprimer</button>
        </div>
      </div>
    `;
    });

    tasksEl.innerHTML = rows.join("");
}


function escapeHtml(s) {
    return String(s)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}


async function loadMe() {
    const { res, data } = await api("/api/me");

    if (res.status === 401) {
        meEl.textContent = "Non connecté. Va sur Connexion.";
        return false;
    }

    if (!res.ok || !data?.success) {
        meEl.textContent = `Erreur /api/me (HTTP ${res.status})`;
        return false;
    }

    meEl.textContent = `Connecté : ${data.user.email} (${data.user.roles.join(", ")})`;
    return true;
}


async function loadTasks() {
    listMsg.textContent = "";
    const { res, data } = await api("/api/tasks");

    if (res.status === 401) {
        listMsg.textContent = "Non connecté. Va sur Connexion.";
        listMsg.className = "msg error";
        renderTasks([]);
        return;
    }

    if (!res.ok || !data?.success) {
        listMsg.textContent = data?.error || `Erreur /api/tasks (HTTP ${res.status})`;
        listMsg.className = "msg error";
        renderTasks([]);
        return;
    }

    renderTasks(data.tasks || []);
}


createForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    createMsg.textContent = "";

    const fd = new FormData(createForm);
    const payload = {
        title: String(fd.get("title") || ""),
        description: String(fd.get("description") || ""),
        difficulty: Number(fd.get("difficulty")),
        durationMinutes: Number(fd.get("durationMinutes")),
    };

    const { res, data } = await api("/api/tasks", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
    });

    if (res.status === 401) {
        createMsg.textContent = "Non connecté. Va sur Connexion.";
        createMsg.className = "msg error";
        return;
    }

    if (!res.ok || !data?.success) {
        createMsg.textContent = JSON.stringify(data?.errors || data || `Erreur HTTP ${res.status}`);
        createMsg.className = "msg error";
        return;
    }

    createMsg.textContent = `Tâche créée (id ${data.task.id})`;
    createMsg.className = "msg ok";
    createForm.reset();

    await loadTasks();
});


tasksEl.addEventListener("click", async (e) => {
    const btn = e.target.closest("button[data-action]");
    if (!btn) return;

    const id = btn.getAttribute("data-id");
    const action = btn.getAttribute("data-action");

    if (action === "delete") {
        const { res } = await api(`/api/tasks/${id}`, { method: "DELETE" });
        if (!res.ok) {
            alert(`Erreur suppression (HTTP ${res.status})`);
            return;
        }
        await loadTasks();
        return;
    }

    const statusMap = { todo: "TODO", progress: "IN_PROGRESS", done: "DONE" };
    const status = statusMap[action];

    const { res, data } = await api(`/api/tasks/${id}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ status }),
    });

    if (!res.ok || !data?.success) {
        alert(`Erreur update (HTTP ${res.status})`);
        return;
    }

    await loadTasks();
});

refreshBtn.addEventListener("click", loadTasks);

// Init
(async () => {
    const ok = await loadMe();
    if (ok) await loadTasks();
})();
