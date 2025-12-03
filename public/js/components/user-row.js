// /public/js/components/user-row.js
// Factory to create a user <tr> row and emit CustomEvents for detail

export function createUserRow(user) {
  const tr = document.createElement('tr');
  tr.dataset.id = user.id_user || '';

  const tdId = document.createElement('td');
  tdId.textContent = user.id_user || '-';

  const tdNama = document.createElement('td');
  tdNama.textContent = user.nama_lengkap || '-';

  const tdEmail = document.createElement('td');
  tdEmail.textContent = user.email || '-';

  const tdNoTelp = document.createElement('td');
  tdNoTelp.textContent = user.no_telp || '-';

  const tdRole = document.createElement('td');
  tdRole.textContent = user.role === 'admin' ? 'Admin' : 'User';

  const tdAksi = document.createElement('td');

  const detailBtn = document.createElement('button');
  detailBtn.textContent = 'Detail';
  detailBtn.className = 'detail-btn';
  detailBtn.addEventListener('click', () => {
    tr.dispatchEvent(new CustomEvent('user-detail', { detail: user, bubbles: true }));
  });

  tdAksi.appendChild(detailBtn);

  tr.appendChild(tdId);
  tr.appendChild(tdNama);
  tr.appendChild(tdEmail);
  tr.appendChild(tdNoTelp);
  tr.appendChild(tdRole);
  tr.appendChild(tdAksi);

  return tr;
}