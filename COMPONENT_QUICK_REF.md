# Quick Reference - Component-Based Architecture

## 📁 Project Structure

```
public/js/
├── components/          # 5 reusable UI components
│   ├── layanan-row.js
│   ├── paket-row.js  
│   ├── penitipan-row.js
│   ├── riwayat-card.js
│   └── user-row.js
├── shared/              # 2 shared utilities
│   ├── auth.js
│   └── ui.js
└── [6 main page modules converted to ES modules]
```

## 🚀 Quick Start

### Creating a New Component

```javascript
// /public/js/components/my-component.js
export function createMyComponent(data) {
  const element = document.createElement('div');
  element.textContent = data.name;
  
  const btn = document.createElement('button');
  btn.textContent = 'Click me';
  btn.addEventListener('click', () => {
    element.dispatchEvent(new CustomEvent('my-action', {
      detail: data,
      bubbles: true
    }));
  });
  
  element.appendChild(btn);
  return element;
}
```

### Using a Component

```javascript
// /public/js/my-page.js
import { createMyComponent } from './components/my-component.js';

const item = createMyComponent({ name: 'Test' });

item.addEventListener('my-action', (e) => {
  console.log('Action:', e.detail);
});

document.body.appendChild(item);
```

### Update XHTML

```xml
<!-- my-page.xhtml -->
<script type="module" src="/public/js/my-page.js"></script>
```

## 🔑 Key Patterns

### Auth Check
```javascript
import { checkSession } from './shared/auth.js';
const token = checkSession();
if (!token) return;
```

### Fetch User Data
```javascript
import { fetchUserData } from './shared/auth.js';
fetchUserData(token, (user) => {
  console.log(user.nama_lengkap);
});
```

### Init Common UI
```javascript
import { initSidebar, initLogout } from './shared/ui.js';
initSidebar();
initLogout();
```

## 📊 Component Events

| Component | Events |
|-----------|--------|
| `layanan-row` | `layanan-edit`, `layanan-delete` |
| `paket-row` | `paket-edit`, `paket-delete` |
| `user-row` | `user-detail` |
| `penitipan-row` | `penitipan-edit`, `penitipan-delete` |
| `riwayat-card` | (none - read-only) |

## 🛠️ Development Workflow

1. **Create Component**
   - Add to `public/js/components/`
   - Export factory function
   - Emit CustomEvents for actions

2. **Import Component**
   - Import in page module
   - Create instances with data
   - Listen to events

3. **Update XHTML**
   - Change script tag to `type="module"`
   - Point to page module

4. **Test**
   - Run `node --check file.js`
   - Test in browser
   - Check console for errors

## 🧪 Testing Components

```javascript
// Test in browser console or Node
import { createUserRow } from './components/user-row.js';

const row = createUserRow({
  id_user: 1,
  nama_lengkap: 'Test',
  email: 'test@test.com',
  no_telp: '123',
  role: 'user'
});

console.log(row); // <tr>...</tr>
```

## 📚 File Responsibilities

| File Type | Responsibility |
|-----------|---------------|
| `components/*.js` | UI rendering + local events |
| `shared/*.js` | Reusable utilities (auth, UI) |
| `[page].js` | Business logic + API calls |
| `[page].xhtml` | HTML structure + module loading |

## ✅ Converted Pages

- ✅ `all_user.js` - Admin user list
- ✅ `dashboard_admin.js` - Admin dashboard
- ✅ `dashboard_user.js` - User dashboard  
- ✅ `kelola.js` - Service/package management
- ✅ `profil.js` - Profile page
- ✅ `riwayat.js` - History page

## 🎯 Best Practices

1. **Keep components pure** - Same input → same output
2. **Use CustomEvents** - Don't call parent functions directly
3. **Extract common code** - Move to `shared/`
4. **One component per file** - Easy to find and maintain
5. **Meaningful event names** - `component-action` pattern

## 🐛 Common Issues

**Module not found**
- Check relative path: `./components/...`
- Ensure file exists

**Event not firing**
- Check `bubbles: true` in CustomEvent
- Verify event listener is attached

**Script not loading**
- Ensure `<script type="module">`
- Check browser console for errors

## 📖 Documentation

- **COMPONENT_ARCHITECTURE.md** - Complete guide
- **COMPONENT_DIAGRAMS.md** - Visual architecture
- **MIGRATION_SUMMARY.md** - What changed

## 🎉 Benefits Achieved

✓ **No frameworks** - Pure JavaScript  
✓ **Reusable** - Components work anywhere  
✓ **Maintainable** - Single source of truth  
✓ **Testable** - Pure functions  
✓ **Scalable** - Easy to extend  

---

**Quick Links:**
- Components: `public/js/components/`
- Shared Utils: `public/js/shared/`
- Full Guide: `COMPONENT_ARCHITECTURE.md`
