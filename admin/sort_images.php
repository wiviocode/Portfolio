<?php
require_once 'includes/auth.php';
require_login();

// Directory containing images
$dirPath = "../assets/images";
$webPath = "../assets/images"; // Path for web URLs
$orderData = load_order_data('image_order.json');

// Save order if AJAX request received
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_order_json'])) {
    $newOrder = json_decode($_POST['image_order_json'], true);
    
    if (is_array($newOrder) && !empty($newOrder)) {
        // Save the new order
        if (save_order_data($newOrder, 'image_order.json')) {
            // Return success response for AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Image order saved successfully!']);
            exit;
        } else {
            // Return error response for AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error saving image order. Please check permissions.']);
            exit;
        }
    }
}

// Get all image files
$files = scandir($dirPath);
$imageFiles = [];
foreach($files as $file) {
    if (preg_match("/\.(jpg|jpeg|png|gif|webp)$/i", $file)) {
        // Get image metadata
        $imagePath = $dirPath . '/' . $file;
        $imageInfo = getimagesize($imagePath);
        
        // Current order (default to 999 if not set)
        $currentOrder = isset($orderData[$file]) ? $orderData[$file] : 999;
        
        // Create image object
        $imageObj = [
            'file' => $file,
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'order' => $currentOrder,
            'alt' => pathinfo($file, PATHINFO_FILENAME),
        ];
        
        $imageFiles[] = $imageObj;
    }
}

// Sort images by current order
usort($imageFiles, function($a, $b) {
    // Sort by order first
    if ($a['order'] !== $b['order']) {
        return $a['order'] - $b['order'];
    }
    // Then by filename as fallback
    return strcmp($a['file'], $b['file']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Image Order - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Admin specific styles */
        .admin-content {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid hsla(var(--accent-hsl), 0.2);
        }
        .admin-header h1 {
            margin: 0;
            color: hsl(var(--accent-hsl));
        }
        
        /* Exact copy of main site portfolio grid styles */
        .portfolio-grid {
            column-count: 3;
            column-gap: 15px;
        }
        
        .portfolio-item {
            break-inside: avoid;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
            border-radius: 0px;
            transition: transform var(--transition-medium);
            cursor: grab;
        }
        
        .portfolio-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .portfolio-item img {
            width: 100%;
            height: auto;
            display: block;
            opacity: 1; /* Ensure images are visible */
            transition: transform var(--transition-medium);
            border-radius: 0px;
            filter: brightness(1);
            background-color: #f0f0f0; /* Light background while loading */
        }
        
        .portfolio-item:hover img {
            transform: scale(1.02);
            filter: brightness(1.05);
        }
        
        /* Admin-specific additions */
        .portfolio-item.dragging {
            opacity: 0.7;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            z-index: 10;
        }
        
        .image-order {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: hsl(var(--accent-hsl));
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
            z-index: 2;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .image-filename {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px;
            background-color: rgba(0,0,0,0.7);
            color: white;
            font-size: 12px;
            text-align: center;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .portfolio-item:hover .image-filename {
            opacity: 1;
        }
        
        #save-message {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }
        
        #save-message.show {
            opacity: 1;
        }
        
        .drag-instructions {
            margin-bottom: 20px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
        
        .admin-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: hsl(var(--accent-hsl));
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        
        .admin-btn:hover {
            background-color: hsl(var(--darkAccent-hsl));
        }
        
        .admin-btn.secondary {
            background-color: #6c757d;
            margin-left: 10px;
        }
        
        .admin-btn.secondary:hover {
            background-color: #5a6268;
        }
    </style>
    <!-- Add Sortable.js library -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body>
    <div class="admin-content">
        <div class="admin-header">
            <h1>Manage Image Order</h1>
            <div>
                <a href="dashboard.php" class="admin-btn secondary">Back to Dashboard</a>
            </div>
        </div>
        
        <div class="drag-instructions">
            <p><strong>Drag and drop the images to reorder them.</strong> Changes are saved automatically.</p>
        </div>
        
        <div id="save-message">Order saved successfully!</div>
        
        <section id="sortable-grid" class="portfolio-grid">
            <?php foreach ($imageFiles as $index => $img): ?>
            <div class="portfolio-item" data-filename="<?php echo htmlspecialchars($img['file']); ?>">
                <div class="image-order"><?php echo $index + 1; ?></div>
                <img src="<?php echo $webPath . '/' . $img['file']; ?>"
                     alt="<?php echo htmlspecialchars($img['alt']); ?>"
                     width="<?php echo $img['width']; ?>"
                     height="<?php echo $img['height']; ?>"
                     loading="lazy">
                <div class="image-filename">
                    <?php echo htmlspecialchars($img['file']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </section>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Sortable
            const sortableGrid = document.getElementById('sortable-grid');
            const saveMessage = document.getElementById('save-message');
            
            const sortable = new Sortable(sortableGrid, {
                animation: 150,
                ghostClass: 'dragging',
                onEnd: function() {
                    // Update the order numbers visually
                    updateOrderNumbers();
                    
                    // Save the new order
                    saveOrder();
                }
            });
            
            // Function to update order numbers displayed on the items
            function updateOrderNumbers() {
                const items = sortableGrid.querySelectorAll('.portfolio-item');
                items.forEach((item, index) => {
                    const orderElement = item.querySelector('.image-order');
                    orderElement.textContent = index + 1;
                });
            }
            
            // Function to save the new order
            function saveOrder() {
                const items = sortableGrid.querySelectorAll('.portfolio-item');
                const newOrder = {};
                
                items.forEach((item, index) => {
                    const filename = item.getAttribute('data-filename');
                    newOrder[filename] = index + 1;
                });
                
                // Send the order to the server
                const formData = new FormData();
                formData.append('image_order_json', JSON.stringify(newOrder));
                
                fetch('sort_images.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show save message
                        saveMessage.classList.add('show');
                        
                        // Hide the message after 2 seconds
                        setTimeout(function() {
                            saveMessage.classList.remove('show');
                        }, 2000);
                    } else {
                        alert('Error saving order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the order.');
                });
            }
        });
    </script>
</body>
</html> 