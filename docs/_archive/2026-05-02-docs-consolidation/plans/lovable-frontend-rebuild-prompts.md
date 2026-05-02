# Denta+ Frontend Rebuild Prompt Pack For Lovable

## Why This File Exists
This file is a serious frontend rebuild brief for Lovable.

The goal is to redesign the entire Denta+ frontend from scratch while preserving:
- the current backend routes
- the current request/response contracts
- the database schema
- RBAC and permission logic
- tenant isolation
- control-plane isolation
- current business logic

This file is intentionally detailed. Lovable needs real product context, not just page names.

Each prompt below is written to give Lovable:
- product background
- screen purpose
- user context
- entity context
- workflow expectations
- UI/UX constraints
- technical boundaries
- deliverables

---

## 1. Product Summary

### Name
`Denta+`

### Company Context
`Palsync.net`

### Region
`Ramallah, Palestine`

### Product Type
Multi-tenant dental SaaS for clinics.

### Product Surfaces
1. Tenant plane: clinic-facing application.
2. Control plane: platform super-admin application.
3. Public / low-friction flows: auth and kiosk.

### Product Tone
The product should feel:
- premium
- operational
- medically credible
- bilingual by design
- calm, readable, and efficient

This should not feel like:
- a generic startup dashboard
- a decorative concept app
- an over-glassy admin template
- a hospital EMR that is too dense and exhausting

---

## 2. Critical Constraints Lovable Must Respect

Use this block in every Lovable session.

```text
You are redesigning frontend only.
Do not require unnecessary backend or database changes.
Preserve the current backend route structure, payloads, response shapes, permissions, tenant isolation, and control-plane isolation.

Important platform constraints:
- Tenant plane requires active clinic context.
- Control plane requires global mode and must remain isolated from tenant context.
- RBAC permissions already exist and must be respected.
- Existing backend routes are fixed unless frontend-only mapping is enough.
- Do not redesign in a way that forces schema changes.
- /api endpoints are not normal HTML screens.
- Odontogram visuals must never be mirrored in RTL.
- WhatsApp operational monitoring and WhatsApp configuration are separate concerns.
```

---

## 3. Language and RTL Rules

Use this block when prompting Lovable about bilingual behavior.

```text
The app is bilingual: English and Arabic.
Arabic must be proper RTL.

However, the following must remain left-to-right even in Arabic:
- phone numbers
- email addresses
- URLs
- invoice numbers
- IDs
- codes / tokens / slugs
- mixed alphanumeric values

Use Arabic numerals, not Hindi/Indic numerals.

RTL quality expectations:
- menu icon spacing must mirror correctly
- button icon spacing must mirror correctly
- card header spacing must mirror correctly
- breadcrumbs must reverse correctly
- form layouts must remain readable
- tables must remain readable
- data chips and badges must remain intentional, not mechanically flipped

Do not mirror the odontogram image or its rendered orientation.
```

---

## 4. Main Modules In The Current App

### Tenant Plane
- Dashboard
- Patients
- Patient create/edit/detail
- Patient attachments
- Patient WhatsApp identity
- Treatment contracts
- Examinations
- Examination calendar
- Appointments
- Appointment calendar
- Odontogram
- Finance
- Finance income/expense
- Finance reports
- Finance payouts
- Treatments
- Prescriptions
- Reports
- Inventory
- Inventory suppliers
- Inventory templates/import
- Inventory usage
- Inventory movements
- Inventory reorder list
- Users
- Roles
- Notifications
- Notification ledger
- Activity log
- Profile
- Settings

### Control Plane
- Entry / gateway
- Dashboard
- Clinics
- Clinic detail
- Plans
- Plan create/edit
- Plan requests
- Invoices
- Users
- Activity log
- Operations
- WhatsApp command center
- Settings
- Clinic onboarding

### Public / Low-Friction
- Login
- Forgot password
- Reset password
- Change password
- Clinic selection
- No-clinic state
- Kiosk check-in
- Contract signing inside kiosk flow

---

## 5. Primary User Types

### Tenant Plane Users
1. Dentist
- cares about patient context, examinations, treatments, prescriptions, odontogram, contracts
- needs focused clinical screens

2. Receptionist / front desk
- cares about appointments, patients, contact details, reminders, check-in
- needs speed and clarity

3. Clinic manager / admin
- cares about settings, users, roles, reports, finance overview, communications
- needs structured administrative UX

4. Finance / billing operator
- cares about income, expense, payouts, reports, finance entries, audit clarity
- needs trustworthy forms and dense readable tables

### Control Plane Users
1. Platform super admin
- manages clinics, plans, defaults, governance, invoices, onboarding, settings

2. Platform ops / support staff
- monitors operations, audits, WhatsApp status, clinic issues, platform tools

### Public Users
1. Patient at kiosk
- may be stressed, older, hurried, or non-technical
- needs low-anxiety, touch-first design

---

## 6. Main Data Objects Lovable Should Keep In Mind

- Clinic
- User
- Role / permission
- Subscription plan
- Plan request
- Control-plane invoice
- Patient
- Examination
- Appointment
- Odontogram
- Treatment
- Treatment contract
- Prescription
- Finance transaction
- Provider payout
- Inventory item
- Inventory supplier
- Inventory movement
- Inventory usage record
- Notification
- Notification channel
- WhatsApp identity / delivery / policy / ops state
- Audit / activity event

These relationships should influence page hierarchy, filters, summaries, and actions.

---

## 7. Route Families Lovable Must Not Fight

Lovable should understand the route families even if the frontend layout changes.

### Auth / public
- `/auth/*`
- `/clinic/select`
- `/clinic/no-clinic`
- `/kiosk/{token}/check-in`

### Tenant
- `/dashboard`
- `/patients/*`
- `/contracts/*`
- `/examinations/*`
- `/appointments/*`
- `/odontogram/*`
- `/finance/*`
- `/treatments/*`
- `/prescriptions/*`
- `/reports/*`
- `/inventory/*`
- `/users/*`
- `/roles/*`
- `/notifications*`
- `/activity-log`
- `/profile`
- `/settings*`

### Control plane
- `/controlplane/dashboard`
- `/controlplane/clinics*`
- `/controlplane/plans*`
- `/controlplane/plan-requests*`
- `/controlplane/invoices*`
- `/controlplane/users*`
- `/controlplane/activity-log*`
- `/controlplane/operations`
- `/controlplane/whatsapp`
- `/controlplane/settings`
- `/controlplane/clinic/create`

---

## 8. Product-Wide UX Principles

- Lists should be searchable, filterable, and action-oriented.
- Detail pages should begin with identity + summary.
- Forms should be grouped into semantic sections.
- Save feedback should be clear and immediate.
- Validation should be both inline and form-aware.
- Destructive actions should be separated visually from primary actions.
- Settings should be directly linkable by section.
- Communications configuration should be separate from communications operations.
- Print layouts are first-class, not afterthoughts.
- Arabic quality must look deliberate, not machine-flipped.

---

## 9. How To Use This File

Recommended order:
1. Prompt 00
2. Prompt 01
3. Prompt 02
4. Prompt 03
5. Prompt 04
6. Prompt 05
7. Prompt 06 onward by module
8. Final synthesis prompts at the end

If Lovable starts making backend assumptions, reuse the backend constraint block.

---

# Prompt Pack

## Prompt 00 - Master Bootstrap Prompt
```text
You are redesigning the full frontend of Denta+, a bilingual multi-tenant dental SaaS by Palsync.net in Ramallah, Palestine.

This product has three surfaces:
1. Tenant plane for clinic staff
2. Control plane for platform super admins
3. Public/auth/kiosk flows

Your task is frontend redesign only.
Do not require unnecessary backend or database changes.
Preserve routes, request/response contracts, permissions, tenant isolation, and control-plane isolation.

Important realities:
- Clinics use this app all day for patients, appointments, examinations, treatments, prescriptions, finance, reports, inventory, users, settings, and communications.
- The control plane manages clinics, plans, invoices, defaults, governance, platform operations, and WhatsApp infrastructure.
- The app must support English and Arabic.
- Arabic must be proper RTL.
- Phone numbers, emails, IDs, invoice numbers, codes, URLs, and mixed alphanumeric values must remain left-to-right in Arabic.
- Use Arabic numerals, not Hindi numerals.
- Odontogram visuals must never be mirrored.
- WhatsApp operations and WhatsApp configuration are separate concerns.

Design goals:
- premium medical SaaS
- calm and credible
- strong scanning efficiency
- clean page hierarchy
- excellent forms
- excellent tables
- excellent settings IA
- responsive across desktop, tablet, and mobile

Before designing modules, produce:
1. overall design direction
2. shell principles for tenant, control plane, and public surfaces
3. design tokens
4. component system
5. RTL/LTR rules
6. page implementation order

Assume backend is fixed. The frontend must adapt to it.
```

## Prompt 01 - Product Design Direction
```text
Using the Denta+ context above, define the full product design direction.

I want:
- the visual personality
- the difference between clinic-facing and platform-facing experiences
- the right density level for each plane
- the right emotional tone for a dental SaaS
- what makes this product feel more professional than a generic dashboard template

Also define what the product must avoid:
- generic startup-dashboard sameness
- excessive decoration
- weak contrast
- weak hierarchy
- poor RTL behavior
- excessive whitespace that hurts workflow
- enterprise heaviness that overwhelms smaller clinics
```

## Prompt 02 - Design System and Tokens
```text
Create the full Denta+ design system.

Return:
- typography system
- font strategy for English and Arabic
- spacing scale
- radius scale
- shadow/elevation system
- semantic color system
- icon system
- chart style system
- card style system
- table system
- form control system
- motion rules

Requirements:
- suitable for medical and financial workflows
- readable in English and Arabic
- shareable across tenant plane and control plane
```

## Prompt 03 - Bilingual, RTL, and Formatting Rules
```text
Define the bilingual and bidi system for Denta+.

Include rules for:
- sidebar items
- page headers
- breadcrumbs
- tabs and subtabs
- buttons with icons
- cards
- tables
- filters
- forms
- badges and pills
- mixed-language values
- phone/email/code formatting
- dates, numbers, and currencies

I want concrete design guidance, not general statements.
```

## Prompt 04 - Shell Architecture
```text
Design the shell architecture for:
1. tenant plane
2. control plane
3. public/auth/kiosk flows

For each shell, define:
- navigation model
- sidebar vs topbar behavior
- search placement
- notifications placement
- profile placement
- clinic switcher behavior if relevant
- breadcrumb strategy
- mobile navigation strategy
- spacing rhythm
- max-width rules

The tenant shell should optimize operational speed.
The control-plane shell should optimize oversight and governance.
The public shell should optimize clarity and confidence.
```

## Prompt 05 - Shared Component Library
```text
Define the reusable component library for Denta+.

Components must include:
- app shell header
- sidebar nav item
- breadcrumb
- page intro/header
- KPI card
- chart card
- section card
- summary strip
- table toolbar
- table
- row actions
- empty state
- zero-results state
- inline validation
- validation summary
- toast
- modal
- drawer
- confirmation dialog
- tab bar
- subtab bar
- settings section links
- form section
- sticky save area
- file upload block
- attachment list
- audit item
- printable header

For each component define:
- purpose
- anatomy
- variants
- states
- RTL rules
- mobile behavior
```

## Prompt 06 - Whole Product Information Architecture
```text
Using the real Denta+ scope, propose the best frontend information architecture while keeping backend contracts intact.

Modules:
- auth
- clinic selection
- dashboard
- patients
- contracts
- examinations
- appointments
- odontogram
- finance
- treatments
- prescriptions
- reports
- inventory
- users
- roles
- notifications
- activity log
- profile
- settings
- control-plane dashboard
- clinics
- plans
- plan requests
- invoices
- control-plane users
- control-plane activity log
- operations
- WhatsApp command center
- control-plane settings
- clinic onboarding
- kiosk

For each module define:
- primary audience
- main user goal
- nav placement
- whether it is list-first, detail-first, form-first, or operations-first
- primary actions
```

## Prompt 07 - Auth Experience
```text
Design the full auth experience for Denta+.

Pages:
- login
- forgot password
- reset password
- change password

Context:
- users may be tenant staff or platform admins
- trust matters more than theatrics
- the design should be clear, secure, and bilingual

Deliver:
- layout concept
- hierarchy
- validation UX
- error messaging
- success messaging
- mobile behavior
```

## Prompt 08 - Clinic Selection and No-Clinic State
```text
Design the clinic selection and clinic switching UX.

Context:
- some users belong to multiple clinics
- clinic context is required before entering the tenant app
- some users may have no assigned clinics

Design:
- clinic selection page
- no-clinic page
- in-app clinic switcher pattern

Show:
- what clinic identity to display
- what role or plan info to display
- how switching should behave without causing confusion
```

## Prompt 09 - Tenant Dashboard
```text
Design the tenant dashboard for Denta+.

Audience:
- dentist
- receptionist
- clinic admin

The dashboard should act as a daily clinic command center.

It should surface:
- today's appointments
- recent examinations
- recent patients
- revenue summary
- operational alerts
- quick actions
- notification awareness

I want:
- a layout strategy
- hierarchy strategy
- chart choices
- KPI choices
- quick action strategy
- empty-state strategy for new clinics
```

## Prompt 10 - Patients Index
```text
Design the patients list page.

Context:
- very high-frequency page
- users need to find patients quickly
- users often want to jump from patient search into appointment, examination, or edit flows

The page should support:
- fast search
- useful filters
- strong row identity
- multilingual names
- readable contact info
- quick actions

Deliver:
- desktop layout
- tablet/mobile adaptation
- empty state
- no-results state
- loading state
```

## Prompt 11 - Patient Create and Edit
```text
Design the patient create and edit forms.

Requirements:
- fast front-desk workflow
- strong inline validation
- date UX that is unambiguous
- phone validation UX that is clear and forgiving
- no invalid save attempts
- support Arabic and English names
- include medical alerts and notes
- handle user mistakes gracefully

Important:
- field errors should clear when the data is corrected
- the form should feel polished, not like a raw CRUD form
```

## Prompt 12 - Patient Detail
```text
Design the patient detail page as a patient command center.

It should unify:
- identity
- contact details
- medical alerts
- notes
- attachments
- examinations
- appointments
- treatments
- contracts
- prescriptions
- finance context when useful
- WhatsApp identity

The page should help staff understand the patient in seconds.
Describe the hierarchy, sections, actions, and tab strategy if any.
```

## Prompt 13 - Treatment Contracts
```text
Design the treatment contracts module.

Screens:
- create contract
- edit contract
- view contract
- print contract
- sign contract

Context:
- contracts are legally and financially important
- must feel serious, readable, and printable
- related to patients, treatments, pricing, signatures, and odontogram snapshots

I want:
- form structure
- summary/review strategy
- signing UX
- print UX
```

## Prompt 14 - Examinations Index and Calendar
```text
Design the examinations list and calendar experiences.

Context:
- used by clinical staff
- should show patient, provider, date, status, and summary context
- must support both list scanning and time-based planning

Deliver:
- list/index page
- calendar page
- filters
- row/card actions
- empty states
- loading strategy
```

## Prompt 15 - Examination Create and Edit
```text
Design the create/edit examination workflow.

Requirements:
- patient context visible during entry
- structured findings plus free notes
- easy movement into treatments and odontogram-related work
- efficient repeated use throughout the day
- safe completion state UX

Explain:
- form grouping
- context side panels if appropriate
- save/complete patterns
- error handling
```

## Prompt 16 - Examination Detail and Print
```text
Design the examination detail page and print layout.

Requirements:
- clinical readability
- chronology
- provider authorship
- patient context
- related treatments/prescriptions
- print-friendly structure
```

## Prompt 17 - Appointments Index and Calendar
```text
Design the appointments module list and calendar views.

Audience:
- receptionist
- clinic admin
- dentist

Requirements:
- date-first workflow support
- doctor/chair/status filters
- strong patient identity
- efficient scheduling visibility
- list and calendar experiences should feel like one system
```

## Prompt 18 - Appointment Create and Edit
```text
Design the appointment create/edit workflow.

Requirements:
- patient selection
- provider/chair selection
- time and duration selection
- conflict prevention UX
- reminder/contact cues
- smooth rescheduling UX
- clear validation

The result should feel fast and dependable.
```

## Prompt 19 - Appointment Detail and Print
```text
Design the appointment detail page and its print layout.

Include:
- patient quick profile
- appointment metadata
- provider/chair details
- contact/reminder context
- timeline or status context if useful
- print clarity
```

## Prompt 20 - Odontogram Workspace
```text
Design the odontogram experience.

Screens:
- odontogram entry/list
- odontogram workspace
- export / print / PDF

Critical constraint:
- do not mirror the odontogram image or its rendered orientation in RTL

Requirements:
- focused workspace
- intuitive tool placement
- clear patient context
- clear save state
- readable export/print layout
```

## Prompt 21 - Finance Home
```text
Design the tenant finance home page.

Context:
- used by clinic managers and finance operators
- should balance overview and actionability
- should support both income and expense logic
- provider payouts matter
- outstanding values matter

The page should surface:
- KPIs
- trends
- recent activity
- important financial actions
- payout visibility
```

## Prompt 22 - Finance Create and Edit
```text
Design the finance transaction create/edit flows.

Flows:
- add income
- add expense
- edit transaction

Requirements:
- distinguish income from expense clearly
- support patient/examination linkage where relevant
- support attachments/check scans
- support payment method details
- strong validation and save-state UX
```

## Prompt 23 - Finance Detail, Reports, Payouts, and Invoice-Like Views
```text
Design the rest of the finance module as one coherent family:
- transaction detail
- finance reports
- provider payouts
- invoice-like transaction view if useful

Context:
- backend exposes accrual and cash-aware concepts
- payouts and outstanding payable matter
- audit clarity matters

I want:
- page hierarchy
- summary strategy
- chart strategy
- filter strategy
- action placement
```

## Prompt 24 - Treatments Module
```text
Design the treatments module.

Screens:
- treatments index
- create treatment
- edit treatment
- treatment detail
- print treatment

Requirements:
- strong links to patient and examination context
- clear status visibility
- readable clinical and billing context
- efficient repeated workflow
```

## Prompt 25 - Prescriptions Module
```text
Design the prescriptions module.

Screens:
- prescriptions index
- create prescription
- edit prescription
- prescription detail
- print prescription

Requirements:
- medication readability is critical
- dosage and instructions must stand out
- mixed Latin medicine names must still read cleanly in Arabic UI
- print must be first-class
```

## Prompt 26 - Reports Hub
```text
Design the reports module as a reporting hub.

Requirements:
- multiple report types
- date-range heavy workflows
- summary cards
- charts
- detail tables
- export flows
- good handling of empty and no-data states

This should feel like a serious analytics workspace.
```

## Prompt 27 - Inventory Core
```text
Design the inventory core screens.

Screens:
- inventory index
- create item
- edit item
- item detail
- low stock

Requirements:
- stock health visibility
- supplier and reorder context
- SKU/unit clarity
- batch/lot readiness
- clear actions for receive, adjust, and usage
```

## Prompt 28 - Inventory Operations
```text
Design the operational inventory screens:
- suppliers
- starter templates / import
- movements ledger
- reorder list
- usage history
- usage detail / usage print

Context:
- inventory is not only record-keeping; it drives action
- some inventory events tie into finance logic

I want layouts that help staff act quickly.
```

## Prompt 29 - Users Module
```text
Design the tenant users management experience.

Screens:
- users index
- create user
- edit user
- user detail
- change password

Requirements:
- clear role context
- status visibility
- safe security actions
- searchable management view
- clean distinction between profile data and security controls
```

## Prompt 30 - Roles and Permissions
```text
Design the roles and permissions module.

Screens:
- roles index
- create role
- edit role
- role detail / permissions

Context:
- permissions can be complex
- the UI must make them understandable without hiding useful detail

Show how permissions should be grouped, scanned, filtered, and explained.
```

## Prompt 31 - Notifications and Activity
```text
Design:
- tenant notifications center
- tenant notification ledger
- tenant activity log

Requirements:
- clear distinction between live notifications and historical audit/ledger views
- filters and search
- readable event/timeline patterns
- no noisy or clumsy interactions
```

## Prompt 32 - Profile and Personal Account
```text
Design the personal profile/account area.

Requirements:
- simple and reliable
- update personal information
- password change entry point
- account context
- no unnecessary complexity
```

## Prompt 33 - Tenant Settings Information Architecture
```text
Design the tenant settings experience from scratch.

Current scope it must support:
- clinic profile
- subscription
- system
- working hours / operations
- kiosk
- backup & restore
- communications overview
- channels
- email
- SMS
- WhatsApp
- alerts

Requirements:
- modern settings IA
- direct section linkability
- clean hierarchy
- safe admin feel
- better discoverability than a long generic settings page
```

## Prompt 34 - Tenant Settings Detailed Screens
```text
Design the detailed tenant settings screens using the IA from the previous prompt.

For each section define:
- layout
- cards/sections
- primary and secondary actions
- validation expectations
- save behavior
- dangerous action treatment if any
- bilingual details

Give special care to:
- system formats and language
- working hours
- kiosk enablement and kiosk link sharing
- backup and restore
```

## Prompt 35 - Tenant Communications Settings
```text
Design the tenant communications settings surfaces.

Sections:
- communications overview
- channels
- email
- SMS
- WhatsApp
- alerts

Context:
- channel enablement can be plan-gated or platform-gated
- some things are configuration
- some things are operational status indicators

Requirements:
- status clarity
- test-action clarity
- reduced confusion between platform governance, clinic configuration, and runtime behavior
```

## Prompt 36 - Control Plane Shell and Dashboard
```text
Design the control-plane shell and dashboard.

Context:
- used by platform admins and ops staff
- must feel distinct from the clinic plane
- should support denser information without becoming ugly or chaotic

The dashboard should surface:
- clinic health
- billing and plan signals
- onboarding/request signals
- operational alerts
- shortcuts to top admin actions
```

## Prompt 37 - Control Plane Clinics
```text
Design the control-plane clinics module.

Screens:
- clinics index
- clinic detail

Requirements:
- searchable clinic roster
- plan and subscription visibility
- lifecycle actions like impersonate, suspend, reactivate, terminate, or delete where allowed
- detail page should summarize commercial, operational, and access context
```

## Prompt 38 - Control Plane Plans and Plan Requests
```text
Design the control-plane plan management and plan request workflows.

Screens:
- plans index
- create/edit plan
- plan requests queue

Requirements:
- feature and limit clarity
- pricing clarity
- strong admin review patterns
- safe approve/reject actions
```

## Prompt 39 - Control Plane Invoices
```text
Design the control-plane invoicing module.

Screens:
- invoices index
- create invoice
- invoice detail

Requirements:
- strong billing clarity
- line item readability
- payment logging UX
- email/send actions
- mark-paid actions
- safe delete behavior for allowed statuses
```

## Prompt 40 - Control Plane Users and Activity Log
```text
Design:
- control-plane users index
- control-plane user detail with clinic memberships
- control-plane activity log

Requirements:
- admin-grade data density
- strong readability
- membership visibility
- safe password reset and status actions
- export-friendly activity browsing
```

## Prompt 41 - Control Plane Operations Console
```text
Design the control-plane operations console.

Context:
- this is not just another settings page
- it supports platform tooling, health, maintenance-like workflows, and enforcement visibility

Requirements:
- strong operational hierarchy
- visible warnings and health blocks
- tools that feel serious and controlled
- clear distinction from configuration screens
```

## Prompt 42 - Control Plane WhatsApp Command Center
```text
Design the control-plane WhatsApp command center.

Context:
- this is an operations and monitoring surface
- separate from static configuration
- covers live delivery stats, journal/ledger, health, opt-outs, emergency pause, and operational actions

Requirements:
- clear distinction between metrics, logs, and actions
- good dense-table readability
- incident-friendly layout
```

## Prompt 43 - Control Plane Settings
```text
Design the control-plane settings experience from scratch.

Current scope:
- platform profile
- clinic defaults
- governance
- communications
- email templates
- WhatsApp configuration

Requirements:
- as coherent and polished as tenant settings
- each major section/card should be directly linkable
- strong page hierarchy
- readable card headers
- settings grouped by administrative logic, not accidental implementation history
```

## Prompt 44 - Control Plane Clinic Onboarding
```text
Design the control-plane clinic onboarding flow.

Requirements:
- create clinic
- choose plan
- assign initial admin
- set defaults
- reduce setup mistakes
- provide a strong review and completion state

This should feel like a fast but serious admin workflow.
```

## Prompt 45 - Kiosk Self Check-In
```text
Design the patient kiosk self-check-in experience.

Subflows:
- appointment lookup
- unlock if needed
- confirm appointment
- update details
- sign contract
- reset session

Requirements:
- touch-first
- low anxiety
- large controls
- high legibility
- suitable for a clinic lobby
- bilingual
```

## Prompt 46 - Print and Export System
```text
Design a unified print/export design system for Denta+.

Surfaces:
- appointments
- examinations
- treatments
- prescriptions
- contracts
- odontogram
- finance reports
- control-plane invoices
- inventory usage

Requirements:
- consistent metadata blocks
- clean print typography
- clinic branding where relevant
- bilingual readiness
- no decorative clutter that hurts legibility
```

## Prompt 47 - States, Validation, and Feedback System
```text
Define the state and feedback system for Denta+.

Include:
- loading states
- skeletons
- empty states
- zero-results states
- field validation
- form-level validation summary
- save success states
- error recovery patterns
- permission denied states
- subscription-limited states
- destructive confirmations
- toasts and inline notices

This should become a product-wide standard.
```

## Prompt 48 - Responsive and Accessibility Strategy
```text
Define the responsive and accessibility system for Denta+.

Requirements:
- desktop-first for dense workflows
- tablet support for clinic floor use
- mobile-friendly key actions
- strong focus states
- keyboard support
- good contrast
- screen reader semantics
- bilingual accessibility support

Explain how major modules should adapt across breakpoints.
```

## Prompt 49 - Final Frontend Blueprint
```text
Now synthesize everything into a final Lovable implementation blueprint for Denta+.

Return:
1. final design direction
2. final design system summary
3. tenant shell blueprint
4. control-plane shell blueprint
5. public/kiosk shell blueprint
6. page-by-page sitemap
7. component map
8. recommended implementation order
9. highest-risk UX flows needing extra QA
10. summary of RTL/LTR rules
11. summary of backend constraints the frontend must preserve

This output should be implementation-ready for a real frontend rebuild.
```

---

## Appendix A - Reusable Reminder Preamble For Lovable

Use this before module prompts if Lovable loses context.

```text
Use the previously defined Denta+ design system and product rules.
Keep the backend intact.
Do not invent unnecessary new endpoints.
If you propose a better frontend information architecture than the current app, make sure it still fits on top of the existing backend route families.
Keep bilingual and RTL behavior professional.
Keep phone, email, URL, code, token, and ID values left-to-right in Arabic.
Do not mirror odontogram visuals.
Favor reusable components and operational clarity over novelty.
```

## Appendix B - Suggested Execution Order
1. Prompt 00
2. Prompt 01
3. Prompt 02
4. Prompt 03
5. Prompt 04
6. Prompt 05
7. Prompt 06
8. Prompt 07-08
9. Prompt 09-35
10. Prompt 36-44
11. Prompt 45
12. Prompt 46-49

## Appendix C - Quality Bar For Lovable Outputs
A good Lovable response should include:
- explicit hierarchy
- layout reasoning
- reusable component reasoning
- state handling
- RTL details
- responsive details
- clear workflow support
- backend boundary awareness

A weak response is one that only gives:
- generic cards and tables
- vague statements
- no hierarchy reasoning
- no Arabic / RTL handling
- no difference between tenant and control plane
- no respect for backend boundaries
