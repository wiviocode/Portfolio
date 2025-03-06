<?php
// Script to create an Open Graph image by resizing an existing image
ini_set('memory_limit', '512M');

// Source image - using a nice, high-quality image from the portfolio
$sourceImage = 'assets/images/MBB vs Ohio State - EL 55.png';
$destinationImage = 'assets/images/og-image.jpg';

// Check if source exists
if (!file_exists($sourceImage)) {
    die("Source image not found: $sourceImage");
}

// Get image info
$imageInfo = getimagesize($sourceImage);
if (!$imageInfo) {
    die("Could not get image information");
}

// Load the image based on type
$image = imagecreatefrompng($sourceImage);
if (!$image) {
    die("Failed to create image from PNG");
}

// Create a new canvas for the OG image (1200x630 is recommended for Facebook)
$ogImage = imagecreatetruecolor(1200, 630);

// Enable alpha blending
imagealphablending($ogImage, true);

// Calculate dimensions for resizing to cover
$sourceWidth = $imageInfo[0];
$sourceHeight = $imageInfo[1];

// Calculate resize ratio to cover the 1200x630 area
$widthRatio = 1200 / $sourceWidth;
$heightRatio = 630 / $sourceHeight;
$ratio = max($widthRatio, $heightRatio);

// Calculate new dimensions
$newWidth = $sourceWidth * $ratio;
$newHeight = $sourceHeight * $ratio;

// Calculate cropping position (center crop)
$srcX = 0;
$srcY = ($sourceHeight - ($sourceHeight * 0.7)) / 2; // Focus higher up in the image

// Resize and crop the image
imagecopyresampled(
    $ogImage,           // Destination image
    $image,             // Source image
    0,                  // Destination X
    0,                  // Destination Y
    $srcX,              // Source X
    $srcY,              // Source Y
    1200,               // Destination width
    630,                // Destination height
    $sourceWidth,       // Source width
    $sourceHeight * 0.7 // Source height (cropped to focus higher)
);

// Create semi-transparent overlay at the bottom for text
$overlayHeight = 80;
$overlay = imagecreatetruecolor(1200, $overlayHeight);
$black = imagecolorallocate($overlay, 0, 0, 0);
imagefill($overlay, 0, 0, $black);
imagecopymerge($ogImage, $overlay, 0, 630 - $overlayHeight, 0, 0, 1200, $overlayHeight, 70);
imagedestroy($overlay);

// Add text overlay with the website name using built-in font
$textColor = imagecolorallocate($ogImage, 255, 255, 255);
$fontSize = 5; // Largest built-in font
$text = "Eli Larson Photography & Videography";
$textWidth = imagefontwidth($fontSize) * strlen($text);
$textX = (1200 - $textWidth) / 2; // Center text horizontally
$textY = 630 - $overlayHeight/2 - imagefontheight($fontSize)/2;
imagestring($ogImage, $fontSize, $textX, $textY, $text, $textColor);

// Save as JPEG with 90% quality
imagejpeg($ogImage, $destinationImage, 90);

// Clean up
imagedestroy($image);
imagedestroy($ogImage);

echo "Open Graph image created successfully: $destinationImage";
?> 