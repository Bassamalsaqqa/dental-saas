# Agent Notes

- Keep `democa_dental_saas_schema_superadmin.sql` updated whenever the schema or superadmin bootstrap data changes.
- The dump must include the IonAuth default group `members` (in addition to `admin`) to avoid "Invalid default group name set" errors.
