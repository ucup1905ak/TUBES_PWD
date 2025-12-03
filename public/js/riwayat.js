import { createRiwayatCard } from './components/riwayat-card.js';

(function() {
    'use strict';

    const token = localStorage.getItem("session_token");
    const expires = localStorage.getItem("session_expires_at");

    if (!token || !expires || new Date(expires) <= new Date()) {
        window.location.href = "/login";
        return;
    }

    async function loadRiwayat() {
        try {
            const response = await fetch("/api/penitipan", {
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + token,
                    "Content-Type": "application/json"
                }
            });

            const data = await response.json();

            if (data.success && data.penitipan && data.penitipan.length > 0) {
                renderRiwayat(data.penitipan);
            } else {
                document.getElementById("riwayatList").innerHTML =
                    "<p>Tidak ada riwayat penitipan.</p>";
            }

        } catch (err) {
            console.error("Error loading riwayat:", err);
        }
    }

    function renderRiwayat(items) {
        const container = document.getElementById("riwayatList");
        container.innerHTML = "";

        items.forEach(item => {
            const card = createRiwayatCard(item);
            container.appendChild(card);
        });
    }

    loadRiwayat();

})();
