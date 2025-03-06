#!/bin/bash

# Color setup
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}====== WEBSITE DEPLOYMENT SCRIPT ======${NC}"
echo -e "Starting deployment at: $(date)"
echo ""

# Function to check if a command succeeded
check_status() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ $1${NC}"
    else
        echo -e "${RED}✗ $1${NC}"
        exit 1
    fi
}

# Step 1: Create Open Graph image if it doesn't exist
echo -e "${YELLOW}=== STEP 1: CREATING OPEN GRAPH IMAGE ===${NC}"
if [ ! -f "assets/images/og-image.jpg" ]; then
    php create_og_image.php
    check_status "Open Graph image created"
else
    echo -e "${GREEN}✓ Open Graph image already exists${NC}"
fi
echo ""

# Step 2: Convert images to WebP format
echo -e "${YELLOW}=== STEP 2: CONVERTING IMAGES TO WEBP ===${NC}"
php convert_images.php
check_status "WebP image conversion"
echo ""

# Step 3: Run pre-launch checks
echo -e "${YELLOW}=== STEP 3: RUNNING PRE-LAUNCH CHECKS ===${NC}"
php pre_launch_check.php
check_status "Pre-launch checks"
echo ""

# Step 4: Check for console.log statements
echo -e "${YELLOW}=== STEP 4: CHECKING FOR CONSOLE.LOG STATEMENTS ===${NC}"
if grep -r "console.log" --include="*.js" ./js/; then
    echo -e "${RED}✗ Found console.log statements. Please remove them before deploying.${NC}"
    echo -e "You can continue if these are intentional."
    read -p "Continue deployment? (y/n) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    echo -e "${GREEN}✓ No console.log statements found${NC}"
fi
echo ""

# Step 5: Compress JavaScript files
echo -e "${YELLOW}=== STEP 5: COMPRESSING JAVASCRIPT FILES ===${NC}"
if command -v uglifyjs &> /dev/null; then
    for file in js/*.js; do
        if [[ $file != *".min.js" ]]; then
            uglifyjs "$file" -o "${file%.js}.min.js" -c -m
            check_status "Compressed $file to ${file%.js}.min.js"
        fi
    done
else
    echo -e "${YELLOW}⚠ UglifyJS not found. Skipping JavaScript compression.${NC}"
    echo -e "Install with: npm install -g uglify-js"
fi
echo ""

# Step 6: Clean up development files
echo -e "${YELLOW}=== STEP 6: CLEANING UP DEVELOPMENT FILES ===${NC}"
dev_files=(
    "create_og_image.php"
    "pre_launch_check.php"
    "pre_launch_check_*.log"
    "deploy.sh"
    "*~"
    "*.bak"
    ".DS_Store"
)

for pattern in "${dev_files[@]}"; do
    find . -name "$pattern" -not -path "*/\.*" -type f -exec echo "Would remove: {}" \;
done

read -p "Do you want to remove these files? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    for pattern in "${dev_files[@]}"; do
        find . -name "$pattern" -not -path "*/\.*" -type f -delete
    done
    echo -e "${GREEN}✓ Development files cleaned up${NC}"
else
    echo -e "${YELLOW}⚠ Skipping cleanup${NC}"
fi
echo ""

# Step 7: Final confirmation
echo -e "${YELLOW}=== DEPLOYMENT COMPLETE ===${NC}"
echo -e "Completed at: $(date)"
echo -e "${GREEN}Your website is ready to be published!${NC}"
echo ""
echo -e "Next steps:"
echo -e "1. Upload all files to your web server"
echo -e "2. Test the website on the live server"
echo -e "3. Submit the sitemap to search engines (Google Search Console, Bing Webmaster Tools)"
echo "" 