# Component-Based Architecture Guide

## Overview

This project has been refactored to use a **component-based architecture** without relying on any frameworks. The approach uses native ES modules, factory functions, and CustomEvents to create reusable, testable, and maintainable code.

## Architecture Principles

### 1. **ES Modules for Encapsulation**
- Each component is an ES module with explicit imports/exports
- No global variables or IIFE patterns
- Clean dependency management

### 2. **Factory Functions for Components**
- Components return DOM elements (not strings)
- Each component encapsulates its own markup and behavior
- Components are pure functions: same input → same output

### 3. **Event-Driven Communication**
- Components emit CustomEvents for parent interaction
- Parents listen to events and handle business logic
- Keeps components decoupled and reusable

### 4. **Shared Utilities**
- Common functionality extracted to shared modules
- DRY principle: auth, UI helpers, formatters

## Folder Structure

```
public/js/
├── components/          # Reusable UI components
│   ├── layanan-row.js   # Service row component
│   ├── paket-row.js     # Package row component
│   ├── user-row.js      # User row component
│   ├── penitipan-row.js # Boarding row component
│   └── riwayat-card.js  # History card component
├── shared/              # Shared utilities
│   ├── auth.js          # Authentication helpers
│   └── ui.js            # UI utilities (sidebar, logout)
├── all_user.js          # Admin - All users page
├── dashboard_admin.js   # Admin dashboard
├── dashboard_user.js    # User dashboard
├── kelola.js            # Admin - Manage services/packages
├── profil.js            # Profile page
└── riwayat.js           # History page
```

## Component Pattern

### Component Structure

```javascript
// /public/js/components/example-row.js
export function createExampleRow(data) {
  // 1. Create DOM structure
  const tr = document.createElement('tr');
  tr.dataset.id = data.id;

  // 2. Build child elements
  const tdName = document.createElement('td');
  tdName.textContent = data.name;

  // 3. Add event listeners that emit CustomEvents
  const editBtn = document.createElement('button');
  editBtn.addEventListener('click', () => {
    tr.dispatchEvent(new CustomEvent('example-edit', {
      detail: data,
      bubbles: true
    }));
  });

  // 4. Assemble and return
  tr.appendChild(tdName);
  return tr;
}
```

### Using Components

```javascript
// Import component
import { createExampleRow } from './components/example-row.js';

// Use in page
function fetchData() {
  fetch('/api/data')
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('tbody');
      tbody.innerHTML = '';

      data.items.forEach(item => {
        const row = createExampleRow(item);

        // Listen to component events
        row.addEventListener('example-edit', (e) => {
          const item = e.detail;
          // Handle edit logic here
        });

        tbody.appendChild(row);
      });
    });
}
```

## Shared Modules

### auth.js - Authentication Helpers

```javascript
import { checkSession, fetchUserData } from './shared/auth.js';

// Check session and get token
const sessionToken = checkSession();
if (!sessionToken) return;

// Fetch user data with callback
fetchUserData(sessionToken, (user) => {
  console.log('User:', user);
});
```

### ui.js - UI Utilities

```javascript
import { initSidebar, initLogout } from './shared/ui.js';

// Initialize common UI elements
initSidebar();  // Handles sidebar toggle
initLogout();   // Handles logout button
```

## Migration Checklist

### Files Converted to ES Modules ✓

- ✅ `public/js/kelola.js` → Uses `layanan-row`, `paket-row` components
- ✅ `public/js/all_user.js` → Uses `user-row` component
- ✅ `public/js/dashboard_admin.js` → Uses shared auth/ui
- ✅ `public/js/dashboard_user.js` → Uses `penitipan-row` component
- ✅ `public/js/riwayat.js` → Uses `riwayat-card` component
- ✅ `public/js/profil.js` → Uses shared auth/ui

### Components Created ✓

- ✅ `components/layanan-row.js` - Service table row
- ✅ `components/paket-row.js` - Package table row
- ✅ `components/user-row.js` - User table row
- ✅ `components/penitipan-row.js` - Boarding table row
- ✅ `components/riwayat-card.js` - History card

### Shared Modules Created ✓

- ✅ `shared/auth.js` - Session check, fetch user data
- ✅ `shared/ui.js` - Sidebar toggle, logout handler

### XHTML Pages Updated ✓

- ✅ `pages/kelola.xhtml` → `<script type="module">`
- ✅ `pages/all_user.xhtml` → `<script type="module">`
- ✅ `pages/dashboard_admin.xhtml` → `<script type="module">`
- ✅ `pages/dashboard_user.xhtml` → `<script type="module">`
- ✅ `pages/profil.xhtml` → `<script type="module">`
- ✅ `pages/riwayat.xhtml` → `<script type="module">`

## Benefits

### 1. **Reusability**
- Components can be used across multiple pages
- No code duplication for similar UI patterns
- Easy to create new pages using existing components

### 2. **Testability**
- Components are pure functions → easy to unit test
- No global state to mock
- Clear inputs and outputs

### 3. **Maintainability**
- Changes to a component affect all usages
- Single source of truth for each UI pattern
- Easy to locate and fix bugs

### 4. **Scalability**
- Add new components without affecting existing code
- Clear separation of concerns
- Easy to refactor or extend

## Best Practices

### Component Design

1. **Single Responsibility** - Each component does one thing well
2. **Pure Functions** - Same input always produces same output
3. **Event Emission** - Don't call parent functions directly
4. **No Side Effects** - Don't modify global state

### Naming Conventions

- Component files: `kebab-case.js` (e.g., `user-row.js`)
- Factory functions: `createComponentName` (e.g., `createUserRow`)
- Custom events: `component-action` (e.g., `user-edit`, `layanan-delete`)

### File Organization

```
components/       → UI components that return DOM elements
shared/          → Utility functions and helpers
[page-name].js   → Page-specific logic and initialization
```

## Testing

### Component Testing Example

```javascript
// Test a component in isolation
import { createUserRow } from './components/user-row.js';

const mockUser = {
  id_user: 1,
  nama_lengkap: 'Test User',
  email: 'test@example.com',
  no_telp: '123456',
  role: 'user'
};

const row = createUserRow(mockUser);

// Verify structure
console.assert(row.tagName === 'TR');
console.assert(row.children.length === 6);

// Test event emission
let eventFired = false;
row.addEventListener('user-detail', (e) => {
  eventFired = true;
  console.assert(e.detail.id_user === 1);
});
row.querySelector('.detail-btn').click();
console.assert(eventFired);
```

## Future Enhancements

### Potential Improvements

1. **Form Components** - Extract add/edit forms to reusable components
2. **Modal Component** - Generic modal for confirmations/forms
3. **API Helper** - Centralized fetch wrapper with auth + error handling
4. **Loading States** - Skeleton screens or spinners
5. **Error Boundaries** - Graceful error handling for components
6. **CSS Modules** - Component-specific styles
7. **Type Safety** - Add JSDoc comments or migrate to TypeScript

### Web Components (Optional)

For even better encapsulation, consider migrating to Web Components:

```javascript
class UserRow extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
  }

  connectedCallback() {
    // Render component
  }
}

customElements.define('user-row', UserRow);
```

Note: Table rows (`<tr>`) need special handling with Web Components.

## Troubleshooting

### Module Loading Issues

**Problem:** Script not loading
**Solution:** Ensure XHTML has `<script type="module" src="...">`

**Problem:** Import paths not resolving
**Solution:** Use relative paths from the importing file: `./components/...`

**Problem:** CORS errors in browser
**Solution:** Serve files through a web server (not file://)

### Browser Compatibility

ES Modules work in all modern browsers:
- Chrome 61+
- Firefox 60+
- Safari 11+
- Edge 16+

For older browsers, consider using a bundler (webpack/rollup).

## Summary

This architecture provides a clean, framework-free approach to component-based development:

- ✅ **No frameworks** - Pure JavaScript with native features
- ✅ **Modular** - ES modules for clean imports/exports
- ✅ **Reusable** - Components work across pages
- ✅ **Testable** - Pure functions, clear inputs/outputs
- ✅ **Maintainable** - DRY principle, single source of truth
- ✅ **Scalable** - Easy to add new features

Perfect for projects that want component-based architecture without the overhead of React, Vue, or Angular.
