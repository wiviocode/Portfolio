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

// Get a featured image for the hero section
$featuredImagesDir = "assets/images";
$featuredImages = [];

// Look for specific featured images or get the first few sorted images
if (file_exists("data/featured_images.json")) {
    $featuredImagesJson = file_get_contents("data/featured_images.json");
    $featuredImagesList = json_decode($featuredImagesJson, true);
    
    if (is_array($featuredImagesList) && count($featuredImagesList) > 0) {
        foreach ($featuredImagesList as $imgFile) {
            $fullPath = $featuredImagesDir . '/' . $imgFile;
            if (file_exists($fullPath)) {
                $imageInfo = getimagesize($fullPath);
                $featuredImages[] = [
                    'file' => $imgFile,
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'alt' => pathinfo($imgFile, PATHINFO_FILENAME)
                ];
            }
        }
    }
}

// If no featured images defined, get 3 random images
if (empty($featuredImages)) {
    $allImages = scandir($featuredImagesDir);
    $validImages = [];
    
    foreach ($allImages as $file) {
        if (preg_match("/\.(jpg|jpeg|png)$/i", $file)) {
            $validImages[] = $file;
        }
    }
    
    // Get 3 random images if we have enough
    if (count($validImages) > 3) {
        shuffle($validImages);
        $randomSelection = array_slice($validImages, 0, 3);
        
        foreach ($randomSelection as $imgFile) {
            $fullPath = $featuredImagesDir . '/' . $imgFile;
            $imageInfo = getimagesize($fullPath);
            $featuredImages[] = [
                'file' => $imgFile,
                'width' => $imageInfo[0],
                'height' => $imageInfo[1],
                'alt' => pathinfo($imgFile, PATHINFO_FILENAME)
            ];
        }
    }
}

include 'includes/header.php';
?>

<main class="landing-page">
  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1>Capturing Moments That Matter</h1>
      <p>Professional photography and videography with a focus on sports, events, and creative storytelling.</p>
      <div class="hero-cta">
        <a href="photography.php" class="cta-button primary">View Photography</a>
        <a href="videography.php" class="cta-button secondary">Watch Videography</a>
      </div>
    </div>
    <div class="hero-image">
      <?php if (!empty($featuredImages)): ?>
        <picture>
          <!-- WebP version -->
          <source
            type="image/webp"
            srcset="<?php echo $baseUrl; ?>/assets/images/webp/<?php echo pathinfo($featuredImages[0]['file'], PATHINFO_FILENAME); ?>.webp"
          >
          <!-- Original image as fallback -->
          <img src="<?php echo $baseUrl; ?>/assets/images/<?php echo $featuredImages[0]['file']; ?>"
               alt="<?php echo htmlspecialchars($featuredImages[0]['alt']); ?>"
               width="<?php echo $featuredImages[0]['width']; ?>"
               height="<?php echo $featuredImages[0]['height']; ?>">
        </picture>
      <?php endif; ?>
    </div>
  </section>

  <!-- Featured Work Section -->
  <section class="featured-work">
    <div class="container">
      <h2>Featured Work</h2>
      <div class="featured-grid">
        <!-- Photography Card -->
        <div class="featured-card">
          <div class="card-image">
            <?php if (count($featuredImages) > 1): ?>
              <picture>
                <source type="image/webp" srcset="<?php echo $baseUrl; ?>/assets/images/webp/<?php echo pathinfo($featuredImages[1]['file'], PATHINFO_FILENAME); ?>.webp">
                <img 
                  src="<?php echo $baseUrl; ?>/assets/images/<?php echo $featuredImages[1]['file']; ?>" 
                  alt="Photography sample - <?php echo htmlspecialchars(pathinfo($featuredImages[1]['file'], PATHINFO_FILENAME)); ?>"
                  class="featured-crop">
              </picture>
            <?php endif; ?>
          </div>
          <div class="card-content">
            <h3>Photography</h3>
            <p>Specializing in sports photography that captures the emotion, intensity, and defining moments of the game.</p>
            <a href="photography.php" class="card-link">View Portfolio</a>
          </div>
        </div>
        
        <!-- Videography Card -->
        <div class="featured-card">
          <div class="card-image">
            <?php if (count($featuredImages) > 2): ?>
              <picture>
                <source type="image/webp" srcset="<?php echo $baseUrl; ?>/assets/images/webp/<?php echo pathinfo($featuredImages[2]['file'], PATHINFO_FILENAME); ?>.webp">
                <img 
                  src="<?php echo $baseUrl; ?>/assets/images/<?php echo $featuredImages[2]['file']; ?>" 
                  alt="Videography sample - <?php echo htmlspecialchars(pathinfo($featuredImages[2]['file'], PATHINFO_FILENAME)); ?>"
                  class="featured-crop">
              </picture>
            <?php endif; ?>
          </div>
          <div class="card-content">
            <h3>Videography</h3>
            <p>Creating compelling video content that tells your story with dynamic visuals and professional editing.</p>
            <a href="videography.php" class="card-link">Watch Videos</a>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- About Preview Section -->
  <section class="about-preview">
    <div class="container">
      <div class="about-grid">
        <div class="about-content">
          <h2>About Eli Larson</h2>
          <p>Passionate photographer and videographer dedicated to telling your story. With years of experience in sports media, I bring technical and creative vision to every project.</p>
          <a href="about.php" class="about-link">Learn More About Me</a>
        </div>
        <div class="about-image">
          <!-- You can add a professional headshot here -->
        </div>
      </div>
    </div>
  </section>
  
  <!-- Contact/CTA Section -->
  <section class="contact-cta">
    <div class="container">
      <h2>Ready to work together?</h2>
      <p>Let's create something amazing. Reach out to discuss your project.</p>
      <a href="mailto:contact@eli-larson.com" class="cta-button primary">Get In Touch</a>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
