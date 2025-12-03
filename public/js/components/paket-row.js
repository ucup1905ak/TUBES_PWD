// /public/js/components/paket-row.js
// Factory to create a paket <tr> row and emit CustomEvents for edit/delete

export function createPaketRow(paket) {
  const tr = document.createElement('tr');
  tr.dataset.id = paket.id_paket || '';

  const tdId = document.createElement('td');
  tdId.textContent = paket.id_paket || '-';

  const tdNama = document.createElement('td');
  tdNama.textContent = paket.nama_paket || '-';

  const tdDeskripsi = document.createElement('td');
  tdDeskripsi.textContent = (paket.deskripsi || '-').substring(0, 50) + (paket.deskripsi && paket.deskripsi.length > 50 ? '...' : '');

  const tdHarga = document.createElement('td');
  tdHarga.textContent = 'Rp' + (paket.harga || 0).toLocaleString();

  const tdAksi = document.createElement('td');

  const editBtn = document.createElement('button');
  editBtn.textContent = 'Edit';
  editBtn.className = 'edit-btn';
  editBtn.addEventListener('click', () => {
    tr.dispatchEvent(new CustomEvent('paket-edit', { detail: paket, bubbles: true }));
  });

  const deleteBtn = document.createElement('button');
  deleteBtn.textContent = 'Hapus';
  deleteBtn.className = 'delete-btn';
  deleteBtn.addEventListener('click', () => {
    tr.dispatchEvent(new CustomEvent('paket-delete', { detail: paket, bubbles: true }));
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
