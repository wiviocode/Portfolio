<?php
// Include authentication first to start session before any output
include_once 'includes/auth.php';
// Then include header which outputs HTML
include_once '../includes/header.php';

// File paths
$titlesDataFile = "../data/video_titles.json";
$videosDir = "../assets/videos";

// Message handling
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $titles = $_POST['titles'] ?? [];
    
    // Get current data to preserve metadata
    $currentData = [];
    if (file_exists($titlesDataFile)) {
        $jsonContent = file_get_contents($titlesDataFile);
        $currentData = json_decode($jsonContent, true) ?: [];
    }
    
    // Preserve metadata entries (keys starting with _)
    $finalData = [];
    foreach ($currentData as $key => $value) {
        if (substr($key, 0, 1) === '_') {
            $finalData[$key] = $value;
        }
    }
    
    // Add the titles
    foreach ($titles as $filename => $title) {
        if (trim($title) !== '') {
            $finalData[$filename] = $title;
        }
    }
    
    // Save back to file
    if (file_put_contents($titlesDataFile, json_encode($finalData, JSON_PRETTY_PRINT))) {
        $message = 'Video titles have been saved successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error saving titles. Check file permissions.';
        $messageType = 'error';
    }
}

// Load custom order data if available
$orderDataFile = "../data/video_order.json";
$orderData = [];
if (file_exists($orderDataFile)) {
    $jsonData = file_get_contents($orderDataFile);
    $orderData = json_decode($jsonData, true) ?: [];
}

// Load current titles
$titles = [];
if (file_exists($titlesDataFile)) {
    $jsonContent = file_get_contents($titlesDataFile);
    $data = json_decode($jsonContent, true) ?: [];
    
    // Filter out metadata keys
    foreach ($data as $key => $value) {
        if (substr($key, 0, 1) !== '_') {
            $titles[$key] = $value;
        }
    }
}

// Get video files and sort them same as videography page
$videoFiles = [];
if (is_dir($videosDir)) {
    $files = scandir($videosDir);
    foreach ($files as $file) {
        if(preg_match("/\.(mp4|mov|webm|ogg)$/i", $file)) {
            // Get order (default to 999 if not set)
            $order = isset($orderData[$file]) ? $orderData[$file] : 999;
            
            // Get filename without extension for thumbnail matching
            $fileNameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
            
            // Check for WebP thumbnail first, then fallback to jpg
            $thumbnailPath = "";
            if (file_exists("../assets/video-thumbnails/{$fileNameWithoutExt}.webp")) {
                $thumbnailPath = "../assets/video-thumbnails/{$fileNameWithoutExt}.webp";
            } elseif (file_exists("../assets/video-thumbnails/{$fileNameWithoutExt}.jpg")) {
                $thumbnailPath = "../assets/video-thumbnails/{$fileNameWithoutExt}.jpg";
            }
            
            // Auto-generated title (for display when custom title is absent)
            $autoTitle = ucwords(str_replace(['_', '-'], ' ', $fileNameWithoutExt));
            
            // Create video object with metadata
            $videoObj = [
                'file' => $file,
                'auto_title' => $autoTitle,
                'order' => $order,
                'thumbnail' => $thumbnailPath
            ];
            $videoFiles[] = $videoObj;
        }
    }
    
    // Sort videos by custom order - identical to videography page
    usort($videoFiles, function($a, $b) {
        // First by order
        if ($a['order'] !== $b['order']) {
            return $a['order'] - $b['order'];
        }
        // Then by name as fallback
        return strcmp($a['file'], $b['file']);
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Video Titles - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .admin-header h1 {
            margin: 0;
        }
        
        .message {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-bar {
            padding: 10px;
            margin: 10px 0 20px;
            border-radius: 4px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .video-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background-color: #fff;
        }
        
        .video-preview {
            width: 100%;
            height: 150px;
            object-fit: cover;
            margin-bottom: 10px;
            border-radius: 4px;
            background-color: #f0f0f0;
        }
        
        .video-filename {
            font-size: 14px;
            margin-bottom: 5px;
            font-family: monospace;
            word-break: break-all;
        }
        
        .video-order {
            font-size: 12px;
            margin-bottom: 10px;
            color: #666;
        }
        
        .has-custom-title {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            color: white;
            background-color: #2ecc71;
            display: inline-block;
            margin-left: 5px;
        }
        
        .title-field {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        
        .title-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .submit-area {
            margin-top: 20px;
            text-align: center;
        }
        
        .btn {
            padding: 10px 20px;
            background-color: hsl(var(--accent-hsl));
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background-color: hsl(var(--accent-hsl), 0.9);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: hsl(var(--accent-hsl));
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        #save-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 15px;
            border-radius: 4px;
            background-color: #3498db;
            color: white;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <div class="admin-header">
            <h1>Edit Video Titles</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <p>Edit custom titles for videos that are displayed in the videography section.</p>
        
        <div class="status-bar">
            <div>
                <strong>Total Videos:</strong> <?php echo count($videoFiles); ?>
                <strong>With Custom Titles:</strong> <?php echo count($titles); ?>
            </div>
            <div>
                <small>Titles auto-save as you type</small>
            </div>
        </div>
        
        <form method="post" action="">
            <div class="video-grid">
                <?php foreach ($videoFiles as $video): ?>
                    <?php $hasCustomTitle = isset($titles[$video['file']]); ?>
                    <div class="video-item <?php echo $hasCustomTitle ? 'has-title' : ''; ?>" data-file="<?php echo htmlspecialchars($video['file']); ?>">
                        <?php if (!empty($video['thumbnail'])): ?>
                            <img src="<?php echo $video['thumbnail']; ?>" alt="<?php echo htmlspecialchars($video['file']); ?>" class="video-preview">
                        <?php else: ?>
                            <div class="video-preview">No thumbnail</div>
                        <?php endif; ?>
                        
                        <div class="video-filename">
                            <?php echo htmlspecialchars($video['file']); ?>
                            <?php if ($hasCustomTitle): ?>
                                <span class="has-custom-title">Custom Title</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="video-order">Order: <?php echo $video['order']; ?></div>
                        
                        <div class="title-info">
                            <strong>Auto-generated title:</strong> <?php echo htmlspecialchars($video['auto_title']); ?>
                        </div>
                        
                        <input 
                            type="text"
                            name="titles[<?php echo $video['file']; ?>]" 
                            class="title-field" 
                            data-filename="<?php echo htmlspecialchars($video['file']); ?>"
                            placeholder="Enter a custom title for this video..."
                            value="<?php echo $hasCustomTitle ? htmlspecialchars($titles[$video['file']]) : ''; ?>"
                        >
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="submit-area">
                <button type="submit" class="btn">Save All Titles</button>
            </div>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleFields = document.querySelectorAll('.title-field');
            const saveIndicator = document.createElement('div');
            
            // Create and style the save indicator
            saveIndicator.id = 'save-indicator';
            saveIndicator.textContent = 'Saving...';
            document.body.appendChild(saveIndicator);
            
            // Create a debounce function for the auto-save
            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }
            
            // Function to show and hide the save indicator
            function showSaveIndicator(message, isSuccess = true) {
                saveIndicator.textContent = message;
                saveIndicator.style.backgroundColor = isSuccess ? '#2ecc71' : '#e74c3c';
                saveIndicator.style.opacity = '1';
                
                setTimeout(() => {
                    saveIndicator.style.opacity = '0';
                }, 2000);
            }
            
            // Function to save a title via AJAX
            function saveTitle(filename, title) {
                showSaveIndicator('Saving...');
                
                const formData = new FormData();
                formData.append('filename', filename);
                formData.append('title', title);
                
                fetch('ajax_save_video_title.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSaveIndicator('Saved!', true);
                    } else {
                        showSaveIndicator('Error: ' + data.message, false);
                    }
                })
                .catch(error => {
                    showSaveIndicator('Error: Could not save', false);
                    console.error('Error:', error);
                });
            }
            
            // Debounced save function to prevent too many requests
            const debouncedSave = debounce((input) => {
                const filename = input.dataset.filename;
                const title = input.value;
                saveTitle(filename, title);
            }, 1000); // Wait 1 second after typing stops before saving
            
            // Add input event listeners to all title fields
            titleFields.forEach(input => {
                input.addEventListener('input', function() {
                    debouncedSave(this);
                    
                    // Update the video item classes and indicators
                    const videoItem = this.closest('.video-item');
                    const hasTitle = this.value.trim() !== '';
                    
                    if (hasTitle) {
                        videoItem.classList.add('has-title');
                        
                        // Check if the label already exists
                        if (!videoItem.querySelector('.has-custom-title')) {
                            const filenameDiv = videoItem.querySelector('.video-filename');
                            const label = document.createElement('span');
                            label.className = 'has-custom-title';
                            label.textContent = 'Custom Title';
                            filenameDiv.appendChild(label);
                        }
                    } else {
                        videoItem.classList.remove('has-title');
                        
                        // Remove the label if it exists
                        const label = videoItem.querySelector('.has-custom-title');
                        if (label) {
                            label.remove();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>