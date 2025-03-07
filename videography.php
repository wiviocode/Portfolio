<?php
// Load custom order data if available
$orderDataFile = "data/video_order.json";
$orderData = [];
if (file_exists($orderDataFile)) {
    $jsonData = file_get_contents($orderDataFile);
    $orderData = json_decode($jsonData, true);
}

// Dynamically load videos from assets/videos folder
$dirPath = "assets/videos";
$files = scandir($dirPath);
$videoFiles = [];
foreach($files as $file) {
  if(preg_match("/\.(mp4|mov|webm|ogg)$/i", $file)) {
    // Get order (default to 999 if not set)
    $order = isset($orderData[$file]) ? $orderData[$file] : 999;
    
    // Get filename without extension for thumbnail matching
    $fileNameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
    
    // Check for WebP thumbnail first, then fallback to jpg
    $thumbnailPath = "";
    if (file_exists("assets/video-thumbnails/{$fileNameWithoutExt}.webp")) {
      $thumbnailPath = "assets/video-thumbnails/{$fileNameWithoutExt}.webp";
    } elseif (file_exists("assets/video-thumbnails/{$fileNameWithoutExt}.jpg")) {
      $thumbnailPath = "assets/video-thumbnails/{$fileNameWithoutExt}.jpg";
    }
    
    // Create video object with metadata
    $videoObj = [
      'file' => $file,
      'title' => ucwords(str_replace(['_', '-'], ' ', $fileNameWithoutExt)),
      'order' => $order,
      'thumbnail' => $thumbnailPath
    ];
    $videoFiles[] = $videoObj;
  }
}

// Sort videos by custom order
usort($videoFiles, function($a, $b) {
  // First by order
  if ($a['order'] !== $b['order']) {
    return $a['order'] - $b['order'];
  }
  // Then by name as fallback
  return strcmp($a['file'], $b['file']);
});

// Generate a random string for cache busting
$cacheBuster = md5(time());

include 'includes/header.php';
?>

<main>
  <!-- Container provides blank space on the sides -->
  <div class="container">
    <h1 class="section-title">Videography</h1>
    
    <section class="video-grid">
      <?php foreach ($videoFiles as $video): ?>
      <div class="video-item" data-video-id="video-<?php echo md5($video['file']); ?>">
        <video 
          id="video-<?php echo md5($video['file']); ?>"
          preload="metadata" 
          playsinline
          <?php if (!empty($video['thumbnail'])): ?>
          poster="<?php echo $baseUrl; ?>/<?php echo $video['thumbnail']; ?>?v=<?php echo $cacheBuster; ?>"
          <?php endif; ?>
          >
          <source src="<?php echo $baseUrl; ?>/assets/videos/<?php echo $video['file']; ?>" type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <div class="video-title"><?php echo htmlspecialchars($video['title']); ?></div>
      </div>
      <?php endforeach; ?>
    </section>
    
    <div class="video-instructions">
      <p>Click on any video to play. Only one video will play at a time.</p>
    </div>
  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Get all video items
    const videoItems = document.querySelectorAll('.video-item');
    
    // Add click handler to each video item
    videoItems.forEach(item => {
      item.addEventListener('click', function() {
        const videoId = this.getAttribute('data-video-id');
        const video = document.getElementById(videoId);
        
        if (!video) return;
        
        // Add controls if not present
        if (!video.hasAttribute('controls')) {
          video.setAttribute('controls', '');
        }
        
        // Pause all other videos
        document.querySelectorAll('.video-item video').forEach(v => {
          if (v.id !== videoId && !v.paused) {
            v.pause();
            v.parentElement.classList.remove('playing');
          }
        });
        
        // Toggle play/pause
        if (video.paused) {
          // Play this video
          video.play()
            .then(() => {
              this.classList.add('playing');
              console.log('Video playing successfully');
            })
            .catch(err => {
              console.error('Error playing video:', err);
            });
        } else {
          video.pause();
          this.classList.remove('playing');
        }
      });
    });
  });
</script>

<?php include 'includes/footer.php'; ?>
