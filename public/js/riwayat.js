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
            const response = await fetch("/api/penitipan/riwayat", {
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + token,
                    "Content-Type": "application/json"
                }
            });

            const data = await response.json();

            if (data.success && data.items) {
                renderRiwayat(data.items);
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
            const div = document.createElement("div");
            div.className = "nota-card fadeIn";

            const layanan = item.layanan && item.layanan.length > 0
                ? item.layanan.join(", ")
                : "Tidak ada";

            div.innerHTML =
                "<div class='nota-header'>" + item.nama_pet + "</div>" +

                "<div class='nota-row'><b>Jenis:</b> " + (item.jenis_pet || "-") + "</div>" +
                "<div class='nota-row'><b>Ras:</b> " + (item.ras || "-") + "</div>" +

                "<div class='nota-row'><b>Kamar:</b> " + item.kamar + "</div>" +
                "<div class='nota-row'><b>Layanan:</b> " + layanan + "</div>" +

                "<div class='nota-row'><b>Check-in:</b> " + item.tgl_checkin + "</div>" +
                "<div class='nota-row'><b>Check-out:</b> " + item.tgl_checkout + "</div>" +
                "<div class='nota-row'><b>Durasi:</b> " + item.durasi + " hari</div>" +

                "<div class='total-display'>Total: Rp " +
                item.total_biaya.toLocaleString("id-ID") +
                "</div>";

            container.appendChild(div);
        });
    }

    loadRiwayat();

})();
