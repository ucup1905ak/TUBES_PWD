// /public/js/components/layanan-row.js
// Factory to create a layanan <tr> row and emit CustomEvents for edit/delete

export function createLayananRow(layanan) {
  const tr = document.createElement('tr');
  tr.dataset.id = layanan.id_layanan || '';

  const tdId = document.createElement('td');
  tdId.textContent = layanan.id_layanan || '-';

  const tdNama = document.createElement('td');
  tdNama.textContent = layanan.nama_layanan || '-';

  const tdDeskripsi = document.createElement('td');
  tdDeskripsi.textContent = (layanan.deskripsi || '-').substring(0, 50) + (layanan.deskripsi && layanan.deskripsi.length > 50 ? '...' : '');

  const tdHarga = document.createElement('td');
  tdHarga.textContent = 'Rp' + (layanan.harga || 0).toLocaleString();

  const tdAksi = document.createElement('td');

  const editBtn = document.createElement('button');
  editBtn.textContent = 'Edit';
  editBtn.className = 'edit-btn';
  editBtn.addEventListener('click', () => {
    tr.dispatchEvent(new CustomEvent('layanan-edit', { detail: layanan, bubbles: true }));
  });

  const deleteBtn = document.createElement('button');
  deleteBtn.textContent = 'Hapus';
  deleteBtn.className = 'delete-btn';
  deleteBtn.addEventListener('click', () => {
    tr.dispatchEvent(new CustomEvent('layanan-delete', { detail: layanan, bubbles: true }));
  });

  tdAksi.appendChild(editBtn);
  tdAksi.appendChild(deleteBtn);

  tr.appendChild(tdId);
  tr.appendChild(tdNama);
  tr.appendChild(tdDeskripsi);
  tr.appendChild(tdHarga);
  tr.appendChild(tdAksi);

  return tr;
}
