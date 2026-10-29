(function () {
    "use strict";

    const bell = document.querySelector(".bell-btn[data-unread-url]");
    if (!bell) return;

    async function refreshUnreadCount() {
        try {
            const response = await fetch(bell.dataset.unreadUrl, {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            });

            if (!response.ok) {
                throw new Error("Gagal memuat jumlah notifikasi.");
            }

            const result = await response.json();
            if (!Number.isInteger(result.unread_count)) {
                throw new Error("Jumlah notifikasi tidak valid.");
            }

            const count = result.unread_count;
            let badge = bell.querySelector(".bell-badge");

            if (count > 0 && !badge) {
                badge = document.createElement("b");
                badge.className = "bell-badge";
                bell.appendChild(badge);
            }

            if (badge) {
                badge.textContent = count;
                badge.hidden = count === 0;
            }

            bell.setAttribute(
                "aria-label",
                "Notifikasi" + (count > 0 ? " (" + count + " belum dibaca)" : ""),
            );
        } catch (error) {
            console.error(error);
        }
    }

    refreshUnreadCount();
    window.setInterval(() => {
        if (!document.hidden) refreshUnreadCount();
    }, 15000);
})();
