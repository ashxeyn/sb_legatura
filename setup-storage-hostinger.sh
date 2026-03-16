#!/bin/bash

# Hostinger Storage Setup Script
# This script sets up storage permissions and symbolic links for file access

echo "========================================="
echo "Hostinger Storage Setup Script"
echo "========================================="
echo ""

# Get the current directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo "Current directory: $SCRIPT_DIR"
echo ""

# Step 1: Create storage directories if they don't exist
echo "Step 1: Creating storage directories..."
mkdir -p storage/app/public/messages
mkdir -p storage/app/public/profiles
mkdir -p storage/app/public/projects
mkdir -p storage/app/public/progress_uploads
mkdir -p storage/app/public/payments
mkdir -p storage/app/public/valid_ids
mkdir -p storage/app/public/police_clearance
mkdir -p storage/app/public/DTI_SEC
mkdir -p storage/app/public/contractor_documents
mkdir -p storage/app/public/doc_cache
echo "✓ Storage directories created"
echo ""

# Step 2: Set permissions on storage directories
echo "Step 2: Setting permissions on storage directories..."
chmod -R 755 storage
chmod -R 755 storage/app/public
chmod -R 755 bootstrap/cache
echo "✓ Permissions set to 755"
echo ""

# Step 3: Remove old symbolic link if it exists
echo "Step 3: Checking for existing symbolic link..."
if [ -L "public/storage" ]; then
    echo "Removing old symbolic link..."
    rm public/storage
    echo "✓ Old symbolic link removed"
elif [ -d "public/storage" ]; then
    echo "Warning: public/storage exists as a directory, not a symbolic link"
    echo "Please manually remove it or rename it before running this script"
    exit 1
fi
echo ""

# Step 4: Create symbolic link
echo "Step 4: Creating symbolic link..."
ln -s ../storage/app/public public/storage
if [ -L "public/storage" ]; then
    echo "✓ Symbolic link created successfully"
else
    echo "✗ Failed to create symbolic link"
    echo "Trying alternative method using Laravel artisan..."
    php artisan storage:link
fi
echo ""

# Step 5: Verify symbolic link
echo "Step 5: Verifying symbolic link..."
if [ -L "public/storage" ]; then
    TARGET=$(readlink public/storage)
    echo "✓ Symbolic link verified"
    echo "  Link: public/storage -> $TARGET"
else
    echo "✗ Symbolic link verification failed"
    echo "Please create it manually or contact Hostinger support"
fi
echo ""

# Step 6: Set ownership (optional, uncomment if needed)
# echo "Step 6: Setting ownership..."
# Replace 'username' with your actual Hostinger username
# chown -R username:username storage
# chown -R username:username public/storage
# echo "✓ Ownership set"
# echo ""

# Step 7: Test file access
echo "Step 7: Testing file access..."
TEST_FILE="storage/app/public/test-access.txt"
echo "Test file for storage access" > "$TEST_FILE"
if [ -f "$TEST_FILE" ]; then
    echo "✓ Test file created successfully"
    if [ -f "public/storage/test-access.txt" ]; then
        echo "✓ Test file accessible via symbolic link"
        rm "$TEST_FILE"
        echo "✓ Test file cleaned up"
    else
        echo "✗ Test file NOT accessible via symbolic link"
        echo "Please check symbolic link configuration"
    fi
else
    echo "✗ Failed to create test file"
    echo "Please check storage directory permissions"
fi
echo ""

echo "========================================="
echo "Setup Complete!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Upload this script to your Hostinger root directory"
echo "2. Run: chmod +x setup-storage-hostinger.sh"
echo "3. Run: ./setup-storage-hostinger.sh"
echo "4. Test by uploading an image in messages"
echo ""
echo "If issues persist:"
echo "- Check .htaccess files are uploaded correctly"
echo "- Verify APP_URL in .env matches your domain"
echo "- Contact Hostinger support for symbolic link issues"
echo ""
