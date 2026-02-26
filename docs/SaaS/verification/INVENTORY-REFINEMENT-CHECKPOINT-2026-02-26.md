# Inventory Refinement Checkpoint (2026-02-26)

## Scope
Document the inventory + finance integration/refinement work completed in the Feb 26 session sequence, including supplier directory, stock movement ledger, starter templates, reorder workflow, supplier-item mappings, and batch/lot tracking foundation.

## Completed Commit Stack (Inventory/Finance Slice)
- `636174c` `feat(finance): auto-post inventory usage cogs expenses`
- `4f53196` `feat(finance): add inventory cogs auto-post toggle`
- `04b3fe2` `fix(finance): improve inventory cogs toggle UX`
- `e7e9d93` `feat(inventory): link usage records to finance cogs entries`
- `c6f05f1` `feat(inventory): add suppliers and stock movement workflow`
- `535e66b` `feat(inventory): add starter templates and movement ledger UI`
- `3394e73` `feat(inventory): add supplier-item mappings and reorder workflow`
- `92e5ee4` `feat(inventory): add movement ledger and batch tracking foundation`

## What Is Implemented

### 1) Inventory -> Finance COGS Integration
- Inventory usage (inventory flow + treatment-driven flow) can auto-post a Finance `expense` row (`expense_category=inventory_cogs`).
- Posting is idempotent through `finances.source_entity_type` + `source_entity_id`.
- Tenant setting toggle controls auto-post behavior:
  - `finance_auto_post_inventory_cogs`
- Inventory usage UI surfaces whether auto-post is enabled.

### 2) Inventory Usage ↔ Finance Traceability
- Usage history actions and usage details modal link to generated Finance COGS entries (when present).
- Linking uses tenant-safe source references:
  - `source_entity_type='inventory_usage'`
  - `source_entity_id=inventory_usage.id`

### 3) Supplier Directory (Tenant-Scoped)
- `suppliers` table + `SupplierModel`
- Supplier CRUD UI in `/inventory/suppliers`
- Quick-create supplier AJAX from inventory create/edit forms
- Inventory items support:
  - `supplier_id` normalized link
  - legacy/free-text `supplier` snapshot (compatibility)

### 4) Stock Movement Ledger Foundation
- `inventory_stock_movements` table + `InventoryStockMovementModel`
- `InventoryMovementService` records movement events for:
  - initial stock
  - receive stock
  - manual adjustments
  - inventory usage consumption (including treatment flow)
- Item detail page includes **Stock Movement Ledger** section

### 5) Receive Stock Workflow
- `POST /inventory/{id}/receive`
- Item detail page has **Receive Stock** action
- Updates quantity and latest unit price snapshot
- Writes movement ledger entry (`receive_stock`)

### 6) Starter Inventory Templates
- `inventory_item_templates` table seeded with starter dental items
- `/inventory/templates` catalog UI with category/search filters
- Import selected templates into tenant inventory (duplicate-safe)

### 7) Supplier-Item Mappings (Procurement Metadata)
- `inventory_supplier_links` table + `InventorySupplierLinkModel`
- Supports per-item supplier metadata:
  - `supplier_sku`
  - `pack_size_label`
  - `preferred_order_qty`
  - `last_unit_price`
  - `currency`
  - `is_preferred`
- Item detail page has supplier mapping list + add/update form
- Create/edit item forms can seed/update preferred mapping when linked supplier is selected

### 8) Reorder Workflow (Tenant)
- `inventory_reorder_items` table + `InventoryReorderItemModel`
- `/inventory/reorder-list` page
- Add to reorder from:
  - low-stock page
  - item detail page
- Reorder line actions:
  - update requested qty
  - status (`draft`, `ordered`, `received`, `cancelled`)
  - delete line
- Reorder list shows supplier contact shortcuts:
  - phone, email, WhatsApp (when available)

### 9) Supplier Contact Tracking on Reorder Lines
- Added reorder contact audit fields:
  - `supplier_contacted_at`
  - `supplier_contact_method`
  - `supplier_contacted_by`
- `/inventory/reorder-list` supports contact logging (phone/email/whatsapp/other)

### 10) Global Movement Ledger Page
- New tenant page: `/inventory/movements`
- Cross-item movement audit trail with filters:
  - movement type
  - item
  - date range
  - search text
  - row limit

### 11) Batch/Lot Tracking Foundation (Non-Breaking)
- `inventory_batches` table + `InventoryBatchModel`
- `inventory_stock_movements.batch_id` link column
- Receive Stock flow can optionally capture:
  - lot number
  - batch expiry date
- Item detail page shows **Batches / Lots (Foundation)** section

## Tenant Isolation / Safety Notes
- New inventory models (`SupplierModel`, `InventoryStockMovementModel`, `InventorySupplierLinkModel`, `InventoryReorderItemModel`, `InventoryBatchModel`) are tenant-scoped via `TenantAwareModel`.
- New inventory controller actions require active tenant context (`active_clinic_id`) and fail closed.
- Supplier mappings and reorder operations verify item/supplier ownership by clinic before mutation.
- Existing inventory usage item lookup was hardened to tenant scope during this sequence.

## Verification Performed (Commands / Outcomes)

### Routes
Verified route registration for new inventory surfaces/actions:
- `GET /inventory/reorder-list`
- `POST /inventory/reorder-list/add`
- `POST /inventory/reorder-list/{id}/status`
- `POST /inventory/reorder-list/{id}/delete`
- `POST /inventory/reorder-list/{id}/contacted`
- `POST /inventory/{id}/supplier-link`
- `POST /inventory/{id}/supplier-link/{linkId}/delete`
- `GET /inventory/movements`

Command used:
```powershell
php spark routes | findstr /I "inventory/reorder-list supplier-link inventory/movements contacted"
```

### PHP Lint
Ran `php -l` on all touched controllers/models/views/routes/migrations for the above commits:
- `app/Controllers/Inventory.php`
- `app/Models/InventorySupplierLinkModel.php`
- `app/Models/InventoryReorderItemModel.php`
- `app/Models/InventoryStockMovementModel.php`
- `app/Models/InventoryBatchModel.php`
- `app/Services/InventoryMovementService.php`
- `app/Views/inventory/reorder_list.php`
- `app/Views/inventory/movements.php`
- `app/Views/inventory/show.php`
- `app/Views/inventory/low_stock.php`
- `app/Views/inventory/create.php`
- `app/Views/inventory/edit.php`
- `app/Views/inventory/index.php`
- `app/Config/Routes.php`
- migrations `2026-02-26-180000`, `2026-02-26-190000`

Result: **PASS**

### Tailwind Build
```powershell
cmd /c npm.cmd run build-css-prod
```
Result: **PASS**

### Migrations
```powershell
php spark migrate
php spark migrate:status
```
Results:
- `AddInventorySupplierLinksAndReorderList` applied in batch `51`
- `AddInventoryBatchesAndReorderContactTracking` applied in batch `52`

### Docker/MySQL Schema Verification
Verified presence of new inventory tables/columns:
- `inventory_supplier_links`
- `inventory_reorder_items`
- `inventory_batches`
- `inventory.supplier_id`
- `inventory_reorder_items.supplier_contacted_at`
- `inventory_stock_movements.batch_id`

### SQL Dump Regeneration
Ran the canonical dump command after schema changes:
```powershell
cmd /c "docker exec mysql8-dental mysqldump -u root -proot --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```
Result: **PASS**

## Remaining / Next Recommended Inventory Steps
1. FEFO batch consumption logic (consume earliest-expiry batch first when batches exist)
2. Replace receive-stock prompt flow with structured modal/form (lot, expiry, invoice ref, supplier)
3. Purchase/PO workflow connected to reorder list (`draft -> ordered -> received`)
4. Cycle count workflow + discrepancy posting
5. Movement export/reporting and supplier spend analytics

