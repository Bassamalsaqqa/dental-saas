#!/bin/bash

# Dental Management System - Deploy Changes Script
# This script helps deploy the modified files to the live server

echo "🚀 Deploying Dental Management System Changes..."
echo "================================================"

# Files that were modified and need to be uploaded
MODIFIED_FILES=(
    "app/Controllers/Odontogram.php"
    "app/Views/odontogram/list.php"
    "app/Config/Routes.php"
)

echo "📁 Modified files to upload:"
for file in "${MODIFIED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file (not found)"
    fi
done

echo ""
echo "📋 Manual Upload Instructions:"
echo "=============================="
echo "1. Access your hosting control panel (cPanel, Plesk, etc.)"
echo "2. Open File Manager"
echo "3. Navigate to: /dev/dental/"
echo "4. Upload these files to their respective directories:"
echo ""

for file in "${MODIFIED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "   📤 Upload: $file"
        echo "      To: /dev/dental/$file"
        echo ""
    fi
done

echo "🔧 Alternative: Use FTP/SFTP"
echo "============================"
echo "If you have FTP access:"
echo "1. Connect to your server via FTP"
echo "2. Navigate to /dev/dental/"
echo "3. Upload the modified files"
echo ""

echo "🧪 Test After Upload"
echo "===================="
echo "After uploading, test at: https://democa.store/dev/dental/odontogram"
echo "You should see a bright red test box with search controls"
echo ""

echo "📞 Need Help?"
echo "============="
echo "If you need help with the upload process:"
echo "1. Check your hosting provider's documentation"
echo "2. Contact your hosting support"
echo "3. Use the File Manager in your control panel"
