# Clinic Branding Guide

## Customizing Your Clinic Identity

To customize your clinic's appearance in the system, navigate to **Settings > Clinic Information**.

### Clinic Tagline
- **Field:** `Clinic Tagline`
- **Description:** This text appears in the sidebar below the clinic name.
- **Default:** `Professional Suite`
- **Max Length:** 100 characters.

### Clinic Logo
- **Field:** `Clinic Logo Path`
- **Description:** Provide a URL or a relative path to your clinic logo.
- **Path Handling:**
    - **Relative Path:** If you provide a path like `assets/images/my-logo.png`, the system will use your base URL.
    - **URL:** If you provide a full URL like `https://example.com/logo.png`, the system will use it directly.
- **Recommended Dimensions:** 
    - Use a square or near-square source image (e.g., 256x256 pixels).
    - The logo is rendered inside a 40x40 pixel container using `object-contain` to preserve aspect ratio.
- **Fallback:** If no logo path is provided, the system displays a default tooth icon.

*Note: Direct file upload is not supported in this version. Please ensure the logo file is available at the provided path or URL.*

### Logo Upload
- **Supported Formats:** PNG, JPG/JPEG, WEBP (No SVG).
- **Size Limit:** 512 KB.
- **Recommended Dimensions:** 40x40 pixels (square).
- **Storage:** Successfully uploaded logos are stored in \public/uploads/clinic/\ and will overwrite previous uploads.
