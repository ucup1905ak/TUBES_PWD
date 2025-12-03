// /public/js/components/riwayat-card.js
// Factory to create a riwayat card <div> and emit CustomEvents if needed

export function createRiwayatCard(item) {
  const div = document.createElement('div');
  div.className = 'nota-card fadeIn';
  div.dataset.id = item.id_penitipan || '';

  const layanan = (item.layanan && item.layanan.length > 0)
    ? item.layanan.join(', ')
    : 'Tidak ada';

  div.innerHTML = `
    <div class='nota-header'>${item.nama_pet}</div>
    <div class='nota-row'><b>Jenis:</b> ${item.jenis_pet || '-'}</div>
    <div class='nota-row'><b>Ras:</b> ${item.ras || '-'}</div>
    <div class='nota-row'><b>Kamar:</b> ${item.kamar}</div>
    <div class='nota-row'><b>Layanan:</b> ${layanan}</div>
    <div class='nota-row'><b>Check-in:</b> ${item.tgl_checkin}</div>
    <div class='nota-row'><b>Check-out:</b> ${item.tgl_checkout}</div>
    <div class='nota-row'><b>Durasi:</b> ${item.durasi} hari</div>
    <div class='total-display'>
        Total: Rp ${item.total_biaya.toLocaleString('id-ID')}
    </div>
  `;

  return div;
}