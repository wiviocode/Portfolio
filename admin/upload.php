<?php
session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$message = '';
$messageType = '';
$uploadType = isset($_GET['type']) ? $_GET['type'] : 'image';

// Set upload directories
$imageUploadDir = '../assets/images/';
$videoUploadDir = '../assets/videos/';
$videoThumbnailDir = '../assets/video-thumbnails/';
$tempDir = '../assets/temp/';

// Create directories if they don't exist
if (!file_exists($imageUploadDir)) {
    mkdir($imageUploadDir, 0755, true);
}
if (!file_exists($videoUploadDir)) {
    mkdir($videoUploadDir, 0755, true);
}
if (!file_exists($videoThumbnailDir)) {
    mkdir($videoThumbnailDir, 0755, true);
}
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

/**
 * Convert image to WebP format and compress it
 * 
 * @param string $sourcePath Original image path
 * @param string $targetPath Target WebP path
 * @param int $quality Compression quality (0-100)
 * @return bool Success or failure
 */
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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if file was uploaded without errors
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file'];
        $fileName = basename($file['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Set allowed file types
        $allowedImageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedVideoExts = ['mp4', 'webm', 'mov', 'ogg'];
        
        // Determine upload directory based on file type
        $targetDir = ($uploadType === 'image') ? $imageUploadDir : $videoUploadDir;
        $allowedExts = ($uploadType === 'image') ? $allowedImageExts : $allowedVideoExts;
        
        // Validate file extension
        if (in_array($fileExt, $allowedExts)) {
            // Sanitize file name
            $newFileName = preg_replace('/[^A-Za-z0-9\-\_\.]/', '', $fileName);
            $newFileName = str_replace(' ', '-', $newFileName);
            
            // For images, save to temp directory first
            $tempFile = '';
            $originalTargetFile = '';
            
            if ($uploadType === 'image') {
                // Create a unique name based on timestamp
                $fileNameParts = pathinfo($newFileName);
                $baseFileName = $fileNameParts['filename'];
                $timeStamp = time();
                
                // Save original to temp dir (needed for conversion)
                $tempFile = $tempDir . $baseFileName . '-' . $timeStamp . '.' . $fileNameParts['extension'];
                
                // Final WebP file name and path
                $newFileName = $baseFileName . '-' . $timeStamp . '.webp';
                $targetFile = $targetDir . $newFileName;
            } else {
                // For videos, handle same as before
                $targetFile = $targetDir . $newFileName;
                
                // Check if file already exists
                if (file_exists($targetFile)) {
                    $fileNameParts = pathinfo($newFileName);
                    $newFileName = $fileNameParts['filename'] . '-' . time() . '.' . $fileNameParts['extension'];
                    $targetFile = $targetDir . $newFileName;
                }
            }
            
            // Try to upload file
            if ($uploadType === 'image') {
                // First upload to temp
                if (move_uploaded_file($file['tmp_name'], $tempFile)) {
                    // Then convert to WebP
                    if (convertToWebP($tempFile, $targetFile)) {
                        $message = "Image uploaded, converted to WebP, and compressed successfully!";
                        $messageType = "success";
                        
                        // Remove the temp file
                        @unlink($tempFile);
                    } else {
                        $message = "Error converting image to WebP. Original format preserved.";
                        $messageType = "error";
                        
                        // If conversion fails, move the original file to the target directory
                        $originalTargetFile = $targetDir . pathinfo($tempFile, PATHINFO_BASENAME);
                        rename($tempFile, $originalTargetFile);
                    }
                } else {
                    $message = "Error uploading file. Please try again.";
                    $messageType = "error";
                }
            } else {
                // For videos, just move to target dir
                if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                    $message = "Video uploaded successfully!";
                    $messageType = "success";
                    
                    // If it's a video, also upload thumbnail if provided
                    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                        $thumbnail = $_FILES['thumbnail'];
                        
                        // Extract the base name of the video file (remove timestamps if present)
                        $videoBaseName = pathinfo($newFileName, PATHINFO_FILENAME);
                        // If filename contains timestamp (e.g., video-name-1234567890)
                        if (preg_match('/(.*)-\d+$/', $videoBaseName, $matches)) {
                            $videoBaseName = $matches[1]; // Use just the base name without timestamp
                        }
                        
                        $thumbnailExt = strtolower(pathinfo($thumbnail['name'], PATHINFO_EXTENSION));
                        
                        if (in_array($thumbnailExt, $allowedImageExts)) {
                            // First upload thumbnail to temp
                            $tempThumbnail = $tempDir . $videoBaseName . '.' . $thumbnailExt;
                            $targetThumbnail = $videoThumbnailDir . $videoBaseName . '.webp';
                            
                            if (move_uploaded_file($thumbnail['tmp_name'], $tempThumbnail)) {
                                // Convert thumbnail to WebP
                                if (convertToWebP($tempThumbnail, $targetThumbnail)) {
                                    $message .= " Thumbnail converted to WebP and uploaded.";
                                    // Remove the temp thumbnail
                                    @unlink($tempThumbnail);
                                } else {
                                    // If conversion fails, move original thumbnail
                                    $originalTargetThumbnail = $videoThumbnailDir . $videoBaseName . '.jpg';
                                    rename($tempThumbnail, $originalTargetThumbnail);
                                    $message .= " Thumbnail uploaded in original format.";
                                }
                            } else {
                                $message .= " But thumbnail upload failed.";
                            }
                        } else {
                            $message .= " But thumbnail has invalid format.";
                        }
                    }
                } else {
                    $message = "Error uploading file. Please try again.";
                    $messageType = "error";
                }
            }
        } else {
            $message = "Invalid file format. Allowed formats: " . implode(', ', $allowedExts);
            $messageType = "error";
        }
    } else {
        $message = "Error: " . $_FILES['file']['error'];
        $messageType = "error";
    }
}

// Get list of allowed extensions for the display of recent uploads
$allowedImageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowedVideoExts = ['mp4', 'webm', 'mov', 'ogg'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Files - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-upload {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .admin-upload h1 {
            margin-top: 0;
            margin-bottom: 30px;
            color: hsl(var(--accent-hsl));
            border-bottom: 2px solid hsla(var(--accent-hsl), 0.2);
            padding-bottom: 15px;
        }
        .upload-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .upload-tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid transparent;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            margin-right: 5px;
        }
        .upload-tab.active {
            background-color: hsla(var(--lightAccent-hsl), 0.3);
            border-color: #ddd;
            font-weight: bold;
        }
        .upload-form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-description {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .btn-upload {
            background-color: hsl(var(--accent-hsl));
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .btn-upload:hover {
            background-color: hsl(var(--darkAccent-hsl));
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
        .thumbnail-preview {
            margin-top: 10px;
            max-width: 200px;
            display: none;
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
        .recent-uploads {
            margin-top: 30px;
        }
        .recent-uploads h2 {
            color: hsl(var(--accent-hsl));
            margin-bottom: 15px;
        }
        .uploads-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        .upload-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        .upload-item img, .upload-item video {
            width: 100%;
            height: 100px;
            object-fit: cover;
        }
        .upload-item-info {
            padding: 8px;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="admin-upload">
        <h1>Upload Files</h1>
        
        <div class="upload-tabs">
            <div class="upload-tab <?php echo $uploadType === 'image' ? 'active' : ''; ?>" onclick="window.location='upload.php?type=image'">Images</div>
            <div class="upload-tab <?php echo $uploadType === 'video' ? 'active' : ''; ?>" onclick="window.location='upload.php?type=video'">Videos</div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form action="upload.php?type=<?php echo $uploadType; ?>" method="post" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label for="file">Select <?php echo ucfirst($uploadType); ?> File:</label>
                <input type="file" name="file" id="file" class="form-control" accept="<?php echo $uploadType === 'image' ? 'image/*' : 'video/*'; ?>" required>
                <?php if ($uploadType === 'image'): ?>
                <div class="form-description">Supported formats: JPG, PNG, GIF. Images will be automatically converted to WebP and compressed.</div>
                <?php endif; ?>
            </div>
            
            <?php if ($uploadType === 'video'): ?>
            <div class="form-group">
                <label for="thumbnail">Video Thumbnail (optional):</label>
                <input type="file" name="thumbnail" id="thumbnail" class="form-control" accept="image/*">
                <div class="form-description">Custom thumbnail for video. Will be converted to WebP automatically.</div>
                <img id="thumbnail-preview" class="thumbnail-preview">
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn-upload">Upload <?php echo ucfirst($uploadType); ?></button>
        </form>
        
        <div class="recent-uploads">
            <h2>Recent Uploads</h2>
            <div class="uploads-list">
                <?php
                $targetDir = ($uploadType === 'image') ? $imageUploadDir : $videoUploadDir;
                $files = array_diff(scandir($targetDir), ['.', '..']);
                $recentFiles = array_slice(array_reverse($files), 0, 8);
                
                foreach ($recentFiles as $file):
                    $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $isImage = in_array($fileExt, $allowedImageExts);
                ?>
                <div class="upload-item">
                    <?php if ($isImage): ?>
                        <img src="<?php echo $targetDir . $file; ?>" alt="<?php echo $file; ?>">
                    <?php else: ?>
                        <video>
                            <source src="<?php echo $targetDir . $file; ?>" type="video/<?php echo $fileExt; ?>">
                        </video>
                    <?php endif; ?>
                    <div class="upload-item-info"><?php echo $file; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="navigation">
            <a href="dashboard.php" class="admin-btn">Back to Dashboard</a>
        </div>
    </div>
    
    <script>
        // Preview thumbnail before upload
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnailInput = document.getElementById('thumbnail');
            const thumbnailPreview = document.getElementById('thumbnail-preview');
            
            if (thumbnailInput && thumbnailPreview) {
                thumbnailInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        var reader = new FileReader();
                        
                        reader.onload = function(e) {
                            thumbnailPreview.src = e.target.result;
                            thumbnailPreview.style.display = 'block';
                        }
                        
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        thumbnailPreview.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html> 