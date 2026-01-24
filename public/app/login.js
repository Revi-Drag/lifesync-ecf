const form = document.getElementById("loginForm");
const msg = document.getElementById("loginMsg");

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    msg.textContent = "";

    const formData = new FormData(form);
    const payload = {
        email: String(formData.get("email") || ""),
        password: String(formData.get("password") || ""),
    };

    try {
        const res = await fetch("/api/login", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include", // IMPORTANT: cookie/session
            body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => null);

        if (!res.ok || !data?.success) {
            msg.textContent = data?.error || `Erreur login (HTTP ${res.status})`;
            msg.className = "msg error";
            return;
        }

        msg.textContent = `Connecté : ${data.user?.email}`;
        msg.className = "msg ok";

        // petite pause visuelle
        setTimeout(() => {
            window.location.href = "./tasks.html";
        }, 300);

    } catch (err) {
        msg.textContent = "Erreur réseau ou serveur non disponible.";
        msg.className = "msg error";
    }
});
