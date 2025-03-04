<?php
// Dynamically load videos from assets/videos folder
$dirPath = "assets/videos";
$files = scandir($dirPath);
$videoFiles = [];
foreach($files as $file) {
  if(preg_match("/\.(mp4|mov|webm|ogg)$/i", $file)) {
    // Create video object with metadata
    $videoObj = [
      'file' => $file,
      'title' => ucwords(str_replace(['_', '-'], ' ', pathinfo($file, PATHINFO_FILENAME))),
    ];
    $videoFiles[] = $videoObj;
  }
}

// Sort videos by name for consistency
usort($videoFiles, function($a, $b) {
  return strcmp($a['file'], $b['file']);
});

// Generate a random string for cache busting
$cacheBuster = md5(time());

include 'includes/header.php';
?>

<main>
  <!-- Container provides blank space on the sides -->
  <div class="container">
    <section class="video-grid">
      <?php foreach ($videoFiles as $video): ?>
      <div class="video-item">
        <video controls preload="none" poster="assets/video-thumbnails/<?php echo pathinfo($video['file'], PATHINFO_FILENAME); ?>.jpg">
          <source data-src="assets/videos/<?php echo $video['file']; ?>" type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <div class="video-title"><?php echo htmlspecialchars($video['title']); ?></div>
      </div>
      <?php endforeach; ?>
    </section>
  </div>
</main>

<?php include 'includes/footer.php'; ?>
