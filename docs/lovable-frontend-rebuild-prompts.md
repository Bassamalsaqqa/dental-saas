# Denta+ Lovable Frontend Rebuild Prompt Pack

## Purpose
This file contains a complete prompt sequence for rebuilding the frontend of **Denta+** in **Lovable** from scratch while **preserving the existing backend, routes, permissions, tenant isolation, and database schema**.

The product is:
- Brand: `Denta+`
- Company context: `Palsync.net`
- Region: `Ramallah, Palestine`
- Type: `multi-tenant dental SaaS`
- Languages: `English` and `Arabic`
- Modes: `Tenant plane`, `Control plane`, and public `Kiosk / Auth`

## Non-Negotiable Constraints For Every Prompt
Use these rules in every Lovable prompt:

1. Do not change backend routes, controllers, request payloads, response shapes, permissions, or database schema.
2. Build frontend only. Use adapters/mappers if needed, but keep backend contracts intact.
3. Treat tenant plane and control plane as separate products with related branding, not one mixed dashboard.
4. Support English and Arabic from day one.
5. Arabic must be proper RTL, but:
   - phone numbers stay left-to-right
   - emails stay left-to-right
   - Latin codes/IDs stay left-to-right
   - use Arabic numerals, not Hindi/Indic numerals
6. Keep odontogram images/assets in their original orientation. Do not mirror the rendered odontogram image in RTL.
7. Follow professional SaaS design standards:
   - strong hierarchy
   - clear information architecture
   - predictable save/cancel patterns
   - inline validation
   - empty states
   - loading states
   - responsive desktop/tablet/mobile behavior
8. Generate production-grade UI/UX, not demo-grade glassmorphism for its own sake.
9. Favor composable reusable patterns: app shell, page header, filters bar, table layout, detail header, form sections, drawers, modals, toasts.
10. Make the design feel modern, premium, medical-professional, and calm.

## Recommended Execution Strategy
Ask Lovable to work in this order:
- Foundation
- Shared components
- Auth/public flows
- Tenant shell
- Tenant modules
- Tenant settings and profile
- Control-plane shell
- Control-plane modules
- Kiosk/public flows
- System states and QA polish

## Prompt 01 - Product Foundation
```text
Build a complete frontend design system and app architecture for a bilingual dental SaaS called Denta+ by Palsync.net, based in Ramallah, Palestine.

Important constraints:
- Rebuild frontend only.
- Do not change backend routes, payloads, permissions, or database schema.
- The product has three surfaces: tenant plane, control plane, and public kiosk/auth flows.
- The app must support English and Arabic with proper RTL/LTR behavior.
- Arabic must keep phone numbers, emails, IDs, and mixed-language values left-to-right.
- Use Arabic numerals, not Hindi numerals.
- Odontogram images/assets must never be mirrored in RTL.

Deliver:
1. Visual direction for the full product.
2. Design tokens: colors, typography, spacing, shadows, radii, motion, icon usage.
3. Component inventory.
4. Separate navigation models for tenant plane and control plane.
5. Page shell rules for list, detail, form, analytics, settings, and public pages.
6. Validation, toast, modal, destructive action, and loading-state standards.
7. Responsive behavior standards.

Style direction:
- premium medical SaaS
- clean and credible
- not generic startup purple
- not over-glassy
- strong readability
- high scanning efficiency
- works in both English and Arabic
```

## Prompt 02 - Global UX Rules
```text
Define a complete UX interaction standard for Denta+ that all pages will follow.

Include:
- page header pattern
- breadcrumb rules
- primary/secondary/tertiary action hierarchy
- save/cancel/delete patterns
- sticky form footer rules
- table filters and bulk action rules
- inline validation behavior
- validation summary behavior
- toast behavior
- optimistic vs non-optimistic update rules
- empty states
- offline/error states
- permission-denied states
- subscription-limited states
- audit-friendly destructive confirmation patterns

This must be reusable across tenant plane and control plane.
```

## Prompt 03 - Bilingual and RTL System
```text
Create the localization and bidi UX system for Denta+ in English and Arabic.

Requirements:
- full RTL/LTR support
- correct spacing between icons and labels in both directions
- correct button padding and icon alignment in both directions
- correct breadcrumb direction and separators
- forms and tables must remain readable in Arabic
- phone numbers, emails, URLs, codes, IDs, invoice numbers, and mixed alphanumeric values must remain left-to-right
- use Arabic numerals, not Hindi/Indic numerals
- odontogram visuals must not be mirrored

Deliver:
1. RTL/LTR design rules
2. component-level bidi rules
3. typography rules for both languages
4. examples for nav, buttons, forms, tables, badges, cards, breadcrumbs, and data cells
```

## Prompt 04 - App Shells
```text
Design the three top-level shells for Denta+:
1. tenant plane shell
2. control plane shell
3. public shell for auth and kiosk

For each shell, provide:
- sidebar/topbar strategy
- search placement
- notifications placement
- profile and clinic switcher placement where relevant
- mobile navigation behavior
- breadcrumb strategy
- page max-width and spacing rules
- tab and subtab patterns

The tenant shell should feel clinic-operational.
The control-plane shell should feel platform-administrative.
The public shell should feel guided and low-friction.
```

## Prompt 05 - Shared Components Library
```text
Define the reusable component library for Denta+.

Include:
- app header
- sidebar nav item
- breadcrumb
- stat card
- chart card
- section card
- data table
- filter bar
- empty state
- detail hero
- summary strip
- form section
- searchable select
- date input
- phone input
- file upload
- attachment list
- timeline/audit list
- drawer
- modal
- toast
- confirmation dialog
- stepper
- tab bar
- subtab bar
- pill filter
- KPI row
- printable layout

For each component, define:
- purpose
- anatomy
- variants
- RTL/LTR behavior
- mobile behavior
```

## Prompt 06 - Route-to-Page Mapping Strategy
```text
Create a frontend information architecture plan for Denta+ that maps the existing backend routes to a new frontend without changing the backend.

Surfaces to include:
- auth
- clinic selection
- tenant dashboard
- patients
- treatment contracts
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
- control plane dashboard
- control plane clinics
- control plane plans
- control plane plan requests
- control plane invoices
- control plane users
- control plane activity log
- control plane operations
- control plane WhatsApp
- control plane settings
- control plane clinic onboarding
- kiosk check-in

Show the best modern IA even if it differs from the current UI, but do not require backend changes.
```

## Prompt 07 - Auth Experience
```text
Design the full authentication experience for Denta+.

Pages:
- login
- forgot password
- reset password
- change password

Requirements:
- premium but minimal
- secure and serious
- bilingual
- clear error messaging
- clear success states
- mobile-friendly
- support returning users and administrators

Also define the email-friendly confirmation/success screens.
```

## Prompt 08 - Clinic Selection and Context Switching
```text
Design the clinic-selection flow for users who belong to multiple clinics.

Pages and moments:
- clinic selection page
- no-clinic state
- quick switcher in authenticated shell

Requirements:
- clearly show clinic identity, plan status, and role
- make switching low-friction but safe
- preserve context where possible
- handle empty states and suspended clinics cleanly
```

## Prompt 09 - Tenant Dashboard
```text
Design a modern tenant dashboard for Denta+.

Goals:
- daily clinic command center
- surface appointments, revenue, examinations, recent patients, quick actions, and notifications
- work for doctors, receptionists, and clinic admins

Include:
- KPI layout
- chart strategy
- quick actions
- today's workload
- alerts/reminders
- empty states for new clinics
- bilingual layout
- mobile adaptation
```

## Prompt 10 - Global Search and Quick Actions
```text
Design a cross-app quick search and quick action experience for the tenant plane.

It should help users jump to:
- patients
- appointments
- examinations
- treatments
- prescriptions
- inventory items
- users
- settings

Do not invent backend changes. This is a frontend discoverability layer over existing routes and search endpoints where available.
```

## Prompt 11 - Patient List
```text
Design the patients index page for Denta+.

Requirements:
- fast-scanning list/table
- strong search and filters
- patient status visibility
- WhatsApp/contact visibility
- direct actions for view/edit/new exam/new appointment
- responsive layout
- supports Arabic and English well

Also define empty state, no-results state, and skeleton state.
```

## Prompt 12 - Patient Create
```text
Design the create-patient flow.

Requirements:
- low-friction intake form
- strong inline validation
- date input UX that is unambiguous
- phone input UX with country-aware clarity
- prevent invalid saves
- show clear field-level and form-level feedback
- support Arabic and English names cleanly

Make this feel like a polished medical intake form, not a generic CRUD page.
```

## Prompt 13 - Patient Detail
```text
Design the patient detail page as a clinical profile hub.

Include:
- profile header
- contact details
- medical alerts
- notes
- attachments
- related examinations
- appointments
- treatments
- contracts
- prescriptions
- finance context where useful
- WhatsApp identity block

Design for fast scanning and action-taking by clinic staff.
```

## Prompt 14 - Patient Edit
```text
Design the edit-patient experience.

Requirements:
- preserve patient context while editing
- clear dirty-state handling
- attachment management
- medical alert editing
- safe save pattern
- validation and cancellation UX
```

## Prompt 15 - Treatment Contracts
```text
Design the treatment contract flow:
- create contract
- edit contract
- view contract
- print contract
- sign contract flow

Requirements:
- feel legally serious but readable
- clear patient identity
- clear financial summary
- signature experience
- printable and kiosk-compatible logic
```

## Prompt 16 - Examinations List
```text
Design the examinations index page.

Requirements:
- searchable/filterable
- support status tracking
- surface doctor, patient, date, and follow-up context
- provide quick open/edit/print actions
- support calendar and list mental models
```

## Prompt 17 - Examination Create
```text
Design the create-examination flow.

Requirements:
- integrate patient selection cleanly
- support clinical notes and structured findings
- allow smooth movement into treatment planning and odontogram-related work
- reduce cognitive overload
- make it efficient for repeat clinic usage all day long
```

## Prompt 18 - Examination Detail
```text
Design the examination detail page.

Requirements:
- clear medical summary
- chronology and authorship
- related treatments/contracts/prescriptions
- print/export actions
- strong readability for both short and long notes
```

## Prompt 19 - Examination Edit
```text
Design the examination edit flow.

Requirements:
- fast editing
- safe completion state UX
- strong unsaved-changes handling
- validation
- preserve links to patient and related workflows
```

## Prompt 20 - Examination Calendar
```text
Design the examination calendar view.

Requirements:
- daily/weekly/monthly views
- color system for status/provider/type
- dense but readable event cards
- mobile fallback pattern
- clear drill-down to examination details
```

## Prompt 21 - Appointments List
```text
Design the appointments index page.

Requirements:
- support receptionist workflow
- excellent filtering by doctor, chair, date, status
- clear patient and appointment type visibility
- strong quick actions
- powerful list/calendar toggle if appropriate
```

## Prompt 22 - Appointment Create
```text
Design the create-appointment flow.

Requirements:
- patient search/select
- date/time and chair selection
- duration clarity
- reminder/contact hints
- conflict prevention UX
- inline validation
- smooth save and return behavior
```

## Prompt 23 - Appointment Edit
```text
Design the edit-appointment flow.

Requirements:
- low-friction changes
- rescheduling clarity
- reminder implications
- conflict handling
- timeline/status visibility
```

## Prompt 24 - Appointment Detail and Print
```text
Design the appointment detail page and printable appointment layout.

Requirements:
- appointment summary
- patient quick card
- clinic/provider/chair details
- reminder/contact info
- print-optimized presentation
```

## Prompt 25 - Odontogram List
```text
Design the odontogram landing/list experience.

Requirements:
- help users quickly find a patient and open the odontogram
- show last updated and related examination context
- work well in Arabic and English
```

## Prompt 26 - Odontogram Workspace
```text
Design the odontogram workspace.

Requirements:
- keep the odontogram asset orientation unchanged in all languages
- provide excellent tool selection and state visibility
- make per-tooth actions intuitive
- support keyboard/mouse/touch reasonably
- avoid clutter
- provide patient context and save status
```

## Prompt 27 - Odontogram Export / Print / PDF
```text
Design the odontogram export, print, and PDF-friendly views.

Requirements:
- preserve diagram orientation
- printable clinical clarity
- consistent headers/legend
- bilingual-friendly labels
```

## Prompt 28 - Finance Index
```text
Design the tenant finance home/index page.

Requirements:
- support both income and expense operations
- show high-level financial health
- expose accrual vs cash-aware concepts clearly where relevant
- surface provider payouts, revenue, expenses, outstanding values, and recent activity
- work for clinic operators, not accountants only
```

## Prompt 29 - Finance Create Income
```text
Design the create-income flow.

Requirements:
- clear patient/examination linkage when relevant
- payment method and proof handling
- optional attachments/check scan
- low-friction form flow
- clean validation and success states
```

## Prompt 30 - Finance Create Expense
```text
Design the create-expense flow.

Requirements:
- category clarity
- counterparty capture
- optional attachments
- source linkage where relevant
- professional accounting-style clarity without overwhelming users
```

## Prompt 31 - Finance Detail and Edit
```text
Design the finance detail and edit experiences.

Requirements:
- preserve audit clarity
- show source references
- attachment handling
- safe edit permissions
- obvious distinction between income and expense records
```

## Prompt 32 - Finance Reports
```text
Design the finance reports page.

Requirements:
- support date-range analysis
- summary cards
- charts
- breakdown tables
- print/export affordances
- clarity around cash vs accrual-style values
```

## Prompt 33 - Finance Payouts
```text
Design the provider payouts page and payout management UX.

Requirements:
- clearly distinguish earned share, paid cash, and outstanding payable
- support payout tracking and payment actions
- feel operational and reliable
```

## Prompt 34 - Treatments List
```text
Design the treatments index page.

Requirements:
- status visibility
- provider/patient/date context
- relation to examinations and plans
- quick complete/edit/print actions
```

## Prompt 35 - Treatment Create
```text
Design the create-treatment flow.

Requirements:
- support structured treatment details
- smooth connection to examination and odontogram context
- clear pricing/provider fields
- efficient for daily repeated use
```

## Prompt 36 - Treatment Detail / Edit / Print
```text
Design the treatment detail, edit, and print experiences as one cohesive UX family.

Requirements:
- clear clinical and billing context
- status timeline
- printable treatment summary
- strong readability
```

## Prompt 37 - Prescriptions List
```text
Design the prescriptions index page.

Requirements:
- searchable and printable
- surface patient, medication summary, prescriber, and date
- support repeat clinical workflow
```

## Prompt 38 - Prescription Create / Edit / Detail / Print
```text
Design the full prescriptions module UX:
- create
- edit
- detail
- print

Requirements:
- strong medication readability
- dosage/instruction emphasis
- safe form UX
- print-friendly medical layout
```

## Prompt 39 - Reports Hub
```text
Design the reports module as a reporting hub.

Requirements:
- support multiple report types
- strong range and dimension filters
- chart + detail table pairing
- export-friendly UX
- make heavy data still feel navigable
```

## Prompt 40 - Inventory Overview
```text
Design the inventory index page.

Requirements:
- stock health overview
- low stock visibility
- reorder visibility
- quick actions for receive, adjust, usage, suppliers
- strong filtering/search
```

## Prompt 41 - Inventory Create / Edit / Detail
```text
Design the inventory item lifecycle UX:
- create item
- edit item
- item detail

Requirements:
- supplier relations
- SKU/unit clarity
- reorder metadata
- batch/lot readiness
- professional operational feel
```

## Prompt 42 - Inventory Movements
```text
Design the inventory movements ledger.

Requirements:
- chronological operational ledger
- type badges
- source references
- filters by item/date/type/user
- printable/exportable behavior
```

## Prompt 43 - Inventory Usage
```text
Design the inventory usage workflow and history.

Requirements:
- easy record-usage flow
- support treatment/procedure linkage where relevant
- show downstream finance linkage when COGS auto-post exists
- readable usage history and usage detail/print views
```

## Prompt 44 - Inventory Reorder List
```text
Design the reorder list experience.

Requirements:
- help staff act on low stock
- supplier contact shortcuts
- reorder status workflow
- compact but actionable layout
```

## Prompt 45 - Inventory Suppliers and Templates
```text
Design the suppliers and starter templates/import experiences.

Requirements:
- supplier directory UX
- quick-add supplier flow
- item-to-supplier mapping clarity
- starter template browsing/import UX
```

## Prompt 46 - Users List
```text
Design the tenant users management page.

Requirements:
- searchable users table
- status, role, department visibility
- direct actions for view/edit/password/status
- safe admin workflow
```

## Prompt 47 - User Create / Edit / Detail / Change Password
```text
Design the tenant user management flows:
- create user
- edit user
- view user
- change password

Requirements:
- clear role assignment
- permission-sensitive action patterns
- safe and professional admin experience
```

## Prompt 48 - Roles and Permissions
```text
Design the roles module.

Pages:
- roles list
- create role
- edit role
- role detail / permissions

Requirements:
- make permission complexity understandable
- support large permission matrices
- clear role status and scope
- avoid overwhelming users
```

## Prompt 49 - Notifications Center
```text
Design the tenant notifications center and notification ledger.

Requirements:
- notification inbox UX
- read/unread handling
- filters
- ledger/history for system notifications
- retry affordance where appropriate
- useful but not noisy
```

## Prompt 50 - Activity Log
```text
Design the tenant activity log.

Requirements:
- clear audit readability
- filters by actor, action, date, entity
- export entry points
- no visual clutter
```

## Prompt 51 - Profile
```text
Design the user profile page.

Requirements:
- personal info editing
- password change entry point
- account context
- clean, light administrative UX
```

## Prompt 52 - Tenant Settings
```text
Design the tenant settings experience from scratch.

Sections to accommodate:
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
- modern settings IA, not necessarily matching current implementation
- URL-addressable sections/tabs
- excellent discoverability
- clean save patterns
- no broken section links
- bilingual and RTL-correct
- settings should feel trustworthy and operationally safe
```

## Prompt 53 - Control Plane Entry and Shell
```text
Design the control-plane entry experience and control-plane shell.

Requirements:
- clearly distinct from tenant plane
- feels like platform administration
- strong hierarchy
- efficient navigation
- supports dense data and operational tools
```

## Prompt 54 - Control Plane Dashboard
```text
Design the control-plane dashboard.

Requirements:
- platform-level KPIs
- clinic health visibility
- plan/subscription visibility
- operational alerts
- onboarding/revenue/admin shortcuts
```

## Prompt 55 - Control Plane Clinics List
```text
Design the control-plane clinics index page.

Requirements:
- searchable/filterable tenant list
- plan and status visibility
- billing/subscription context
- quick actions for impersonate, manage subscription, suspend, reactivate, detail
- strong admin-grade scanning
```

## Prompt 56 - Control Plane Clinic Detail
```text
Design the control-plane clinic detail page.

Requirements:
- clinic identity
- subscription state
- operational status
- admin actions
- audit and recent activity
- safe destructive actions
```

## Prompt 57 - Control Plane Plans
```text
Design the control-plane plans module:
- plans list
- create plan
- edit plan

Requirements:
- pricing clarity
- feature entitlement clarity
- lifecycle actions
- high readability for commercial configuration
```

## Prompt 58 - Control Plane Plan Requests
```text
Design the plan requests workflow.

Requirements:
- queue-like operational UX
- request details
- approve/reject/delete actions
- strong status handling
- clear admin accountability
```

## Prompt 59 - Control Plane Invoices
```text
Design the control-plane invoices module:
- invoices list
- create invoice
- invoice detail

Requirements:
- platform billing clarity
- invoice status and actions
- email/send/payment actions
- safe delete behavior for allowed statuses
```

## Prompt 60 - Control Plane Users
```text
Design the control-plane users module:
- users index
- user detail

Requirements:
- show memberships across clinics
- reset password
- toggle status
- manage memberships and roles
- keep it admin-efficient
```

## Prompt 61 - Control Plane Activity Log
```text
Design the control-plane activity log.

Requirements:
- stronger operations/audit emphasis than tenant log
- export flow
- deep filtering
- dense but readable
```

## Prompt 62 - Control Plane Operations Console
```text
Design the control-plane operations console.

Requirements:
- platform tooling and health checks
- runtime status
- maintenance utilities
- plan enforcement visibility
- feel like a serious operations surface, not a settings page
```

## Prompt 63 - Control Plane WhatsApp Command Center
```text
Design the control-plane WhatsApp command center.

Requirements:
- live delivery stats
- journal/ledger
- health state
- opt-out visibility
- emergency pause visibility
- clear distinction between monitoring/operations and configuration
```

## Prompt 64 - Control Plane Settings
```text
Design the control-plane settings experience from scratch.

Sections to support:
- platform profile
- clinic defaults
- governance
- communications
- email templates
- WhatsApp configuration

Requirements:
- must feel as coherent and polished as the tenant settings experience
- every major settings card/section should be directly linkable
- strong page hierarchy
- readable card headers
- professional enterprise SaaS structure
- no broken intra-page links
```

## Prompt 65 - Control Plane Onboarding
```text
Design the create-clinic onboarding flow for control-plane admins.

Requirements:
- structured multi-step or well-sectioned form
- reduces setup mistakes
- clear defaults, plan, admin user, and clinic identity capture
- clean success state
```

## Prompt 66 - Public Kiosk Check-In
```text
Design the public kiosk self-check-in experience.

Requirements:
- extremely simple for patients
- large touch targets
- multilingual
- accessible in a clinic lobby
- support appointment lookup, confirmation, details update, and contract signing
- clear error and timeout handling
```

## Prompt 67 - Public Kiosk Subflows
```text
Design the kiosk subflows in detail:
- lookup
- unlock
- confirm appointment
- update details
- sign treatment contract
- reset session

Requirements:
- touch-first
- low anxiety
- clear privacy cues
- resilient for older patients and busy reception areas
```

## Prompt 68 - Print System
```text
Design a print system for Denta+ across:
- appointments
- examinations
- treatments
- prescriptions
- contracts
- odontogram
- inventory usage
- invoices
- finance reports

Requirements:
- consistent headers and metadata blocks
- clean print typography
- clinic branding support
- bilingual print readiness
```

## Prompt 69 - Notifications and Toast Framework
```text
Design the notification and toast framework for the whole app.

Requirements:
- success/error/warning/info toasts
- non-blocking but visible
- queue/stack behavior
- action toasts where appropriate
- notification bell/dropdown behavior
- avoids alert() patterns completely
```

## Prompt 70 - Empty, Loading, and Error States
```text
Design the full state system for Denta+:
- skeleton loading
- table loading
- form saving
- empty states
- no-results states
- partial failure states
- full-page failure states
- permission denied
- subscription limited
- maintenance state

This must be visually unified across all planes.
```

## Prompt 71 - Mobile and Tablet Strategy
```text
Define the responsive strategy for Denta+ across all modules.

Requirements:
- desktop-first for operational density
- tablet usability for clinic floor usage
- mobile usability for quick actions and essential workflows
- explain which modules become cards, drawers, accordions, tabs, or condensed tables on smaller screens
```

## Prompt 72 - Accessibility Standard
```text
Create the accessibility design standard for Denta+.

Include:
- contrast
- focus states
- keyboard navigation
- screen-reader semantics
- form instructions
- icon labeling
- modal behavior
- table accessibility
- bilingual accessibility considerations
```

## Prompt 73 - Charting and Analytics Style
```text
Define the analytics visualization system for Denta+.

Requirements:
- charts for dashboard, finance, reports, and control-plane metrics
- readable legends
- color-safe data encoding
- Arabic and English number/date handling
- print/export compatibility considerations
```

## Prompt 74 - Destructive and Sensitive Actions
```text
Design the UX system for destructive or high-risk actions in Denta+.

Include:
- delete
- suspend/reactivate
- terminate clinic
- rotate secret
- emergency pause
- reset password
- prune retention data
- restore/backup actions

Requirements:
- friction proportional to risk
- confirmation copy
- audit visibility
- post-action feedback
```

## Prompt 75 - Form System
```text
Design the form system used across Denta+.

Include:
- text inputs
- textarea
- select/searchable select
- date input
- time input
- phone input
- money input
- file upload
- checkbox/radio/toggle
- repeatable rows
- form section headers
- sticky save bars

Requirements:
- works beautifully in English and Arabic
- strong validation
- no confusing persisted error helpers after correction
```

## Prompt 76 - Table System
```text
Design the data table system used across Denta+.

Requirements:
- sorting
- filters
- bulk actions where needed
- row actions
- density options if useful
- responsive degradation
- bilingual compatibility
- strong readability for admin/operator workflows
```

## Prompt 77 - Search and Entity Pickers
```text
Design the entity-search and picker experience for Denta+.

Entities include:
- patients
- users
- examinations
- treatments
- inventory
- medications
- departments
- roles

Requirements:
- reusable searchable picker pattern
- keyboard support
- fast result scanning
- clear secondary metadata
```

## Prompt 78 - Timeline and Audit Components
```text
Design timeline/audit UI patterns for Denta+.

Use cases:
- activity logs
- patient history
- finance audit context
- WhatsApp journal/ledger
- contract events

Requirements:
- readable chronology
- actor/action/entity clarity
- filters and grouping rules
```

## Prompt 79 - Communications UX System
```text
Design the communications UX system that spans tenant and control plane.

Include:
- notification channels
- email settings
- SMS settings
- WhatsApp settings
- alerts
- email templates
- operational WhatsApp command center linkages

Requirements:
- clearly separate configuration from operations
- reduce confusion
- support future growth without IA collapse
```

## Prompt 80 - Final Integration Prompt
```text
Now synthesize everything into a final Denta+ frontend blueprint for Lovable implementation.

Deliver:
1. full site map for tenant plane, control plane, and public flows
2. global design system summary
3. page-by-page implementation order
4. reusable component map
5. responsive strategy summary
6. bilingual/RTL summary
7. frontend architecture recommendation that preserves the existing backend
8. list of high-risk UI flows requiring extra QA

Do not change backend contracts or database design.
Output should be implementation-ready for a serious frontend rebuild.
```

## Optional Use Pattern
You can give Lovable:
- Prompt 01 through Prompt 06 first, to establish the system
- then one prompt per feature/module
- then Prompt 80 as the consolidation pass

## Coverage Summary
This prompt pack covers:
- tenant plane
- control plane
- auth
- kiosk
- settings
- analytics
- forms
- tables
- print flows
- communications
- bilingual UX
- accessibility
- responsive behavior

