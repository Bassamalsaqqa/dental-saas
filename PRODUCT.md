# Denta+

Arabic/English multi-tenant dental clinic operations on CodeIgniter 4, PHP, MySQL/MariaDB and native Studio views. Reception manages patients and visits; clinicians use charts, examinations, treatment, prescriptions and clinical evidence; finance and inventory staff use existing ledgers and stock workflows. Clinic owners and Control Plane operators have distinct authority. Kiosk check-in has its own privacy scope.

The app is deployed and working. This campaign improves correctness, discoverability, workflows, responsiveness and developer experience; it does not restart a frontend rebuild or invent payroll/accounting policy. [WORK_PROGRAM](docs/WORK_PROGRAM.md) is the current task register; [PROJECT_STATE](docs/PROJECT_STATE.md) identifies the installed release. [DESIGN](DESIGN.md) and the shared Studio components guide presentation.

Question every task flow and default against actual user needs and canonical source. An older nontechnical operator should find the task, understand what will happen, finish it and find its history. Preserve supported capabilities, tenant/RBAC enforcement, calculations, signed records and payment history. Source services and settings own facts and mutations. Missing rules require an explicit decision, not fabricated defaults.

Existing layouts and historical acceptance describe their source/time. They do not compel new work to preserve confusing navigation or composition. Deployment, migrations and feature activation retain their separate authorization gates; see [AGENTS](AGENTS.md).
