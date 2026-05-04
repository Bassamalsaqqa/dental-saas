# Frontend Refactor Study: Dental SaaS Transition to Lovable.dev

## 1. Executive Summary
The current application is a multi-tenant Dental SaaS built with CodeIgniter 4 and MySQL. While the backend logic is robust (SaaS-hardened, tenant-isolated), the frontend is fragmented and lacks the modern, intuitive workflow required for a high-end medical application.

**Goal:** Completely replace the PHP-driven frontend with a modern, React-based SPA (Single Page Application) using **Lovable.dev**, maintaining the existing CI4 backend as a stateless REST API.

---

## 2. Technical Compatibility (Shared Hosting / Hostinger)
**Stack:** React (Vite) + Tailwind CSS + Lucide Icons + TanStack Query.

**Deployment Strategy for Hostinger:**
- **Backend:** Remains CI4 on the same server, serving requests at `/api/v1/`.
- **Frontend:** The React build (`dist` folder) is placed in the `public/` directory or a subdomain.
- **Shared DB:** MySQL (already optimized).
- **Compatibility:** Since the frontend is static assets (HTML/JS/CSS), it works perfectly on shared hosting alongside PHP.

---

## 3. Core Roles & Key Workflows

### A. Super Admin (Control Plane)
- **Main Job:** Platform stability and revenue.
- **Key Workflows:**
    - Manual Clinic Onboarding (Create Clinic + Admin User + Subscription).
    - Plan Management (Define quotas for patients/doctors).
    - Revenue Tracking (Invoice generation, payment verification).
    - Impersonation (Access tenant views for support).

### B. Clinic Admin (Tenant Management)
- **Main Job:** Operation efficiency and financial health.
- **Key Workflows:**
    - Staff Management (Roles & Permissions).
    - Clinic-level Finance (Total revenue, expenses, provider payouts).
    - Inventory Control (Stock levels, supplier orders).
    - WhatsApp Settings (Templates, automation policies).

### C. Dentist (Clinical Focus)
- **Main Job:** Patient care and treatment execution.
- **Key Workflows:**
    - **The Clinical Hub:** Interactive 3D/2D Odontogram (Charting).
    - Treatment Planning (Creating multi-stage contracts).
    - Prescription Issuance (Quick-search templates).
    - Imaging/X-ray Gallery (File management).

### D. Receptionist (Patient Flow)
- **Main Job:** Scheduling and billing.
- **Key Workflows:**
    - **Smart Calendar:** Drag-and-drop appointments.
    - Patient Onboarding (Digital registration).
    - Invoice Collection (Payment processing, balance tracking).

---

## 4. Lovable.dev Master Prompt (The Foundation)

Copy and paste these into Lovable to start the project. It provides the full architectural context.

### Prompt 1: The SaaS Architecture & Auth
> "Build a modern, high-density Dental SaaS Dashboard.
> **Context:** This is the frontend for a multi-tenant application. It must be clean, professional (medical blue/slate palette), and highly optimized for speed.
> **Architecture:**
> 1. **Auth:** Stateless Bearer Token login. Use an 'Authentication' service to store tokens in localStorage.
> 2. **Tenant Wall:** After login, if the user belongs to multiple clinics, show a 'Clinic Selector'. Store the `active_clinic_id`.
> 3. **API Integration:** All data must come from `/api/v1/`. Every request MUST include `Authorization: Bearer <token>` and `X-Clinic-Id: <id>` headers.
> 4. **Layout:** A responsive sidebar with: Dashboard, Patients, Calendar, Clinical (Odontogram), Finance, Inventory, and Settings.
> 5. **Design Language:** Use Tailwind CSS. Focus on 'Information Density'—doctors need to see data quickly without excessive scrolling. Use Lucide icons.
> 6. **State Management:** Use TanStack Query (React Query) for data fetching and caching.
> 
> **Database Context (Existing):**
> Tables include: `clinics`, `users`, `patients`, `appointments`, `examinations`, `odontograms`, `finances`, `inventory`.
> 
> **Goal:** Create the Login page, Clinic Selector, and the main Shell with a global Search bar (Cmd+K)."

### Prompt 2: The Patient Journey (Clinical)
> "Implement the Patient Profile and Clinical Hub. 
> 1. **Patient Profile:** Summary card (Name, Age, Medical Alerts), Timeline of visits.
> 2. **Odontogram:** An interactive grid of 32 teeth. Users can click a tooth to apply a 'Finding' (e.g., Cavity) or a 'Treatment' (e.g., Filling). Use color-coding for status (Planned = Blue, Completed = Green).
> 3. **Treatment Contracts:** A builder to add procedures, calculate totals, and show a 'Signature' area for the patient.
> 4. **Workflow:** The UI should guide the user from Examination -> Finding -> Treatment Plan -> Invoice."

### Prompt 3: Finance & Inventory (The Operations)
> "Build the Operations Module.
> 1. **Finance Ledger:** A list of all invoices with status tags (Paid, Pending, Overdue). Include a 'Quick Payment' modal.
> 2. **Inventory Dashboard:** Show items with 'Low Stock' warnings. Movement ledger for 'Usage' vs 'Arrival'.
> 3. **Provider Payouts:** A dedicated view to calculate doctor commissions based on completed treatments.
> 4. **Reports:** Visual charts for Monthly Revenue vs Expenses."

---

## 5. Summary of Features for Implementation

| Module | Feature | Implementation Detail |
| :--- | :--- | :--- |
| **Auth** | Tenant-Aware Login | Redirects to Clinic Selection if > 1 membership found. |
| **Clinical** | **Interactive Odontogram** | SVG-based tooth selector with state-persistent colors. |
| **Clinical Hub** | Treatment Timeline | A vertical list of all actions taken on a patient over time. |
| **Finance** | Split-Payment Handling | Allow partial payments on a single invoice. |
| **Messaging** | WhatsApp Templates | Preview and send treatment reminders via API integration. |
| **Inventory** | Batch Tracking | Expiry date monitoring for medical supplies. |
| **Control Plane** | Impersonation Bar | A top-banner appearing for Super Admins to 'Return to Admin'. |
