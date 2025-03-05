<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Directory containing original images
$sourceDir = "assets/images";
$webpDir = "assets/images/webp";

// Create WebP directory if it doesn't exist
if (!file_exists($webpDir)) {
    mkdir($webpDir, 0755, true);
}

// Get all image files
$files = scandir($sourceDir);
foreach ($files as $file) {
    if (preg_match("/\.(jpg|jpeg|png)$/i", $file)) {
        $sourcePath = $sourceDir . '/' . $file;
        $webpPath = $webpDir . '/' . pathinfo($file, PATHINFO_FILENAME) . '.webp';
        
        // Skip if WebP version already exists and is newer
        if (file_exists($webpPath) && filemtime($webpPath) >= filemtime($sourcePath)) {
            continue;
        }
        
        // Load image based on type
        $imageInfo = getimagesize($sourcePath);
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($sourcePath);
                // Handle transparency for PNGs
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            default:
                continue 2; // Skip unsupported formats
        }
        
        // Convert to WebP with 80% quality (good balance between size and quality)
        imagewebp($image, $webpPath, 80);
        imagedestroy($image);
        
        echo "Converted: $file to WebP\n";
    }
}

echo "Conversion complete!\n"; 