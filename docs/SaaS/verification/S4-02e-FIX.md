# Verification: S4-02e-FIX — Patient Picker Hardening

## Objective
Eliminate all cross-clinic patient exposure in the Appointment create/edit picker, including server-rendered HTML.

## 1. Page-Source Proof
### Clinic A
- **Action**: Open `/appointment/create` while logged into Clinic A.
- **Expected**: View source contains `patients: []` in the controller data and only the placeholder `<option value="">Select a patient</option>` in the HTML.
- **Result**: Confirmed. No Clinic B patient identifiers are emitted.

### Clinic B
- **Action**: Open `/appointment/create` while logged into Clinic B.
- **Expected**: Same as above, no Clinic A identifiers.
- **Result**: Confirmed.

## 2. Edit-Form Proof
- **Action**: Edit an appointment in Clinic A associated with "Patient A".
- **Expected**:
  - The `<select>` contains only "Patient A" as an option.
  - Attempting to edit an appointment from Clinic B while in Clinic A context returns `404 Not Found`.
- **Result**:
  - Controller code:
    ```php
    $patient = $this->patientModel->where('clinic_id', $clinicId)->find($appointment['patient_id']);
    // ...
    'patients' => [$patient]
    ```
  - View code:
    ```php
    <?php if (!empty($patients)): ?>
        <?php foreach ($patients as $patient): ?>
            <option value="<?= $patient['id'] ?>" selected>
                <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> (<?= $patient['phone'] ?>)
            </option>
        <?php endforeach; ?>
    <?php endif; ?>
    ```

## 3. Network Proof (API Scoping)
- **Endpoint**: `/api/search/patients?q=Patient`
- **Clinic A Result**: Returns only patients belonging to Clinic A.
- **Clinic B Result**: Returns only patients belonging to Clinic B.
- **Fail-Closed**: Accessing without a clinic session returns `403 Forbidden` JSON.

## 4. Guardrail Compliance
**Command**: `rg -n "table\(" app/Controllers/Appointment.php`
**Result**: (empty) - No raw builders added.
**Status**: PASS
