# DentaCare Multi-Tenant Security & Isolation Contract

## Purpose

This document defines the non-negotiable rules governing tenant isolation, clinic selection, authorization, storage, background jobs, and failure behavior in the **DentaCare SaaS platform**.

It is a binding contract between:

- the backend  
- the frontend  
- background workers  
- and future developers  

Violations of this contract are considered **security defects**, not bugs.

---

## 1. Definitions

| Term | Meaning |
|------|--------|
| **Tenant / Clinic** | A logical isolation boundary identified by `clinic_id` |
| **Tenant Plane** | Normal application operation scoped to one clinic |
| **Control Plane** | Platform-level operations (super admin only) |
| **Active Clinic** | The clinic currently selected by the user and stored in session |
| **RBAC** | Role-Based Access Control enforced exclusively via `PermissionService` |

---

## 2. Session Contract (Authoritative)

Session keys are **server-controlled only**.

| Key | Type | Meaning |
|-----|------|--------|
| `active_clinic_id` | `int` / `uuid` | Required for all tenant-plane routes |
| `global_mode` | `bool` | Required for all control-plane routes |
| `is_impersonating` | `bool` | Indicates super-admin impersonation |
| `impersonated_clinic_id` | `int` / `uuid` \| `null` | Target clinic during impersonation |

### Invariants

- Tenant routes **must reject** requests if `active_clinic_id` is missing  
- Tenant routes **must reject** requests if `global_mode = true`  
- Control-plane routes **must reject** requests if `active_clinic_id` exists  
- Session ID **must be regenerated** on clinic switch or impersonation change  

---

## 3. Authentication vs Authorization

### Authentication

- Handled exclusively by **IonAuth**  
- Responsible only for identity and session creation  

### Authorization

- Handled exclusively by **RBAC (`PermissionService`)**  
- IonAuth groups are **never used** for runtime authorization decisions  
- **“Admin” is a permission, not a group**

This aligns with **Plan B’s IonAuth removal strategy**  
(`MULTI_TENANCY_RBAC_MIGRATION`)

---

## 4. Clinic Selection Lifecycle

### 4.1 Post-Login Behavior

After successful authentication:

1. Fetch clinic memberships from `user_clinics`
2. Apply the following logic:

| Membership Count | Behavior |
|------------------|----------|
| `0` | Show **“No clinic assigned”** wall. User cannot proceed |
| `1` | Auto-select clinic → set `active_clinic_id` |
| `>1` | Show **“Choose Clinic”** wall. User must select |

### 4.2 Hard Wall Rule

No:

- dashboard  
- menu  
- API access  

…until `active_clinic_id` is set.

---

## 5. Clinic Switching

### Mechanism

- UI dropdown in header (only if user has >1 clinic)  
- `POST /clinic/switch`

### Server Responsibilities

- Validate membership  
- Set `active_clinic_id`  
- Regenerate session ID  
- Write audit log event: `clinic_switch`  

---

## 6. Tenant Enforcement Layers (Defense in Depth)

### Layer 1 — Route Filters

- `TenantFilter`: requires active clinic + membership  
- `ControlPlaneFilter`: requires `super_admin` + `global_mode`  

### Layer 2 — Data Access

All tenant models extend `TenantBaseModel`.

Automatic `WHERE clinic_id = active_clinic_id` on:

- `SELECT`
- `UPDATE`
- `DELETE`

Inserts force-set `clinic_id` server-side.

### Layer 3 — Scoped Access

- No plain `find(id)` on tenant models  
- All reads are scoped: `(id + clinic_id)`

### Layer 4 — CI Guardrails

Builds fail if:

- tenant tables queried without clinic scoping  
- `raw db->table()` is used in controllers  
- group-based auth appears  

### Layer 5 — Audit Visibility

Suspicious access attempts may log:

- `tenant_violation_attempt` (rate-limited)

---

## 7. Mismatch Handling

A mismatch occurs when a request attempts to access data not belonging to the active clinic.

### Required Behavior

- Return **HTTP 404** (never 403)  
- Do **not** reveal existence of other-tenant records  
- Optionally log audit event  

---

## 8. File Storage Contract (Private, No Leaks)

### Storage Rules

Files stored outside web root:

writable/tenants/{clinic_uuid}/...


- No predictable filenames  
- No public URLs  

### Access Rules

Files accessed **only** via controller streaming endpoint:

- Authenticated  
- Tenant membership verified  
- File ownership (`clinic_id`) verified  

### Guarantee

Even with URL guessing or misconfiguration, **cross-tenant file access is impossible**.

---

## 9. Reporting & Exports

- All reports are **per-clinic only**  
- Export endpoints require:
  - RBAC permission  
  - active clinic  

- Every export creates an **audit log entry**  
- No cross-clinic aggregation for normal users  

---

## 10. Background Jobs & Messaging

### Job Payload Contract

Every job must include:

- `clinic_id`  
- `actor_user_id` (or system)  
- payload data  
- idempotency key  

### Execution Rules

- Workers set tenant context from payload  
- Workers never rely on session state  
- WhatsApp / notifications are **rate-limited per clinic**  
- All sends are **auditable**

---

## 11. Blast Radius Definition (Security Guarantee)

**Maximum blast radius = one clinic**

Even in the presence of bugs:

- A tenant can never read, write, or export another clinic’s data  
- A tenant can never access another clinic’s files  
- A tenant can never affect platform-level state  

---

## 12. Non-Negotiable Summary

If any change violates this contract:

- It is a **security regression**  
- It **blocks release**  
- It **must be fixed before merge**

---

## 13. Fail-Closed Semantics (Authoritative) — 2026-01-16

When required context is missing or invalid, the system must **fail closed** with **no writes** and a deterministic response:

- **Missing `active_clinic_id` on tenant-plane requests:**  
  - HTML: redirect to `/clinic/select`  
  - API: return `403` JSON `{ "error": "TENANT_CONTEXT_REQUIRED" }`

- **Cross-tenant access (record not owned by active clinic):**  
  - HTML: return `404`  
  - API: return `403` JSON  
  - Optional audit event: `tenant_violation_attempt` (rate-limited)

- **Control-plane access without `global_mode`:**  
  - return `404` (or `403` JSON for API)

No silent no-ops. If blocking occurs, it must be observable (response + optional audit row).

---

## 14. Storage Path Correction (Supersedes Section 8)

**Authoritative storage path:**  
`writable/uploads/clinic_{clinic_id}/...`

Section 8’s `writable/tenants/{clinic_uuid}` language is legacy and does not match the implemented StorageService.

