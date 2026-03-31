# Patient Profile Page - Feature Requirements Brief
Date: 2026-03-30
Product: Dental SaaS
Scope: Designer-facing requirements for a patient profile page redesign
Status: Neutral functional brief only. No current UI structure should be inferred from this document.

## 1. Purpose
The patient profile page is the operational home for everything related to a single patient.
It must let clinic staff quickly understand the patient's identity, clinical state, financial state, history, documents, and next actions.

This document intentionally avoids prescribing tabs, cards, columns, rails, accordions, or any specific visual layout.
It describes only what the page needs to support.

## 2. Primary Users
- Reception / front desk
- Dentist / provider
- Assistant / nurse
- Clinic manager / finance-capable staff

## 3. Page Goals
The page should help a user answer these questions quickly:
- Who is this patient?
- How do I contact them?
- Are there important medical alerts?
- When did they last visit?
- What is their next appointment?
- What examinations, treatments, prescriptions, and invoices exist?
- How much does the patient owe the clinic?
- What files, scans, contracts, or records exist?
- What should I do next for this patient?

## 4. Core Information Domains
### 4.1 Patient Identity
- Patient internal ID / chart number
- Full name
- Status
  - active
  - inactive
  - pending or equivalent lifecycle state if used
- Gender
- Date of birth
- Derived age
- Created date / member since
- Created by / responsible staff if relevant

### 4.2 Contact Information
- Phone
- Email
- City / location
- WhatsApp identity information when available
- WhatsApp opt-out state when applicable

### 4.3 Medical Risk / Alert Information
Structured medical alert flags with optional details.
Current known alert categories:
- allergies
- currently taking medications
- blood thinners
- diabetes
- high blood pressure
- heart condition / heart surgery history
- pregnant / possibly pregnant
- prior anesthesia or sedation problem

Also support legacy or free-text fields when present:
- medical history
- allergies free text
- notes

The page must make urgent or high-risk alerts visually discoverable.

### 4.4 Clinical Activity
Related patient records include:
- appointments
- examinations
- treatments
- prescriptions
- odontogram / tooth chart data

For each related domain, the page should support both:
- summary understanding
- drill-down into the specific record

### 4.5 Financial Information
Patient-linked finance records exist.
The page should support at least:
- total billed to the patient
- total paid by the patient
- outstanding balance / patient owes clinic
- invoice/payment status visibility per finance record
- access to create/view/edit patient-linked finance records where permitted

### 4.6 Documents / Files
File attachments may exist for a patient.
The page should support:
- file upload entry point
- download/view access
- delete where permitted
- category
- notes
- upload history
- storage-awareness messaging if quota/limits apply

### 4.7 Contracts
Treatment contracts may exist when the feature is enabled.
The page should support:
- view contract
- edit draft contract
- print contract
- delete contract where permitted
- contract status
- contract history list

## 5. Activity / History Requirements
The page needs a unified patient activity history capability.
That history should be able to incorporate records such as:
- appointments
- examinations
- treatments
- prescriptions
- finance events
- optionally files/contracts if useful

The page should support:
- chronological viewing
- searching across history
- filtering by date range
- filtering by record type if the designer chooses to expose that
- opening the underlying source record from the history item

## 6. Operational Snapshot Requirements
The page should make key patient state immediately visible without forcing the user to inspect multiple records.
Useful snapshot metrics include:
- last visit date
- next appointment date
- number of active/open treatments
- number of medical alerts
- number of appointments
- number of examinations
- number of prescriptions
- number of files
- number of contracts
- outstanding balance
- total billed
- total paid

The designer should decide which of these deserve primary emphasis vs secondary emphasis.

## 7. Actions the Page Must Support
Depending on permission and feature availability, the page may need entry points for:
- go back to patient list
- edit patient
- delete patient if allowed
- create appointment for this patient
- create examination for this patient
- create treatment for this patient
- create prescription for this patient
- create finance entry / invoice for this patient
- open odontogram for this patient
- upload attachment
- create contract from exam context where applicable
- update WhatsApp identity

Actions must be permission-aware.
Actions should be available without the user having to hunt for them.

## 8. Related Record Interaction Requirements
For lists/tables of related records, the page should support:
- open the record
- edit the record when permitted
- clear status visibility
- relevant metadata visibility
- efficient scanning when there are many records

Examples of related record metadata:
### Appointments
- date
- time
- type
- duration
- status

### Examinations
- date
- type
- chief complaint
- status

### Treatments
- treatment name
- type
- tooth number / location if relevant
- cost
- status

### Prescriptions
- medicine(s)
- dosage
- frequency
- prescribed date
- status

### Finance records
- transaction date
- type
- payment method
- total
- status
- remaining due if relevant

### Attachments
- upload date
- category
- notes
- file name
- file actions

### Contracts
- contract date
- linked exam if any
- total
- status
- contract actions

## 9. Odontogram Context
There is an existing odontogram feature in the product.
The patient profile page should not attempt to replace its domain logic.
It should provide:
- clear access to the odontogram
- enough context to indicate that tooth-chart data exists
- optional summary if helpful, but the odontogram remains a dedicated clinical tool

## 10. Feature Flags / Conditional Areas
Some areas may not be available for every clinic/plan.
The design should support conditional appearance of sections such as:
- attachments
- contracts
- WhatsApp identity features
- possibly other subscription-gated features

The page must still feel complete when some optional capabilities are absent.

## 11. Permissions / Role Sensitivity
Different users may have different permissions.
The page should support permission-aware visibility for:
- viewing records
- editing records
- deleting records
- creating related records
- finance actions
- contract actions
- attachment actions

The design must not depend on all actions always being present.

## 12. Internationalization / Localization Requirements
The page must work in:
- English (LTR)
- Arabic (RTL)

Design implications:
- all labels/messages must be translatable
- layout must survive RTL mirroring cleanly
- icons and directional affordances must respect RTL where relevant
- mixed-direction content such as phone numbers, IDs, currency, or dates should remain readable

The designer should not hard-code language-specific spacing assumptions.

## 13. Responsiveness Requirements
The page must work on:
- desktop
- tablet
- mobile

Functional expectations on smaller screens:
- primary patient identity remains visible quickly
- critical actions remain accessible
- history/related records remain usable
- dense financial and clinical information should not become unreadable
- the page should not require impossible horizontal scrolling for core use

## 14. Accessibility Requirements
The page should follow practical accessibility standards:
- strong text/background contrast
- meaningful hierarchy
- keyboard-navigable actions
- visible focus states
- non-color-only status communication
- accessible labels for icons and action controls
- avoid hiding essential meaning in hover-only interactions

## 15. Performance / Density Requirements
Real clinics may have many related records.
The page design should anticipate:
- long patient history
- many appointments
- many files
- many prescriptions
- many finance records

The design should support efficient scanning and not assume a tiny dataset.

## 16. Content Prioritization Guidance
The page should balance four types of information:
- identity and risk
- next operational action
- clinical history
- financial state

A good design should make it obvious:
- what is urgent
- what is current
- what is historical
- what requires action now

## 17. Financial Context the Designer Should Know
The financial area is not just a list of invoices.
Users care about:
- what the patient still owes the clinic
- whether payments are complete, partial, overdue, or pending
- how much has been billed overall
- how much has been collected overall
- opening the exact financial record for details

The designer should treat finance as an important patient dimension, not a minor afterthought.

## 18. Edge Cases to Support
The design should remain coherent for patients who have:
- no appointments yet
- no examinations yet
- no files yet
- no contracts yet
- no finance records yet
- many medical alerts
- no medical alerts
- missing optional contact info
- high outstanding balances
- lots of historical records

## 19. Designer Freedom
The designer is free to decide:
- overall information architecture
- whether to use tabs, sections, sidebar, stacked blocks, sticky tools, split panes, etc.
- what deserves primary vs secondary emphasis
- how to visually express urgency, finance, chronology, and actions

The only requirement is that the resulting design supports the full feature set and operational needs above.

## 20. Implementation Constraints for Later Handoff
For later engineering implementation, the design should ideally be compatible with these realities:
- route structure and permissions already exist
- data is available across these domains:
  - patient
  - appointments
  - examinations
  - treatments
  - prescriptions
  - finance
  - attachments
  - contracts
  - odontogram
  - WhatsApp identity
- some sections are feature-flagged or plan-gated
- English and Arabic are both required
- desktop must remain strong while mobile/tablet must also work

## 21. Deliverable Expected From Designer
The designer should provide:
- a full patient profile page concept
- information hierarchy rationale
- handling for empty states
- handling for high-volume record states
- desktop and mobile/tablet behavior
- RTL/LTR considerations
- action model and prioritization
- treatment of finance, history, alerts, files, and odontogram access

## 22. Summary
This page is not just a contact card.
It is the clinic's patient command center.
A successful redesign should help staff understand the patient quickly, assess risks, access records, take the next action, and inspect financial and clinical context without confusion.
