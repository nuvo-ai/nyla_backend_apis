# Pharmacy Portal: Deep-Dive Technical Summary

## 1. Routing Structure

- **Root Route:** `/pharmacy/*`
  - Defined in `App.tsx`:
    ```tsx
    <Route path="/pharmacy/*" element={<PharmacyDashboard />} />
    ```
- **Subroutes** (inside `PharmacyDashboard.tsx`):
    ```tsx
    <Routes>
      <Route path="/" element={<PharmacyMainDashboard />} />
      <Route path="/order-history" element={<Orders />} />
      <Route path="/emr" element={<PharmacyEMR />} />
      <Route path="/profile" element={<PharmacyProfile />} />
      <Route path="/ai-assistant" element={<PharmacyAIAssistant />} />
      <Route path="/settings" element={<PharmacySettings />} />
    </Routes>
    ```
- **Navigation:** Managed via `PharmacySidebar` with links to each subroute.

---

## 2. Layout & Structure

- **Main Container:** `PharmacyDashboard.tsx`
  - Handles sidebar toggle, mobile responsiveness, and layout.
  - Renders:
    - `PharmacySidebar` (navigation)
    - `PharmacyHeader` (top bar: theme, profile, logout)
    - Main content area (renders current subpage via React Router)

---

## 3. Sidebar Navigation

- **Component:** `PharmacySidebar.tsx`
- **Menu Items:**
  - Dashboard (`/pharmacy`)
  - Orders (`/pharmacy/order-history`)
  - EMR (`/pharmacy/emr`)
  - AI Assistant (`/pharmacy/ai-assistant`)
  - Profile (`/pharmacy/profile`)
  - Settings (`/pharmacy/settings`)
- **Responsive:** Sidebar can be toggled on mobile.

---

## 4. Pharmacy Pages & Main Components

Each subroute loads a dedicated page/component:

| Route                   | Component                | Purpose/Features                                                                 |
|-------------------------|--------------------------|----------------------------------------------------------------------------------|
| `/pharmacy`             | `PharmacyMainDashboard`  | Main dashboard, incoming orders, quick actions, recent activity                   |
| `/pharmacy/order-history` | `Orders`               | Order management (list, filter, status update, export, details)                   |
| `/pharmacy/emr`         | `PharmacyEMR`            | Electronic Medical Records, aggregates completed/delivered orders                 |
| `/pharmacy/profile`     | `PharmacyProfile`        | Pharmacy info, settings, financials, rating                                      |
| `/pharmacy/ai-assistant`| `PharmacyAIAssistant`    | Chat-based AI assistant for pharmacy operations                                   |
| `/pharmacy/settings`    | `PharmacySettings`       | Subscription, billing, and configuration                                         |

**Supporting Components:**
- `PharmacyHeader`: Top bar (theme, profile, logout)
- `InventoryManagement`: (used for medication stock management)
- Various UI components for cards, tables, dialogs, etc.

---

## 5. Data & Logic Overview

- **State Management:** Uses React hooks for local state, custom hooks for data (e.g., `useOrderManagement`, `useSidebar`, `useIsMobile`).
- **Order/EMR Logic:** Orders are fetched/managed via hooks; EMR is generated from completed/delivered orders.
- **Export:** Orders can be exported as CSV/PDF.
- **AI Assistant:** Stores chat history, handles file uploads/attachments.
- **Profile/Settings:** Editable pharmacy info, subscription/billing management.

---

## 6. Onboarding Flow

- **Route:** `/onboarding/pharmacy`
- **Component:** `PharmacyOnboarding.tsx` (multi-step onboarding wizard)
- **Subcomponents:** For basic info, legal compliance, service locations, etc.

---

## 7. Visual Summary

```
/pharmacy/* (PharmacyDashboard)
│
├── /               → PharmacyMainDashboard
├── /order-history  → Orders
├── /emr            → PharmacyEMR
├── /profile        → PharmacyProfile
├── /ai-assistant   → PharmacyAIAssistant
└── /settings       → PharmacySettings
```
- Navigation via `PharmacySidebar`
- Shared layout: sidebar + header + routed main content

---

## 8. Key Business Features

- Order management (CRUD, status, export)
- Medication/inventory tracking
- EMR aggregation from orders
- Pharmacy profile & settings
- AI-powered chat assistant
- Subscription & billing management

---

**This summary provides all the context needed for a backend or API agent to design supporting services, models, and endpoints for the pharmacy portal.**
