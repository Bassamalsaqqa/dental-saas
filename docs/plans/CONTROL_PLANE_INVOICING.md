# Implementation Plan: Control Plane Invoicing (SaaS Billing)

**Goal:** Enable Super Admins to manually generate, manage, and send invoices to Clinics for their subscription fees or other services.

**Inspiration:** Adapted from `App\Controllers\Finance` (Clinic Domain), but simplified for B2B SaaS context (One "Patient" = One Clinic).

---

## 1. Database Schema

**Table:** `control_plane_invoices`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | INT (PK) | Auto-increment |
| `clinic_id` | INT (FK) | Links to `clinics.id` |
| `invoice_number` | VARCHAR(20) | Unique (e.g., `SAAS-2024-0001`) |
| `status` | ENUM | `draft`, `sent`, `paid`, `overdue`, `void`, `cancelled` |
| `issue_date` | DATE | Date invoice was issued |
| `due_date` | DATE | Payment deadline |
| `paid_at` | DATETIME | Timestamp of payment |
| `currency` | CHAR(3) | Default: USD |
| `subtotal` | DECIMAL(10,2) | Sum of items |
| `tax_amount` | DECIMAL(10,2) | Tax applied |
| `total_amount` | DECIMAL(10,2) | Final payable amount |
| `items_json` | JSON | Array of `{ description, quantity, unit_price, total }` |
| `notes` | TEXT | Public notes visible to client |
| `private_notes` | TEXT | Internal admin notes |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |

---

## 2. Models

### `App\Models\ControlPlaneInvoiceModel`
*   **Namespace:** `App\Models`
*   **Extends:** `CodeIgniter\Model`
*   **Features:**
    *   `generateInvoiceNumber()`: Auto-increment logic.
    *   `getInvoicesWithClinic()`: Join with `clinics` table for listing.
    *   `calculateTotals($items)`: Helper to compute subtotal/tax/total.

---

## 3. Controller

### `App\Controllers\ControlPlane\Invoices`
*   **Namespace:** `App\Controllers\ControlPlane`
*   **Route Group:** `controlplane/invoices`

**Methods:**
1.  **`index()`**:
    *   **View:** `control_plane/invoices/index`
    *   **Logic:** List paginated invoices. Filters: Status, Clinic, Date Range.
    *   **UI:** Unified Table Card (Glassmorphism).

2.  **`create()`**:
    *   **View:** `control_plane/invoices/create`
    *   **Logic:** Show form. Fetch list of Active Clinics for dropdown.
    *   **UI:** "Glass Form" with dynamic line item repeater.

3.  **`store()`**:
    *   **Logic:** Validate input. Calculate totals. Generate Invoice Number. Save to DB.
    *   **Audit:** Log `invoice_create`.

4.  **`show($id)`**:
    *   **View:** `control_plane/invoices/view`
    *   **Logic:** Display invoice details. Actions: Print, Email, Mark Paid, Void.
    *   **UI:** Professional Invoice Template (SaaS Branding).

5.  **`markPaid($id)`**:
    *   **Logic:** Update status to `paid`, set `paid_at`.
    *   **Audit:** Log `invoice_paid`.

6.  **`email($id)`**:
    *   **Logic:** Generate PDF (or HTML link), send to Clinic Admin email.
    *   **Audit:** Log `invoice_sent`.

---

## 4. Views (UI/UX)

### A. List Page (`index.php`)
*   **Design:** Unified Table Card.
*   **Columns:** Invoice #, Clinic, Date, Due Date, Amount, Status (Badge), Actions.
*   **Stats Cards:** Total Outstanding, Revenue this Month, Overdue Count.

### B. Create Page (`create.php`)
*   **Design:** Glass Form.
*   **Sections:**
    *   **Clinic Selection:** Searchable Dropdown (Select2).
    *   **Invoice Details:** Issue Date, Due Date.
    *   **Line Items:** Dynamic JS table (Add Item, Remove Item).
        *   Cols: Description, Qty, Price, Total.
    *   **Summary:** Subtotal, Tax %, Total (Auto-calculated via JS).

### C. Invoice Document (`view.php`)
*   **Design:** Clean, White Paper style (Print-friendly).
*   **Header:** SaaS Logo, "Bill From" (Platform details), "Bill To" (Clinic details).
*   **Content:** Line Items Table.
*   **Footer:** Payment Instructions (Bank details, Stripe link placeholder).

---

## 5. Workflow Integration

1.  **Sidebar:** Add "Invoices" under "Billing" section in `main_control_plane.php`.
2.  **Clinic Detail:** Add "Invoices" tab in `control_plane/clinic_detail.php` to see invoices specific to that clinic.

---

## 6. Future Expansion (Not in V1)
*   **Stripe Webhooks:** Auto-create invoices when Stripe charges subscription.
*   **PDF Generation:** Use `dompdf` or `mpdf` for true PDF attachments (V1 uses Print-to-PDF).
*   **Client Portal:** Allow Clinic Admins to log in and view their SaaS invoices.
