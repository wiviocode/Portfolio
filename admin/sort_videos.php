<?php
require_once 'includes/auth.php';
require_login();

// Directory containing videos
$dirPath = "../assets/videos";
$thumbnailPath = "../assets/video-thumbnails";
$orderData = load_order_data('video_order.json');

// Save order if AJAX request received
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_order_json'])) {
    $newOrder = json_decode($_POST['video_order_json'], true);
    
    if (is_array($newOrder) && !empty($newOrder)) {
        // Save the new order
        if (save_order_data($newOrder, 'video_order.json')) {
            // Return success response for AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Video order saved successfully!']);
            exit;
        } else {
            // Return error response for AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error saving video order. Please check permissions.']);
            exit;
        }
    }
}

// Get all video files
$files = scandir($dirPath);
$videoFiles = [];
foreach($files as $file) {
    if (preg_match("/\.(mp4|mov|webm|ogg)$/i", $file)) {
        // Current order (default to 999 if not set)
        $currentOrder = isset($orderData[$file]) ? $orderData[$file] : 999;
        
        // Create video object
        $videoObj = [
            'file' => $file,
            'title' => ucwords(str_replace(['_', '-'], ' ', pathinfo($file, PATHINFO_FILENAME))),
            'order' => $currentOrder,
            'thumbnail' => $thumbnailPath . '/' . pathinfo($file, PATHINFO_FILENAME) . '.jpg'
        ];
        
        $videoFiles[] = $videoObj;
    }
}

// Sort videos by current order
usort($videoFiles, function($a, $b) {
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
    <title>Manage Video Order - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-content {
            max-width: 1200px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .video-item {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            position: relative;
            cursor: grab;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .video-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .video-item.dragging {
            opacity: 0.7;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            z-index: 10;
        }
        .video-thumbnail {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .video-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .video-order {
            position: absolute;
            top: 5px;
            left: 5px;
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
        }
        .video-filename {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
            word-break: break-all;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .form-actions {
            text-align: center;
            margin: 30px 0;
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
    </style>
    <!-- Add Sortable.js library -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body>
    <div class="admin-content">
        <div class="admin-header">
            <h1>Manage Video Order</h1>
            <div>
                <a href="dashboard.php" class="admin-btn secondary">Back to Dashboard</a>
            </div>
        </div>
        
        <div class="drag-instructions">
            <p><strong>Drag and drop the videos to reorder them.</strong> Changes are saved automatically.</p>
        </div>
        
        <div id="save-message">Order saved successfully!</div>
        
        <div id="sortable-grid" class="admin-grid">
            <?php foreach ($videoFiles as $index => $video): ?>
            <div class="video-item" data-filename="<?php echo htmlspecialchars($video['file']); ?>">
                <div class="video-order"><?php echo $index + 1; ?></div>
                <img src="<?php echo $video['thumbnail']; ?>" 
                     alt="<?php echo htmlspecialchars($video['title']); ?>" 
                     class="video-thumbnail">
                <div class="video-title">
                    <?php echo htmlspecialchars($video['title']); ?>
                </div>
                <div class="video-filename">
                    <?php echo $video['file']; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
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
                const items = sortableGrid.querySelectorAll('.video-item');
                items.forEach((item, index) => {
                    const orderElement = item.querySelector('.video-order');
                    orderElement.textContent = index + 1;
                });
            }
            
            // Function to save the new order
            function saveOrder() {
                const items = sortableGrid.querySelectorAll('.video-item');
                const newOrder = {};
                
                items.forEach((item, index) => {
                    const filename = item.getAttribute('data-filename');
                    newOrder[filename] = index + 1;
                });
                
                // Send the order to the server
                const formData = new FormData();
                formData.append('video_order_json', JSON.stringify(newOrder));
                
                fetch('sort_videos.php', {
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