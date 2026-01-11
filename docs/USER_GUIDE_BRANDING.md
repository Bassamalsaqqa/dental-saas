# Clinic Branding Guide

## Customizing Your Clinic Identity

To customize your clinic's appearance in the system, navigate to **Settings > Clinic Information**.

### Clinic Tagline
- **Field:** `Clinic Tagline`
- **Description:** This text appears in the sidebar below the clinic name.
- **Default:** `Professional Suite`
- **Max Length:** 100 characters.

### Clinic Logo
- **Field:** `Clinic Logo Upload`
- **Description:** Upload your clinic's logo to display on the dashboard and printouts.
- **Upload Support:**
    - **Formats:** PNG, JPG, WEBP.
    - **Max Size:** 512 KB.
    - **Recommended:** 40x40 pixels (square) for best dashboard fit. Printouts scale gracefully.
- **Removing Logo:** Check "Remove logo (use default)" and save to revert to the default system icon.
- **Rendering:**
    - Dashboard: Displayed in the sidebar header.
    - Printouts: Displayed at the top-center of invoices, prescriptions, and reports.
- **Fallback:** If no logo is set, the system displays a default tooth icon.

### Technical Details
- **Storage:** Uploaded logos are stored securely in `public/uploads/clinic/`.
- **Naming:** Files are renamed to `clinic-logo.[ext]` to ensure consistent loading.
- **Path Handling:** The system automatically handles local paths vs URLs if manually configured in the database, but the UI enforces file upload for security.