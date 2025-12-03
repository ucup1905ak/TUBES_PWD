# Component Architecture Diagram

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         XHTML Pages                              │
│  (all_user, dashboard_admin, dashboard_user, kelola, etc.)      │
└────────────────────┬────────────────────────────────────────────┘
                     │ <script type="module" src="...">
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Main JS Modules                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │ all_user.js  │  │ kelola.js    │  │dashboard_    │          │
│  │              │  │              │  │user.js       │          │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘          │
│         │ imports         │ imports         │ imports           │
└─────────┼─────────────────┼─────────────────┼───────────────────┘
          │                 │                 │
          ▼                 ▼                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Shared Modules (DRY)                          │
│  ┌───────────────────────┐    ┌───────────────────────┐        │
│  │   shared/auth.js      │    │   shared/ui.js        │        │
│  │  • checkSession()     │    │  • initSidebar()      │        │
│  │  • fetchUserData()    │    │  • initLogout()       │        │
│  └───────────────────────┘    └───────────────────────┘        │
└─────────────────────────────────────────────────────────────────┘
          │                 │                 │
          └─────────┬───────┴─────────┬───────┘
                    │ imports         │ imports
                    ▼                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                    UI Components                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │ layanan-row  │  │ user-row     │  │ penitipan-   │          │
│  │              │  │              │  │ row          │          │
│  │ • Renders TR │  │ • Renders TR │  │ • Renders TR │          │
│  │ • Emits:     │  │ • Emits:     │  │ • Emits:     │          │
│  │   layanan-   │  │   user-      │  │   penitipan- │          │
│  │   edit       │  │   detail     │  │   edit       │          │
│  │   layanan-   │  │              │  │   penitipan- │          │
│  │   delete     │  │              │  │   delete     │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐                            │
│  │ paket-row    │  │ riwayat-card │                            │
│  │              │  │              │                            │
│  │ • Renders TR │  │ • Renders    │                            │
│  │ • Emits:     │  │   DIV card   │                            │
│  │   paket-edit │  │              │                            │
│  │   paket-     │  │              │                            │
│  │   delete     │  │              │                            │
│  └──────────────┘  └──────────────┘                            │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow Example: Kelola Page (Manage Services)

```
┌──────────────────────────────────────────────────────────────────┐
│ 1. User opens /admin/kelola                                      │
└────────────────────────┬─────────────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│ 2. kelola.xhtml loads <script type="module" src="kelola.js">    │
└────────────────────────┬─────────────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│ 3. kelola.js imports:                                            │
│    • createLayananRow from './components/layanan-row.js'         │
│    • createPaketRow from './components/paket-row.js'             │
│    • initSidebar, initLogout from './shared/ui.js'               │
└────────────────────────┬─────────────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│ 4. fetchLayanan() calls API → gets data array                   │
└────────────────────────┬─────────────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│ 5. For each item: row = createLayananRow(item)                  │
│    Component returns <tr> with edit/delete buttons              │
└────────────────────────┬─────────────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│ 6. kelola.js listens to CustomEvents:                           │
│    row.addEventListener('layanan-edit', handleEdit)              │
│    row.addEventListener('layanan-delete', handleDelete)          │
└────────────────────────┬─────────────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│ 7. User clicks "Edit" button                                     │
└────────────────────────┬─────────────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│ 8. Component emits: new CustomEvent('layanan-edit', {detail})   │
└────────────────────────┬─────────────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────────────────┐
│ 9. kelola.js event handler receives event                       │
│    • Extracts e.detail (the data)                                │
│    • Shows edit form with pre-filled values                      │
└──────────────────────────────────────────────────────────────────┘
```

## Component Communication Pattern

```
┌─────────────────────────────────────────────────────────────────┐
│                    Parent Page (kelola.js)                       │
│                                                                  │
│  ┌─────────────────────────────────────────────────────┐        │
│  │  1. Fetch data from API                             │        │
│  └──────────────────────┬──────────────────────────────┘        │
│                         ▼                                        │
│  ┌─────────────────────────────────────────────────────┐        │
│  │  2. Create component: row = createLayananRow(data)  │        │
│  └──────────────────────┬──────────────────────────────┘        │
│                         ▼                                        │
│  ┌─────────────────────────────────────────────────────┐        │
│  │  3. Listen to events from component                 │        │
│  │     row.addEventListener('layanan-edit', ...)        │        │
│  └──────────────────────┬──────────────────────────────┘        │
│                         ▼                                        │
│  ┌─────────────────────────────────────────────────────┐        │
│  │  4. Append to DOM: tbody.appendChild(row)           │        │
│  └─────────────────────────────────────────────────────┘        │
│                                                                  │
└─────────────────────┬────────────────────────────────────────────┘
                      │ User clicks button
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│              Component (layanan-row.js)                          │
│                                                                  │
│  ┌─────────────────────────────────────────────────────┐        │
│  │  Button click → Emit CustomEvent                    │        │
│  │  tr.dispatchEvent(new CustomEvent('layanan-edit',   │        │
│  │    { detail: data, bubbles: true }))                │        │
│  └──────────────────────┬──────────────────────────────┘        │
│                         │                                        │
└─────────────────────────┼────────────────────────────────────────┘
                          │ Event bubbles up
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│              Parent Event Listener                               │
│                                                                  │
│  ┌─────────────────────────────────────────────────────┐        │
│  │  Receives event with data in e.detail                │        │
│  │  • Show form / Navigate / Update state               │        │
│  │  • Handle business logic                             │        │
│  └─────────────────────────────────────────────────────┘        │
└─────────────────────────────────────────────────────────────────┘
```

## File Dependencies Graph

```
kelola.js
  ├─ imports → components/layanan-row.js
  ├─ imports → components/paket-row.js
  └─ imports → shared/ui.js
                  └─ (no dependencies)

all_user.js
  ├─ imports → components/user-row.js
  ├─ imports → shared/auth.js
  └─ imports → shared/ui.js

dashboard_user.js
  ├─ imports → components/penitipan-row.js
  ├─ imports → shared/auth.js
  └─ imports → shared/ui.js

dashboard_admin.js
  ├─ imports → shared/auth.js
  └─ imports → shared/ui.js

profil.js
  ├─ imports → shared/auth.js
  └─ imports → shared/ui.js

riwayat.js
  └─ imports → components/riwayat-card.js
```

## Benefits Visualization

```
┌───────────────────────┐
│   Before (IIFE)       │
├───────────────────────┤
│ • Global scope        │
│ • Duplicated code     │
│ • Hard to test        │
│ • Tightly coupled     │
│ • No reuse            │
└───────────────────────┘
           │
           │ Refactor
           ▼
┌───────────────────────┐
│   After (Modules)     │
├───────────────────────┤
│ ✓ Encapsulated        │
│ ✓ DRY (shared)        │
│ ✓ Pure functions      │
│ ✓ Decoupled (events)  │
│ ✓ Highly reusable     │
└───────────────────────┘
```

## Component Lifecycle

```
1. Import
   ↓
2. Create (Factory Function)
   • Receives data
   • Builds DOM structure
   • Attaches event listeners
   ↓
3. Emit Events
   • User interaction
   • Dispatches CustomEvent
   ↓
4. Parent Handles
   • Listens to event
   • Updates state/API
   • Re-renders if needed
```

## Real-World Example Flow

**Scenario:** Admin deletes a service (layanan)

```
┌──────────────────────────────────────────────────────────────────┐
│ User clicks "Hapus" button on layanan row                       │
└──────────────────┬───────────────────────────────────────────────┘
                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ layanan-row.js: button.addEventListener('click', ...)           │
│ → tr.dispatchEvent(new CustomEvent('layanan-delete', {...}))    │
└──────────────────┬───────────────────────────────────────────────┘
                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ kelola.js: row.addEventListener('layanan-delete', (e) => {...}) │
│ → const item = e.detail                                          │
│ → if (confirm('Yakin?')) { ... }                                 │
└──────────────────┬───────────────────────────────────────────────┘
                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ fetch('/api/admin/layanan/' + id, { method: 'DELETE', ... })    │
└──────────────────┬───────────────────────────────────────────────┘
                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ Backend deletes record → returns {success: true}                │
└──────────────────┬───────────────────────────────────────────────┘
                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ kelola.js: if (res.success) fetchLayanan()                      │
│ → Re-fetches data and re-renders table                          │
└──────────────────────────────────────────────────────────────────┘
```

## Key Takeaways

1. **Separation of Concerns**
   - Components = UI + local behavior
   - Pages = Business logic + API calls
   - Shared = Common utilities

2. **Unidirectional Data Flow**
   - Parent passes data to component
   - Component emits events to parent
   - Parent updates state and re-renders

3. **No Framework Needed**
   - Native ES modules
   - Standard DOM APIs
   - CustomEvents for communication

4. **Scalable & Maintainable**
   - Easy to add new components
   - DRY principle enforced
   - Clear dependencies
