<?php
// Set memory limit to 512MB
ini_set('memory_limit', '512M');

// Set error reporting based on environment
// On production, hide errors
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1' || $_SERVER['SERVER_ADDR'] === '::1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Directory containing original images
$sourceDir = "assets/images";
$webpDir = "assets/images/webp";

// Statistics
$totalFiles = 0;
$convertedFiles = 0;
$skippedFiles = 0;
$errorFiles = 0;

// Create WebP directory if it doesn't exist
if (!file_exists($webpDir)) {
    mkdir($webpDir, 0755, true);
    echo "Created WebP directory: $webpDir\n";
}

// Get all image files
$files = scandir($sourceDir);
foreach ($files as $file) {
    try {
        if (preg_match("/\.(jpg|jpeg|png)$/i", $file)) {
            $totalFiles++;
            $sourcePath = $sourceDir . '/' . $file;
            $webpPath = $webpDir . '/' . pathinfo($file, PATHINFO_FILENAME) . '.webp';
            
            // Skip if WebP version already exists and is newer
            if (file_exists($webpPath) && filemtime($webpPath) >= filemtime($sourcePath)) {
                $skippedFiles++;
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
                    $errorFiles++;
                    continue 2; // Skip unsupported formats
            }
            
            // Convert to WebP with 80% quality (good balance between size and quality)
            imagewebp($image, $webpPath, 80);
            imagedestroy($image);
            
            $convertedFiles++;
            echo "Converted: $file to WebP\n";
        }
    } catch (Exception $e) {
        $errorFiles++;
        echo "Error processing $file: " . $e->getMessage() . "\n";
        continue;
    }
}

echo "\nConversion complete!\n"; 
echo "Total files: $totalFiles\n";
echo "Converted: $convertedFiles\n";
echo "Skipped (already converted): $skippedFiles\n";
echo "Errors: $errorFiles\n";
?> 