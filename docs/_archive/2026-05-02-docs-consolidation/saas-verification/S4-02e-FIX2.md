# Verification: S4-02e-FIX2 — AJAX-backed Patient Picker

## Objective
Restore a usable patient picker while keeping it clinic-scoped by converting it into an AJAX-backed Select2 control.

## 1. Network Proof (AJAX Loading)
- **Endpoint**: `/api/search/patients?q=Patient`
- **Action**: Open `/appointment/create` and type "Patient" in the picker.
- **Verification**: Browser DevTools (Network tab) shows XHR requests to the above endpoint.
- **Success**: Status `200 OK` with JSON results.

## 2. JSON Proof (Clinic Isolation)
- **Clinic A Request**: `GET /api/search/patients?q=Patient` (with Clinic A session)
- **Expected Results**: Only Clinic A patient IDs/names.
- **Clinic B Request**: `GET /api/search/patients?q=Patient` (with Clinic B session)
- **Expected Results**: Only Clinic B patient IDs/names.
- **Result**: JSON response `results` array is strictly filtered by the active clinic ID.

## 3. UI Proof
- **Action**: Switch between Clinic A and Clinic B and check the appointment form patient dropdown.
- **Clinic A UI**: Lists "Patient A", "Patient A2", etc.
- **Clinic B UI**: Lists "Patient B", "Patient B2", etc.
- **Result**: No cross-clinic leakage in the UI results.

## 4. Page Source Proof
- **Action**: Inspect HTML of `/appointment/create`.
- **Expected**:
  ```html
  <select name="patient_id" ...>
      <option value="">Select a patient</option>
  </select>
  ```
- **Result**: No preloaded `<option>` tags for other patients exist in the server-rendered HTML.

## 5. Edit Form Proof
- **Action**: Edit an appointment for "Patient A" in Clinic A.
- **Expected**:
  ```html
  <select name="patient_id" ...>
      <option value="">Select a patient</option>
      <option value="1" selected>Patient A (+123456789)</option>
  </select>
  ```
- **Result**: Confirmed. Only the currently selected patient is emitted as an option.

## 6. Guardrail Compliance
- **Command**: `bash scripts/ci/saas_guardrails.sh`
- **Result**: DOM=0, Group=0, Raw=32 (No increase in raw matches).
- **Status**: PASS
