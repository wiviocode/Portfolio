<?php
// Load custom order data if available
$orderDataFile = "data/video_order.json";
$orderData = [];
if (file_exists($orderDataFile)) {
    $jsonData = file_get_contents($orderDataFile);
    $orderData = json_decode($jsonData, true);
}

// Load custom video titles if available
$titlesDataFile = "data/video_titles.json";
$titlesData = [];
if (file_exists($titlesDataFile)) {
    $jsonData = file_get_contents($titlesDataFile);
    $titlesData = json_decode($jsonData, true) ?: [];
    
    // Remove any metadata keys that start with underscore
    foreach ($titlesData as $key => $value) {
        if (substr($key, 0, 1) === '_') {
            unset($titlesData[$key]);
        }
    }
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
    
    // Use custom title if available, otherwise generate one from filename
    $videoTitle = isset($titlesData[$file]) 
        ? $titlesData[$file] 
        : ucwords(str_replace(['_', '-'], ' ', $fileNameWithoutExt));
    
    // Create video object with metadata
    $videoObj = [
      'file' => $file,
      'title' => $videoTitle,
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
      <p>Click on any video to open and play in a larger view.</p>
    </div>
  </div>
  
  <!-- Video Lightbox -->
  <div id="video-lightbox" class="video-lightbox">
    <div class="video-lightbox-content">
      <span class="close-video">&times;</span>
      <div class="video-container">
        <video id="lightbox-video" controls playsinline>
          <source src="" type="video/mp4">
          Your browser does not support the video tag.
        </video>
      </div>
      <h3 id="lightbox-video-title" class="lightbox-video-title"></h3>
    </div>
  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Get all video items and lightbox elements
    const videoItems = document.querySelectorAll('.video-item');
    const videoLightbox = document.getElementById('video-lightbox');
    const lightboxVideo = document.getElementById('lightbox-video');
    const lightboxVideoSource = lightboxVideo.querySelector('source');
    const lightboxVideoTitle = document.getElementById('lightbox-video-title');
    const closeButton = document.querySelector('.close-video');
    let currentVideo = null;
    
    // Prevent the grid videos from playing when clicked
    document.querySelectorAll('.video-item video').forEach(video => {
      // Add the 'disablePlay' property to videos in the grid
      video.disablePlay = true;
      
      // Prevent default play behavior by immediately pausing
      video.addEventListener('play', function(e) {
        if (this.disablePlay) {
          this.pause();
        }
      });
    });
    
    // Function to open the lightbox
    function openVideoLightbox(videoSrc, videoTitle) {
      // Set the video source and load it
      lightboxVideoSource.setAttribute('src', videoSrc);
      lightboxVideo.load();
      
      // Set the title
      lightboxVideoTitle.textContent = videoTitle;
      
      // Show the lightbox
      requestAnimationFrame(() => {
        videoLightbox.classList.add('active');
        document.body.classList.add('lightbox-open');
      });
      
      // Auto-play the video - slight delay to allow animation to complete
      setTimeout(() => {
        lightboxVideo.play()
          .catch(err => {
            console.warn('Auto-play prevented:', err);
          });
      }, 400);
    }
    
    // Function to close the lightbox
    function closeVideoLightbox() {
      // Pause the video
      lightboxVideo.pause();
      
      // Hide the lightbox with animation
      videoLightbox.classList.remove('active');
      
      // Wait for animation to complete before removing body class
      setTimeout(() => {
        document.body.classList.remove('lightbox-open');
        
        // Reset the current video
        currentVideo = null;
      }, 400);
    }
    
    // Add click handler to each video item
    videoItems.forEach(item => {
      item.addEventListener('click', function() {
        const videoId = this.getAttribute('data-video-id');
        const video = document.getElementById(videoId);
        const videoTitle = this.querySelector('.video-title').textContent;
        const videoSrc = video.querySelector('source').getAttribute('src');
        
        // Make sure the original video is paused
        if (video && !video.paused) {
          video.pause();
        }
        
        // Open the lightbox with this video
        openVideoLightbox(videoSrc, videoTitle);
        currentVideo = this;
      });
    });
    
    // Close button event
    closeButton.addEventListener('click', closeVideoLightbox);
    
    // Click outside to close
    videoLightbox.addEventListener('click', function(e) {
      if (e.target === videoLightbox) {
        closeVideoLightbox();
      }
    });
    
    // Keyboard events
    document.addEventListener('keydown', function(e) {
      if (videoLightbox.classList.contains('active')) {
        if (e.key === 'Escape') {
          closeVideoLightbox();
        }
      }
    });
    
    // Video ended event
    lightboxVideo.addEventListener('ended', function() {
      // Optional: close lightbox when video ends
      // closeVideoLightbox();
    });
  });
</script>

<?php include 'includes/footer.php'; ?>
