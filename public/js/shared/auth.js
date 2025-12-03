// /public/js/shared/auth.js
// Shared auth utilities

export function checkSession() {
  const sessionToken = localStorage.getItem('session_token');
  const expiresAt = localStorage.getItem('session_expires_at');

  if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
    localStorage.removeItem('session_token');
    localStorage.removeItem('session_expires_at');
    window.location.href = '/login';
    return false;
  }
  return sessionToken;
}

export function fetchUserData(sessionToken, callback) {
  fetch('/api/auth/me', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + sessionToken,
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && data.user) {
      callback(data.user);
    }
  })
  .catch(error => console.error('Error fetching user data:', error));
}