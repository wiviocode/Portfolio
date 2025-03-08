<?php
// Include authentication
include_once '../includes/header.php';
include_once 'includes/auth.php';

// File paths
$descriptionsFile = "../data/image_descriptions.json";
$imagesDir = "../assets/images";

// Message handling
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $descriptions = $_POST['descriptions'] ?? [];
    
    // Get current data to preserve metadata
    $currentData = [];
    if (file_exists($descriptionsFile)) {
        $jsonContent = file_get_contents($descriptionsFile);
        $currentData = json_decode($jsonContent, true) ?: [];
    }
    
    // Preserve metadata entries (keys starting with _)
    $finalData = [];
    foreach ($currentData as $key => $value) {
        if (substr($key, 0, 1) === '_') {
            $finalData[$key] = $value;
        }
    }
    
    // Add the descriptions
    foreach ($descriptions as $filename => $description) {
        if (trim($description) !== '') {
            $finalData[$filename] = $description;
        }
    }
    
    // Save back to file
    if (file_put_contents($descriptionsFile, json_encode($finalData, JSON_PRETTY_PRINT))) {
        $message = 'Image descriptions have been saved successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error saving descriptions. Check file permissions.';
        $messageType = 'error';
    }
}

// Load current descriptions
$descriptions = [];
if (file_exists($descriptionsFile)) {
    $jsonContent = file_get_contents($descriptionsFile);
    $data = json_decode($jsonContent, true) ?: [];
    
    // Filter out metadata keys
    foreach ($data as $key => $value) {
        if (substr($key, 0, 1) !== '_') {
            $descriptions[$key] = $value;
        }
    }
}

// Load image order data to match photography page display
$orderDataFile = "../data/image_order.json";
$orderData = [];
if (file_exists($orderDataFile)) {
    $jsonData = file_get_contents($orderDataFile);
    $orderData = json_decode($jsonData, true) ?: [];
}

// Get image files and apply same sorting as photography page
$imageFiles = [];
if (is_dir($imagesDir)) {
    $files = scandir($imagesDir);
    foreach ($files as $file) {
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            // Get image dimensions and metadata
            $imagePath = $imagesDir . '/' . $file;
            if (file_exists($imagePath)) {
                $imageInfo = getimagesize($imagePath);
                
                // Get order (default to 999 if not set)
                $order = isset($orderData[$file]) ? $orderData[$file] : 999;
                
                // Create image object with metadata
                $imageObj = [
                    'file' => $file,
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'order' => $order
                ];
                $imageFiles[] = $imageObj;
            }
        }
    }
    
    // Sort images by custom order - identical to photography page
    usort($imageFiles, function($a, $b) {
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
    <title>Edit Image Descriptions - Admin</title>
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
        
        .status-bar {
            padding: 10px;
            margin: 10px 0 20px;
            border-radius: 4px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .image-filters {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .search-box {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            flex-grow: 1;
            max-width: 300px;
        }
        
        .has-description {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            color: white;
            background-color: #2ecc71;
            display: inline-block;
            margin-left: 5px;
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
        
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .image-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background-color: #fff;
        }
        
        .image-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        
        .image-filename {
            font-size: 14px;
            margin-bottom: 5px;
            font-family: monospace;
            word-break: break-all;
        }
        
        .image-order {
            font-size: 12px;
            margin-bottom: 10px;
            color: #666;
        }
        
        textarea {
            width: 100%;
            height: 100px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-family: inherit;
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
    </style>
</head>
<body>
    <div class="admin-container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <div class="admin-header">
            <h1>Edit Image Descriptions</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <p>Add descriptions to images that will be shown in tooltips when hovering over the images in the photography portfolio.</p>
        
        <div class="status-bar">
            <div>
                <strong>Total Images:</strong> <?php echo count($imageFiles); ?>
                <strong>With Descriptions:</strong> <?php echo count($descriptions); ?>
            </div>
            <div>
                <small>Descriptions auto-save as you type</small>
            </div>
        </div>
        
        <div class="image-filters">
            <input type="text" id="search-box" class="search-box" placeholder="Search images...">
            <label>
                <input type="checkbox" id="filter-has-description"> 
                Show only images with descriptions
            </label>
            <label>
                <input type="checkbox" id="filter-no-description"> 
                Show only images without descriptions
            </label>
        </div>
        
        <form method="post" action="">
            <div class="image-grid">
                <?php foreach ($imageFiles as $img): ?>
                    <?php $hasDesc = isset($descriptions[$img['file']]); ?>
                    <div class="image-item <?php echo $hasDesc ? 'has-desc' : 'no-desc'; ?>" data-file="<?php echo htmlspecialchars($img['file']); ?>">
                        <img src="<?php echo "../assets/images/{$img['file']}"; ?>" alt="<?php echo htmlspecialchars($img['file']); ?>" class="image-preview">
                        <div class="image-filename">
                            <?php echo htmlspecialchars($img['file']); ?>
                            <?php if ($hasDesc): ?>
                                <span class="has-description">Has Description</span>
                            <?php endif; ?>
                        </div>
                        <div class="image-order">Order: <?php echo $img['order']; ?></div>
                        <textarea 
                            name="descriptions[<?php echo $img['file']; ?>]" 
                            class="description-field" 
                            data-filename="<?php echo htmlspecialchars($img['file']); ?>"
                            placeholder="Enter a description for this image..."
                        ><?php echo $hasDesc ? htmlspecialchars($descriptions[$img['file']]) : ''; ?></textarea>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="submit-area">
                <button type="submit" class="btn">Save Descriptions</button>
            </div>
        </form>
    </div>
    
    <script>
        // Auto-save functionality
        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('.description-field');
            const saveIndicator = document.createElement('div');
            const searchBox = document.getElementById('search-box');
            const filterHasDesc = document.getElementById('filter-has-description');
            const filterNoDesc = document.getElementById('filter-no-description');
            const imageItems = document.querySelectorAll('.image-item');
            
            // Create and style the save indicator
            saveIndicator.id = 'save-indicator';
            saveIndicator.style.position = 'fixed';
            saveIndicator.style.bottom = '20px';
            saveIndicator.style.right = '20px';
            saveIndicator.style.padding = '10px 15px';
            saveIndicator.style.borderRadius = '4px';
            saveIndicator.style.backgroundColor = '#3498db';
            saveIndicator.style.color = 'white';
            saveIndicator.style.opacity = '0';
            saveIndicator.style.transition = 'opacity 0.3s ease';
            saveIndicator.style.zIndex = '9999';
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
            
            // Function to save a description via AJAX
            function saveDescription(filename, description) {
                showSaveIndicator('Saving...');
                
                const formData = new FormData();
                formData.append('filename', filename);
                formData.append('description', description);
                
                fetch('ajax_save_description.php', {
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
            const debouncedSave = debounce((textarea) => {
                const filename = textarea.dataset.filename;
                const description = textarea.value;
                saveDescription(filename, description);
            }, 1000); // Wait 1 second after typing stops before saving
            
            // Add input event listeners to all textareas
            textareas.forEach(textarea => {
                textarea.addEventListener('input', function() {
                    debouncedSave(this);
                });
            });
            
            // Add manual save button functionality
            const saveButton = document.querySelector('.btn');
            saveButton.textContent = 'Save All Descriptions';
            
            // Filtering functionality
            function applyFilters() {
                const searchTerm = searchBox.value.toLowerCase();
                const showHasDesc = filterHasDesc.checked;
                const showNoDesc = filterNoDesc.checked;
                
                imageItems.forEach(item => {
                    const filename = item.dataset.file.toLowerCase();
                    const hasDescription = item.classList.contains('has-desc');
                    
                    // Default visibility
                    let visible = true;
                    
                    // Apply search filter
                    if (searchTerm && !filename.includes(searchTerm)) {
                        visible = false;
                    }
                    
                    // Apply description filters
                    if (showHasDesc && !hasDescription) {
                        visible = false;
                    }
                    
                    if (showNoDesc && hasDescription) {
                        visible = false;
                    }
                    
                    // Set visibility
                    item.style.display = visible ? 'block' : 'none';
                });
            }
            
            // Add event listeners for filters
            searchBox.addEventListener('input', applyFilters);
            filterHasDesc.addEventListener('change', function() {
                if (this.checked) {
                    filterNoDesc.checked = false;
                }
                applyFilters();
            });
            
            filterNoDesc.addEventListener('change', function() {
                if (this.checked) {
                    filterHasDesc.checked = false;
                }
                applyFilters();
            });
            
            // Update the description status indicator when a description is added/removed
            textareas.forEach(textarea => {
                textarea.addEventListener('input', function() {
                    const imageItem = this.closest('.image-item');
                    const hasDesc = this.value.trim() !== '';
                    
                    if (hasDesc) {
                        imageItem.classList.add('has-desc');
                        imageItem.classList.remove('no-desc');
                        
                        // Check if the label already exists
                        if (!imageItem.querySelector('.has-description')) {
                            const filenameDiv = imageItem.querySelector('.image-filename');
                            const label = document.createElement('span');
                            label.className = 'has-description';
                            label.textContent = 'Has Description';
                            filenameDiv.appendChild(label);
                        }
                    } else {
                        imageItem.classList.remove('has-desc');
                        imageItem.classList.add('no-desc');
                        
                        // Remove the label if it exists
                        const label = imageItem.querySelector('.has-description');
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