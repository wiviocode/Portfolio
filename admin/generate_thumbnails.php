<?php
session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Set directories
$videoDir = '../assets/videos/';
$thumbnailDir = '../assets/video-thumbnails/';
$tempDir = '../assets/temp/';

// Create directories if they don't exist
if (!file_exists($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
}
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

// Function to convert image to WebP
function convertToWebP($sourcePath, $targetPath, $quality = 80) {
    // Get image type
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }
    
    $mime = $imageInfo['mime'];
    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    
    // Create image resource based on type
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            // Handle transparency for PNG
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            // Already WebP, just copy with compression
            $image = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$image) {
        return false;
    }
    
    // Calculate dimensions for 3:4 aspect ratio (portrait orientation)
    // Where height = width * 4/3
    
    // Determine how to crop based on the source image proportions
    $sourceRatio = $sourceWidth / $sourceHeight;
    $targetRatio = 3/4; // width:height = 3:4
    
    if ($sourceRatio > $targetRatio) {
        // Image is wider than needed - crop width
        $newHeight = $sourceHeight;
        $newWidth = $sourceHeight * $targetRatio;
        $cropX = ($sourceWidth - $newWidth) / 2;
        $cropY = 0;
    } else {
        // Image is taller than needed - crop height
        $newWidth = $sourceWidth;
        $newHeight = $sourceWidth / $targetRatio;
        $cropX = 0;
        $cropY = ($sourceHeight - $newHeight) / 2;
    }
    
    // Create a new image with the target dimensions
    $croppedImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Copy and resize part of the source image
    imagecopy(
        $croppedImage,  // destination
        $image,         // source
        0, 0,           // destination x, y
        $cropX, $cropY, // source x, y
        $newWidth,      // destination width
        $newHeight      // destination height
    );
    
    // Save as WebP with specified quality
    $result = imagewebp($croppedImage, $targetPath, $quality);
    
    // Free memory
    imagedestroy($image);
    imagedestroy($croppedImage);
    
    return $result;
}

// Initialize variables
$message = '';
$messageType = '';
$generatedCount = 0;
$skippedCount = 0;
$errorCount = 0;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    // Get all video files
    $videoFiles = array_diff(scandir($videoDir), ['.', '..']);
    
    foreach ($videoFiles as $videoFile) {
        // Check if it's a video file
        if (preg_match("/\.(mp4|mov|webm|ogg)$/i", $videoFile)) {
            // Get base name without extension
            $baseName = pathinfo($videoFile, PATHINFO_FILENAME);
            
            // Check if thumbnail already exists
            $webpThumbnail = $thumbnailDir . $baseName . '.webp';
            $jpgThumbnail = $thumbnailDir . $baseName . '.jpg';
            
            if (file_exists($webpThumbnail) || file_exists($jpgThumbnail)) {
                $skippedCount++;
                continue; // Skip if thumbnail already exists
            }
            
            // Generate thumbnail using FFmpeg if available
            $videoPath = $videoDir . $videoFile;
            $tempThumbnail = $tempDir . $baseName . '.jpg';
            $targetThumbnail = $thumbnailDir . $baseName . '.webp';
            
            // Try to use FFmpeg to extract a frame at 1 second
            $ffmpegCommand = "ffmpeg -i " . escapeshellarg($videoPath) . " -ss 00:00:01 -vframes 1 " . escapeshellarg($tempThumbnail) . " 2>&1";
            $output = [];
            $returnCode = 0;
            
            exec($ffmpegCommand, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempThumbnail)) {
                // Convert to WebP
                if (convertToWebP($tempThumbnail, $targetThumbnail)) {
                    $generatedCount++;
                    // Remove temp file
                    @unlink($tempThumbnail);
                } else {
                    // If WebP conversion fails, keep the JPG
                    rename($tempThumbnail, $jpgThumbnail);
                    $generatedCount++;
                }
            } else {
                $errorCount++;
            }
        }
    }
    
    if ($generatedCount > 0) {
        $message = "Successfully generated $generatedCount thumbnails. Skipped $skippedCount videos that already had thumbnails.";
        $messageType = "success";
        if ($errorCount > 0) {
            $message .= " Failed to generate $errorCount thumbnails.";
        }
    } elseif ($skippedCount > 0) {
        $message = "No new thumbnails generated. All $skippedCount videos already have thumbnails.";
        $messageType = "info";
    } else {
        $message = "No videos found or all thumbnail generation failed.";
        $messageType = "error";
    }
}

// Get current thumbnail status
$videoFiles = array_diff(scandir($videoDir), ['.', '..']);
$totalVideos = 0;
$videosWithThumbnails = 0;

foreach ($videoFiles as $videoFile) {
    if (preg_match("/\.(mp4|mov|webm|ogg)$/i", $videoFile)) {
        $totalVideos++;
        $baseName = pathinfo($videoFile, PATHINFO_FILENAME);
        
        if (file_exists($thumbnailDir . $baseName . '.webp') || file_exists($thumbnailDir . $baseName . '.jpg')) {
            $videosWithThumbnails++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Video Thumbnails - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-thumbnails {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .admin-thumbnails h1 {
            margin-top: 0;
            margin-bottom: 30px;
            color: hsl(var(--accent-hsl));
            border-bottom: 2px solid hsla(var(--accent-hsl), 0.2);
            padding-bottom: 15px;
        }
        .status-card {
            background-color: hsla(var(--lightAccent-hsl), 0.3);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .status-card h2 {
            margin-top: 0;
            color: hsl(var(--accent-hsl));
            font-size: 1.4rem;
        }
        .status-card p {
            margin-bottom: 0;
        }
        .progress-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin: 15px 0;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: hsl(var(--accent-hsl));
            border-radius: 4px;
            transition: width 0.3s;
        }
        .message {
            padding: 10px 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .btn-generate {
            background-color: hsl(var(--accent-hsl));
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .btn-generate:hover {
            background-color: hsl(var(--darkAccent-hsl));
        }
        .note {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff3cd;
            color: #856404;
            border-radius: 4px;
            border: 1px solid #ffeeba;
        }
        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .admin-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: hsl(var(--accent-hsl));
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .admin-btn:hover {
            background-color: hsl(var(--darkAccent-hsl));
        }
    </style>
</head>
<body>
    <div class="admin-thumbnails">
        <h1>Generate Video Thumbnails</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="status-card">
            <h2>Thumbnail Status</h2>
            <p>
                <?php echo $videosWithThumbnails; ?> of <?php echo $totalVideos; ?> videos have thumbnails
                (<?php echo $totalVideos > 0 ? round(($videosWithThumbnails / $totalVideos) * 100) : 0; ?>%)
            </p>
            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: <?php echo $totalVideos > 0 ? ($videosWithThumbnails / $totalVideos) * 100 : 0; ?>%;"></div>
            </div>
        </div>
        
        <form method="post" action="">
            <button type="submit" name="generate" class="btn-generate">Generate Missing Thumbnails</button>
        </form>
        
        <div class="note">
            <strong>Note:</strong> This tool requires FFmpeg to be installed on the server. It will extract a frame from each video at the 1-second mark and convert it to WebP format.
        </div>
        
        <div class="navigation">
            <a href="dashboard.php" class="admin-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 