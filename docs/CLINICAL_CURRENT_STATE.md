# Clinical Workflows Current State

Last updated: 2026-05-02

## Core Modules

- **Patient Profile:** baseline odontogram, demographics, attachments, appointments history
- **Odontogram:** interactive tooth chart with conditions, procedures, and status tracking
- **Examinations:** per-visit clinical exams with exam-scoped odontogram
- **Treatments:** treatment plans linked to examinations
- **Treatment Contracts:** frozen odontogram snapshots (SVG+JSON), itemized treatment lists, e-signature capture
- **Prescriptions:** medication management linked to patients

## Current Charting Model

Three separate charting behaviors exist:

1. **Patient baseline odontogram** — loaded from patient profile, mutable, no longitudinal history
2. **Examination-scoped odontogram** — per-visit chart state with `examination_id`, closest to visit snapshot
3. **Contract snapshot odontogram** — frozen as SVG and JSON at contract creation time

## Known Gaps

- No true longitudinal patient odontogram history (timeline of changes across visits)
- No first-class child / mixed dentition support
- Contract creation artificially forced from examination flow
- Contracts currently seeded from patient baseline odontogram, not exam odontogram
- Examination/treatment workflow boundary needs clearer product concept

## Key Files

- Patient controller: `app/Controllers/Patient.php`
- Examination controller: `app/Controllers/Examination.php`
- Odontogram controller: `app/Controllers/Odontogram.php`
- Treatment controller: `app/Controllers/Treatment.php`
- Treatment contracts: `app/Controllers/TreatmentContracts.php`

## Archived Audit

The full clinical workflow audit (2026-04-12) is archived in `docs/_archive/2026-05-02-docs-consolidation/audits/`.
