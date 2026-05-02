# P5-CON-01 Verification

## Summary
- Added treatment contract flow with odontogram snapshot, itemized pricing, and in-app signatures.
- Contract prints are generated from stored snapshots and are downloadable as stored attachments.

## Commands & Evidence

### PHP Lint
```bash
php -l app/Controllers/TreatmentContracts.php
```
Output:
```
No syntax errors detected in app/Controllers/TreatmentContracts.php
```

```bash
php -l app/Views/contracts/create.php
```
Output:
```
No syntax errors detected in app/Views/contracts/create.php
```

```bash
php -l app/Views/contracts/show.php
```
Output:
```
No syntax errors detected in app/Views/contracts/show.php
```

```bash
php -l app/Views/contracts/print.php
```
Output:
```
No syntax errors detected in app/Views/contracts/print.php
```

```bash
php -l app/Database/Migrations/2026-02-05-130000_CreateTreatmentContractsTables.php
```
Output:
```
No syntax errors detected in app/Database/Migrations/2026-02-05-130000_CreateTreatmentContractsTables.php
```

### Routes
```bash
php spark routes | findstr /i "contracts"
```
Output:
```
| GET    | contracts/create/([0-9]+)                               | ?    | \App\Controllers\TreatmentContracts::create/$1                    | csrf forcehttps auth tenant subscription permission:patients      | permission:patients subscription tenant auth secureheaders csrfjson toolbar      |
| GET    | contracts/([0-9]+)                                      | ?    | \App\Controllers\TreatmentContracts::show/$1                      | csrf forcehttps auth tenant subscription permission:patients      | permission:patients subscription tenant auth secureheaders csrfjson toolbar      |
| GET    | contracts/([0-9]+)/print                                | ?    | \App\Controllers\TreatmentContracts::print/$1                     | csrf forcehttps auth tenant subscription permission:patients      | permission:patients subscription tenant auth secureheaders csrfjson toolbar      |
| POST   | contracts/create/([0-9]+)                               | ?    | \App\Controllers\TreatmentContracts::store/$1                     | csrf forcehttps auth tenant subscription permission:patients      | permission:patients subscription tenant auth secureheaders csrfjson toolbar      |
| POST   | contracts/([0-9]+)/sign                                 | ?    | \App\Controllers\TreatmentContracts::sign/$1                      | csrf forcehttps auth tenant subscription permission:patients      | permission:patients subscription tenant auth secureheaders csrfjson toolbar      |
```

## Manual Checks
1. Enable plan features `contracts.enabled` and `contracts.signature.enabled`.
2. Open a patient profile → Contracts tab → Create Contract.
3. Click teeth to set conditions; add treatment items; save draft.
4. View contract, draw signature, and sign; confirm status becomes Signed.
5. Print view should match the snapshot and include signature.
