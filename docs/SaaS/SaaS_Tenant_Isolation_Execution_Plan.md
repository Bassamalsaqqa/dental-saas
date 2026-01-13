# SaaS Implementation Task Breakdown (Engineering-Ready)

## Purpose

This document provides a **phase-by-phase delivery plan** for implementing the DentaCare SaaS platform with **provable tenant isolation, RBAC enforcement, and security guardrails**.

Each phase can be tracked independently in **Jira, Linear, or GitHub Projects** and reviewed as a gated milestone.

---

## PHASE 0 — SaaS Scaffold & Guardrails

**Goal:** Establish a safe foundation before touching any production data.

### Tasks

- Create SaaS repository clone  
- Import `docs/SaaS/*`  
- Add CI checks:
  - ban DOM sinks  
  - ban raw tenant queries  
  - ban group-based auth  
- Freeze legacy repository  

### Definition of Done

- CI fails on forbidden patterns  
- SaaS repository builds clean  

---

## PHASE 1 — RBAC Consolidation

**Goal:** Enforce a single, authoritative authorization system.

### Tasks

- Remove `AdminFilter`  
- Add `ControlPlaneFilter`  
- Remove IonAuth group helpers  
- Refactor controllers to use `PermissionService`  
- Update routes to: `{ auth → tenant → permission }`

### Definition of Done

- No `in_group`, `is_admin`, or group checks exist in the codebase  
- Permission denial behaves consistently across the platform  

---

## PHASE 2 — Clinic Context & Selection Wall

**Goal:** Prevent any access without an active clinic.

### Tasks

- Add `clinics` and `user_clinics` tables  
- Implement post-login clinic resolution  
- Implement **“Choose Clinic”** wall  
- Add clinic switch endpoint + audit logging  
- Add header clinic dropdown  

### Definition of Done

- User cannot reach dashboard without a clinic  
- Switching clinics regenerates session and logs an audit event  

---

## PHASE 3 — Schema Tenancy

**Goal:** All tenant data must be explicitly keyed by clinic.

### Tasks

- Add nullable `clinic_id` to tenant tables  
- Backfill legacy data  
- Enforce `NOT NULL` + composite indexes  
- Add `patients.user_id`  
- Add `clinic_id` to RBAC assignment tables  

### Definition of Done

- No tenant rows exist without `clinic_id`  
- Per-clinic uniqueness is enforced  

---

## PHASE 4 — TenantBaseModel & Query Refactor

**Goal:** Make data isolation the default, not an option.

### Tasks

- Implement `TenantBaseModel`  
- Refactor tenant models to extend it  
- Replace raw queries and datatables  
- Add scoped `find` helpers  
- Enforce CI guards  

### Definition of Done

- Cross-clinic access tests fail correctly  
- CI rejects unsafe queries  

---

## PHASE 5 — Private File Storage

**Goal:** Eliminate all cross-tenant file leakage.

### Tasks

- Implement tenant storage service  
- Move branding uploads to private storage  
- Create file registry table  
- Implement streaming controller  

### Definition of Done

- No files exist under public tenant paths  
- Unauthorized file access returns HTTP 404  

---

## PHASE 6 — Background Jobs & Messaging

**Goal:** Ensure all async work is tenant-safe.

### Tasks

- Create `jobs` table  
- Implement worker with tenant context  
- Implement reminders  
- Implement WhatsApp sending service  
- Add delivery and audit logs  

### Definition of Done

- Jobs cannot execute without `clinic_id`  
- Messages are sent only within the correct clinic  

---

## PHASE 7 — Verification & Hardening

**Goal:** Prove that tenant isolation is real.

### Tasks

- Tenant isolation integration tests  
- Export permission tests  
- File access tests  
- Clinic switch edge-case tests  
- Manual penetration checklist  

### Definition of Done

- All tests pass  
- No cross-tenant leaks are detectable  

---

## Final Statement (for Reviewers)

This plan:

- treats tenancy as a **security boundary**  
- enforces **RBAC as a single authority**  
- prevents **accidental developer bypass**  
- limits **blast radius by design**  
- scales **without rewriting assumptions**  

This is suitable for **serious SaaS**, not merely “multi-user mode”.
