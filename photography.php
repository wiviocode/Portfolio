<?php
// Load custom order data if available
$orderDataFile = "data/image_order.json";
$orderData = [];
if (file_exists($orderDataFile)) {
    $jsonData = file_get_contents($orderDataFile);
    $orderData = json_decode($jsonData, true);
}

// Dynamically load images from assets/images folder
$dirPath = "assets/images";
$files = scandir($dirPath);
$imageFiles = [];
foreach($files as $file) {
  if(preg_match("/\.(jpg|jpeg|png|gif|webp)$/i", $file)) {
    // Get image dimensions and metadata
    $imagePath = $dirPath . '/' . $file;
    $imageInfo = getimagesize($imagePath);
    // Get order (default to 999 if not set)
    $order = isset($orderData[$file]) ? $orderData[$file] : 999;
    // Create image object with metadata
    $imageObj = [
      'file' => $file,
      'width' => $imageInfo[0],
      'height' => $imageInfo[1],
      'alt' => pathinfo($file, PATHINFO_FILENAME), // Use filename as alt text
      'order' => $order
    ];
    $imageFiles[] = $imageObj;
  }
}

// Sort images by custom order
usort($imageFiles, function($a, $b) {
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
    <h1 class="section-title">Photography</h1>
    
    <section class="portfolio-grid">
      <?php foreach ($imageFiles as $img): ?>
      <div class="portfolio-item">
        <picture>
          <!-- WebP version -->
          <source
            type="image/webp"
            srcset="<?php echo $baseUrl; ?>/assets/images/webp/<?php echo pathinfo($img['file'], PATHINFO_FILENAME); ?>.webp"
            data-srcset="<?php echo $baseUrl; ?>/assets/images/webp/<?php echo pathinfo($img['file'], PATHINFO_FILENAME); ?>.webp"
          >
          <!-- Original image as fallback -->
          <img data-src="<?php echo $baseUrl; ?>/assets/images/<?php echo $img['file']; ?>"
               src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 <?php echo $img['width']; ?> <?php echo $img['height']; ?>'%3E%3C/svg%3E"
               alt="<?php echo htmlspecialchars($img['alt']); ?>"
               width="<?php echo $img['width']; ?>"
               height="<?php echo $img['height']; ?>"
               loading="lazy"
               sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw">
        </picture>
      </div>
      <?php endforeach; ?>
    </section>
  </div>
</main>

<?php include 'includes/footer.php'; ?> 