# GR-RAW-01 Verification

**Task:** GR-RAW-01 (Raw tenant query cleanup)
**Date:** 2026-01-20
**Status:** PASS

## 1) Raw Query Inventory and Disposition

1) `app/Controllers/DebugController.php:205`
   - **What:** Direct `role_permissions` table query in controller.
   - **Reachability:** No explicit route; controller is not in routes file.
   - **Protection:** IonAuth admin gate in controller.
   - **Decision:** Refactor to model method (`RoleModel::getAllRolePermissions`) to remove controller raw query.

2) `app/Controllers/RepairController.php:20`
   - **What:** Raw ALTER TABLE on `finances` (add total_amount).
   - **Reachability:** No explicit route; controller is not in routes file.
   - **Protection:** None (controller-level only).
   - **Decision:** Remove controller file; repairs are not a tenant surface.

3) `app/Controllers/RepairController.php:31`
   - **What:** Raw ALTER TABLE on `finances` currency enum.
   - **Reachability:** No explicit route; controller is not in routes file.
   - **Protection:** None.
   - **Decision:** Remove controller file.

4) `app/Controllers/RepairController.php:48`
   - **What:** Raw ALTER TABLE on `users` (ip_address nullable).
   - **Reachability:** No explicit route; controller is not in routes file.
   - **Protection:** None.
   - **Decision:** Remove controller file.

5) `app/Controllers/RepairController.php:51`
   - **What:** Raw ALTER TABLE on `users` (created_on nullable).
   - **Reachability:** No explicit route; controller is not in routes file.
   - **Protection:** None.
   - **Decision:** Remove controller file.

6) `app/Controllers/RepairController.php:67`
   - **What:** Raw ALTER TABLE on `users` (add hire_date).
   - **Reachability:** No explicit route; controller is not in routes file.
   - **Protection:** None.
   - **Decision:** Remove controller file.

7) `app/Controllers/RepairController.php:69`
   - **What:** Raw ALTER TABLE on `users` (add active).
   - **Reachability:** No explicit route; controller is not in routes file.
   - **Protection:** None.
   - **Decision:** Remove controller file.

8) `app/Controllers/RepairController.php:71`
   - **What:** Raw ALTER TABLE on `users` (add address).
   - **Reachability:** No explicit route; controller is not in routes file.
   - **Protection:** None.
   - **Decision:** Remove controller file.

## 2) Verification Evidence (Verbatim)

### 2.1 Guardrails
Command: `scripts/ci/saas_guardrails.sh`
```
Guardrail 'DOM sinks in app/Views' passed (0 match(es)).
Guardrail 'Group-based auth helpers' passed (0 match(es)).
Guardrail 'Raw tenant queries in controllers' passed (0 match(es)).
SaaS guardrail validation succeeded.
```

### 2.2 Controller Raw Query Scan
Command: `rg -n -- "->table\\(|db->query" app/Controllers`
```
no matches
```

