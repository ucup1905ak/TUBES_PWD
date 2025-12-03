# Component-Based Architecture Guide

## Overview

This project now uses a **reusable component pattern** with ES modules instead of frameworks. Each page uses small, self-contained components that emit custom events for communication.

---

## 🏗️ Architecture

### File Structure
```
public/
├── js/
│   ├── shared/               # Reusable utilities
│   │   ├── auth.js          # Authentication helpers
│   │   └── ui.js            # UI utilities (sidebar, logout)
│   ├── components/          # Reusable UI components
│   │   ├── layanan-row.js   # Layanan table row factory
│   │   ├── paket-row.js     # Paket table row factory
│   │   ├── user-row.js      # User table row factory
│   │   ├── penitipan-row.js # Penitipan table row factory
│   │   └── riwayat-card.js  # Riwayat card factory
│   ├── kelola.js            # Admin layanan/paket page (ES module)
│   ├── all_user.js          # Admin users list (ES module)
│   ├── dashboard_admin.js   # Admin dashboard (ES module)
│   ├── dashboard_user.js    # User dashboard (ES module)
│   ├── profil.js            # Profile page (ES module)
│   ├── riwayat.js           # Riwayat page (ES module)
│   └── ...
└── pages/
    └── *.xhtml              # XHTML pages loading modules
```

---

## 🧩 Component Pattern

### Factory Functions
Components are **factory functions** that return DOM elements and emit `CustomEvent` for actions:

```javascript
// components/layanan-row.js
export function createLayananRow(layanan) {
  const tr = document.createElement('tr');
  // ... build DOM ...
  
  editBtn.addEventListener('click', () => {
    tr.dispatchEvent(new CustomEvent('layanan-edit', { 
      detail: layanan, 
      bubbles: true 
    }));
  });
  
  return tr;
}
```

### Usage
Parent pages import and use components, listening to their events:

```javascript
// kelola.js
import { createLayananRow } from './components/layanan-row.js';

data.layanan.forEach(layanan => {
  const row = createLayananRow(layanan);
  
  row.addEventListener('layanan-edit', function(e) {
    const item = e.detail;
    // Handle edit...
  });
  
  tbody.appendChild(row);
});
```

---

## 📦 Available Components

### Table Row Components

#### `createLayananRow(layanan)`
**File:** `components/layanan-row.js`  
**Returns:** `<tr>` element  
**Events:** `layanan-edit`, `layanan-delete`  
**Usage:** Admin layanan management

#### `createPaketRow(paket)`
**File:** `components/paket-row.js`  
**Returns:** `<tr>` element  
**Events:** `paket-edit`, `paket-delete`  
**Usage:** Admin paket management

#### `createUserRow(user)`
**File:** `components/user-row.js`  
**Returns:** `<tr>` element  
**Events:** `user-detail`  
**Usage:** Admin user list

#### `createPenitipanRow(penitipan)`
**File:** `components/penitipan-row.js`  
**Returns:** `<tr>` element  
**Events:** `penitipan-edit`, `penitipan-delete`  
**Usage:** User dashboard penitipan table

### Card Components

#### `createRiwayatCard(item)`
**File:** `components/riwayat-card.js`  
**Returns:** `<div>` element  
**Events:** None (read-only)  
**Usage:** Riwayat history list

---

## 🔧 Shared Utilities

### `shared/auth.js`

#### `checkSession()`
Validates session token and expiry. Returns token or redirects to login.

```javascript
import { checkSession } from './shared/auth.js';

const sessionToken = checkSession();
if (!sessionToken) return; // Already redirected
```

#### `fetchUserData(sessionToken, callback)`
Fetches user data from `/api/auth/me` and calls callback with user object.

```javascript
import { fetchUserData } from './shared/auth.js';

fetchUserData(sessionToken, (user) => {
  console.log(user.nama_lengkap);
});
```

### `shared/ui.js`

#### `initSidebar()`
Initializes sidebar toggle functionality.

#### `initLogout()`
Initializes logout button with confirmation.

```javascript
import { initSidebar, initLogout } from './shared/ui.js';

initSidebar();
initLogout();
```

---

## 🔄 Event-Driven Communication

Components emit **CustomEvent** objects with data in the `detail` property:

```javascript
// Component emits event
editBtn.addEventListener('click', () => {
  tr.dispatchEvent(new CustomEvent('layanan-edit', { 
    detail: layanan,  // Data payload
    bubbles: true     // Bubble up DOM tree
  }));
});

// Parent listens to event
row.addEventListener('layanan-edit', function(e) {
  const item = e.detail;  // Access the data
  // Handle edit logic...
});
```

### Benefits:
- ✅ **Decoupled:** Components don't know about parents
- ✅ **Reusable:** Same component works anywhere
- ✅ **Testable:** Easy to test in isolation
- ✅ **Type-safe:** Event names prevent typos

---

## 🎯 Converted Pages

| Page | Status | Components Used |
|------|--------|----------------|
| `kelola.js` | ✅ Module | `layanan-row`, `paket-row` |
| `all_user.js` | ✅ Module | `user-row` |
| `dashboard_admin.js` | ✅ Module | Auth, UI helpers |
| `dashboard_user.js` | ✅ Module | `penitipan-row` |
| `profil.js` | ✅ Module | Auth, UI helpers |
| `riwayat.js` | ✅ Module | `riwayat-card` |
| `login.js` | ⚪ Standalone | N/A (no auth needed) |
| `register.js` | ⚪ Standalone | N/A (no auth needed) |
| `titip.js` | ⚪ IIFE | To be converted |

---

## 📝 How to Add a New Component

### 1. Create the component file
```javascript
// components/my-component.js
export function createMyComponent(data) {
  const element = document.createElement('div');
  element.className = 'my-component';
  
  // Build DOM structure
  element.innerHTML = `<h3>${data.title}</h3>`;
  
  // Add interactivity
  element.addEventListener('click', () => {
    element.dispatchEvent(new CustomEvent('my-action', { 
      detail: data,
      bubbles: true 
    }));
  });
  
  return element;
}
```

### 2. Import and use in page
```javascript
// my-page.js
import { createMyComponent } from './components/my-component.js';

const component = createMyComponent({ title: 'Hello' });

component.addEventListener('my-action', (e) => {
  console.log('Action triggered:', e.detail);
});

container.appendChild(component);
```

### 3. Update XHTML
```html
<script type="module" src="/public/js/my-page.js"></script>
```

---

## 🚀 Benefits Over Frameworks

### No Dependencies
- **Zero npm packages** to install or update
- No build step required
- Works directly in browser

### Full Control
- **Complete control** over DOM and events
- No virtual DOM abstraction
- Direct manipulation when needed

### Performance
- **Lightweight:** Only load what you need
- **Fast:** Native DOM operations
- **Efficient:** No framework overhead

### Learning Curve
- **Simple:** Just JS, no JSX or templates
- **Familiar:** Standard DOM API
- **Flexible:** Mix with any library

---

## 🛠️ Development Tips

### Module Loading
Ensure XHTML pages use `type="module"`:
```html
<script type="module" src="/public/js/kelola.js"></script>
```

### Browser Cache
Hard-reload (Ctrl+Shift+R) after changing modules to clear cache.

### Debugging
Use browser DevTools to inspect custom events:
```javascript
// Log all custom events
window.addEventListener('*', (e) => {
  if (e instanceof CustomEvent) {
    console.log('Custom Event:', e.type, e.detail);
  }
}, true);
```

### MIME Types
Ensure your server sends correct MIME type for `.js` files:
```
Content-Type: application/javascript
```

---

## 📚 Further Improvements

### To Do:
- [ ] Convert `titip.js` to ES module with form components
- [ ] Create reusable modal/dialog component
- [ ] Add form validation utilities to `shared/`
- [ ] Create API fetch wrapper in `shared/api.js`
- [ ] Add CSS component styles in `css/components/`
- [ ] Document component props/events with JSDoc

### Optional Web Components
For advanced use cases, consider native **Web Components**:
```javascript
class LayananRow extends HTMLElement {
  constructor() {
    super();
    // Shadow DOM for encapsulation
  }
}
customElements.define('layanan-row', LayananRow);
```

---

## 🎓 Resources

- [MDN: CustomEvent](https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent)
- [MDN: ES Modules](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Modules)
- [MDN: Web Components](https://developer.mozilla.org/en-US/docs/Web/Web_Components)

---

**Last Updated:** December 3, 2025  
**Branch:** `test_reusable_component`
