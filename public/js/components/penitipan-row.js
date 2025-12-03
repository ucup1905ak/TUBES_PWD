// /public/js/components/penitipan-row.js
// Factory to create a penitipan <tr> row and emit CustomEvents for edit/delete

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID');
}

export function createPenitipanRow(p) {
  const tr = document.createElement('tr');
  tr.dataset.id = p.id_penitipan || '';

  const tdPet = document.createElement('td');
  tdPet.textContent = p.nama_pet || 'Unknown';

  const tdCheckin = document.createElement('td');
  tdCheckin.textContent = formatDate(p.tgl_checkin);

  const tdCheckout = document.createElement('td');
  tdCheckout.textContent = formatDate(p.tgl_checkout);

  const tdStatus = document.createElement('td');
  const statusBadge = document.createElement('span');
  statusBadge.className = 'status-badge status-' + (p.status_penitipan || 'aktif');
  statusBadge.textContent = p.status_penitipan || 'Aktif';
  tdStatus.appendChild(statusBadge);

  const tdAksi = document.createElement('td');
  tdAksi.style.textAlign = 'center';

  const editBtn = document.createElement('button');
  editBtn.textContent = 'Edit';
  editBtn.className = 'edit-btn';
  editBtn.style.background = '#2196f3';
  editBtn.style.color = 'white';
  editBtn.style.border = 'none';
  editBtn.style.borderRadius = '5px';
  editBtn.style.padding = '6px 12px';
  editBtn.style.marginRight = '8px';
  editBtn.style.cursor = 'pointer';
  editBtn.addEventListener('click', () => {
    tr.dispatchEvent(new CustomEvent('penitipan-edit', { detail: p, bubbles: true }));
  });

  const deleteBtn = document.createElement('button');
  deleteBtn.textContent = 'Hapus';
  deleteBtn.className = 'delete-btn';
  deleteBtn.style.background = '#f44336';
  deleteBtn.style.color = 'white';
  deleteBtn.style.border = 'none';
  deleteBtn.style.borderRadius = '5px';
  deleteBtn.style.padding = '6px 12px';
  deleteBtn.style.cursor = 'pointer';
  deleteBtn.addEventListener('click', () => {
    tr.dispatchEvent(new CustomEvent('penitipan-delete', { detail: p, bubbles: true }));
  });

  tdAksi.appendChild(editBtn);
  tdAksi.appendChild(deleteBtn);

  tr.appendChild(tdPet);
  tr.appendChild(tdCheckin);
  tr.appendChild(tdCheckout);
  tr.appendChild(tdStatus);
  tr.appendChild(tdAksi);

  return tr;
}