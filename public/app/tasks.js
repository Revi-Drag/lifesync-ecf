const meEl = document.getElementById("me");
const tasksEl = document.getElementById("tasks");
const statsEl = document.getElementById("stats");
const listMsg = document.getElementById("listMsg");
const refreshBtn = document.getElementById("refreshBtn");

const createForm = document.getElementById("createForm");
const createMsg = document.getElementById("createMsg");
const logoutBtn = document.getElementById("logoutBtn");

if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
        // bouton "Déconnexion" = retour page login (comme avant)
        window.location.href = "login.html";
    });
}


const editTasksEl = document.getElementById("editTasks"); // pas utilisé ici (onglet modifier à faire après)
const editMsg = document.getElementById("editMsg");       // pas utilisé ici

const tabButtons = document.querySelectorAll(".tab");
const tabPanels = document.querySelectorAll(".tab-panel");

let currentUser = null; // { id, email, roles }
let isAdmin = false;

function openTab(tabId) {
    tabButtons.forEach((b) => b.classList.toggle("active", b.dataset.tab === tabId));
    tabPanels.forEach((p) => (p.hidden = p.id !== tabId));
}


tabButtons.forEach(btn => {
    btn.addEventListener("click", async () => {
        const tabId = btn.dataset.tab;
        openTab(tabId);

        if (tabId === "tabStats") await loadMyStats();
        if (tabId === "tabEdit") await loadEditTasks();
        if (tabId === "tabTasks") await loadTasks();
    });
});


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

    const text = await res.text();          // <-- on lit toujours le body brut
    let data = null;
    try { data = JSON.parse(text); } catch { /* pas du JSON */ }

    return { res, data, text };
}


function escapeHtml(s) {
    return String(s)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}


function renderTasks(tasks) {
    if (!tasks.length) {
        tasksEl.innerHTML = `<p class="muted">Aucune tâche.</p>`;
        return;
    }

    const rows = tasks.map((t) => {
        const startedLine = renderUserLine("Commencé par :", t.startedBy, t.startedAt);
        const doneLine = renderUserLine("Fait par :", t.doneBy, t.doneAt);

        const canDelete = isAdmin || (t.createdBy === currentUser?.email);

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

          <div class="task-history">
            ${startedLine}
            ${doneLine}
          </div>
        </div>

        <div class="task-actions">
          <button data-action="todo" data-id="${t.id}" class="btn secondary">TODO</button>
          <button data-action="progress" data-id="${t.id}" class="btn secondary">IN_PROGRESS</button>
          <button data-action="done" data-id="${t.id}" class="btn secondary">DONE</button>
          ${canDelete ? `<button data-action="delete" data-id="${t.id}" class="btn danger">Supprimer</button>` : ""}
        </div>
      </div>
    `;
    });

    tasksEl.innerHTML = rows.join("");
}


function renderEditTasks(tasks) {
    if (!editTasksEl) return;

    if (!tasks.length) {
        editTasksEl.innerHTML = `<p class="muted">Aucune tâche.</p>`;
        return;
    }

    const rows = tasks.map(t => {
        const canEdit = isAdmin || (t.createdBy === currentUser?.email); // règle CDC
        if (!canEdit) return ""; // on n'affiche pas les tâches non éditables

        return `
        <div class="task">
          <div class="task-main">
            <div class="task-title">
              ${escapeHtml(t.title)}
              <span class="badge badge-${t.status.toLowerCase().replace("_", "")}">${t.status}</span>
            </div>

            <div class="task-meta muted small">
              Créée par: ${escapeHtml(t.createdBy || "-")} — ${t.createdAt ? new Date(t.createdAt).toLocaleString() : "-"}
            </div>

            <div class="grid" style="margin-top:10px">
              <label>
                <span class="muted small">Titre</span>
                <input data-edit="title" data-id="${t.id}" value="${escapeHtml(t.title)}" />
              </label>

              <label>
                <span class="muted small">Description</span>
                <input data-edit="description" data-id="${t.id}" value="${escapeHtml(t.description || "")}" />
              </label>

              <label>
                <span class="muted small">Difficulté (1-5)</span>
                <input data-edit="difficulty" data-id="${t.id}" type="number" min="1" max="5" value="${t.difficulty ?? 1}" />
              </label>

              <label>
                <span class="muted small">Durée (minutes)</span>
                <input data-edit="durationMinutes" data-id="${t.id}" type="number" min="1" max="1440" value="${t.durationMinutes ?? 1}" />
              </label>
            </div>

            <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap">
              <button class="btn" data-action="saveEdit" data-id="${t.id}">Enregistrer</button>
              <button class="btn secondary" data-action="resetEdit" data-id="${t.id}">Annuler</button>
            </div>
          </div>
        </div>
      `;
    }).filter(Boolean);

    editTasksEl.innerHTML = rows.join("") || `<p class="muted">Aucune tâche éditable.</p>`;
}


async function loadEditTasks() {
    if (!editMsg || !editTasksEl) return;

    editMsg.textContent = "";
    const { res, data } = await api("/api/tasks");

    if (res.status === 401) {
        editMsg.textContent = "Non connecté.";
        editMsg.className = "msg error";
        renderEditTasks([]);
        return;
    }

    if (!res.ok || !data?.success) {
        console.log("SAVE payload:", payload);
        console.log("HTTP", res.status, "raw:", text);
        alert(data?.error || data?.errors ? JSON.stringify(data) : text || `HTTP ${res.status}`);
        return;
    }

    renderEditTasks(data.tasks || []);
}


function getEditPayload(taskId) {
    const inputs = editTasksEl.querySelectorAll(`input[data-id="${taskId}"]`);
    const payload = {};
    inputs.forEach(inp => {
        const key = inp.getAttribute("data-edit");
        let val = inp.value;

        if (key === "difficulty" || key === "durationMinutes") {
            val = Number(val);
        }

        payload[key] = val;
    });
    return payload;
}


async function loadMyStats() {
    const { res, data } = await api("/api/me/stats");

    if (res.status === 401) {
        statsEl.textContent = "Non connecté.";
        return;
    }

    if (!res.ok || !data?.success) {
        statsEl.textContent = `Erreur stats (HTTP ${res.status})`;
        return;
    }

    statsEl.innerHTML = `
    <div><strong>Tâches créées :</strong> ${data.stats.tasksCreated}</div>
    <div><strong>Tâches effectuées :</strong> ${data.stats.tasksDone}</div>
  `;
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

    currentUser = data.user; // { id, email, roles }
    isAdmin = currentUser.roles.includes("ROLE_ADMIN");

    meEl.textContent = `Connecté : ${currentUser.email} (${currentUser.roles.join(", ")})`;
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


if (createForm) {
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
        await loadMyStats();
    });
}


if (tasksEl) {
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
            await loadMyStats();
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
        await loadMyStats();
    });
}

if (editTasksEl) {
    editTasksEl.addEventListener("click", async (e) => {
        const btn = e.target.closest("button[data-action]");
        if (!btn) return;

        const action = btn.dataset.action;
        const id = btn.dataset.id;
        if (!id) return;

        // --- Annuler ---
        if (action === "resetEdit") {
            await loadEditTasks();
            return;
        }

        // --- Enregistrer ---
        if (action === "saveEdit") {

            // récupère les valeurs directement depuis les inputs
            const getVal = (field) =>
                editTasksEl.querySelector(
                    `input[data-edit="${field}"][data-id="${id}"]`
                )?.value;

            const payload = {
                title: String(getVal("title") ?? "").trim(),
                description: String(getVal("description") ?? "").trim(),
                difficulty: Number(getVal("difficulty")),
                durationMinutes: Number(getVal("durationMinutes")),
            };

            // sécurité anti PATCH vide
            if (!payload.title || payload.title.length < 2) {
                alert("Titre trop court (min 2 caractères).");
                return;
            }

            const { res, data } = await api(`/api/tasks/${id}`, {
                method: "PATCH",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            if (!res.ok || !data?.success) {
                alert(data?.error || JSON.stringify(data) || `HTTP ${res.status}`);
                return;
            }

            editMsg.textContent = "Tâche modifiée ✅";
            editMsg.className = "msg ok";

            // rafraîchit tout
            await loadTasks();
            await loadEditTasks();
            await loadMyStats();
        }
    });
}


if (refreshBtn) {
    refreshBtn.addEventListener("click", async () => {
        await loadTasks();
        await loadMyStats();
    });
}


// Init
(async () => {
    openTab("tabTasks");
    const ok = await loadMe();
    if (!ok) return;

    await loadMyStats();
    await loadTasks();
    // optionnel :
    // await loadEditTasks();
})();
