<?php
// Dynamically load videos from assets/videos folder
$dirPath = "assets/videos";
$files = scandir($dirPath);
$videoFiles = [];
foreach($files as $file) {
  if(preg_match("/\.(mp4|mov|webm|ogg)$/i", $file)) {
    $videoFiles[] = $file;
  }
}
usort($videoFiles, "strcmp");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Videography - Eli Larson</title>
  <link rel="stylesheet" href="css/style.css">
  <!-- Google Fonts as in your design -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital,wght@0,400;1,400&family=Newsreader:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>
  <header class="site-header">
    <div class="logo">Eli Larson</div>
    <nav class="site-nav">
      <ul>
        <li><a href="index.php">Photography</a></li>
        <li><a href="videography.php" class="active">Videography</a></li>
        <li><a href="about.html">About Me</a></li>
        <li class="social">
          <a href="https://www.instagram.com/strike_lnk/" target="_blank">
            <!-- Corrected inline Instagram SVG -->
            <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <title>Instagram</title>
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.333 3.608 1.308.975.975 1.246 2.242 1.308 3.608.058 1.266.07 1.645.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.333 2.633-1.308 3.608-.975.975-2.242 1.246-3.608 1.308-1.266.058-1.645.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.333-3.608-1.308-.975-.975-1.246-2.242-1.308-3.608C2.175 15.747 2.163 15.368 2.163 12s.012-3.584.07-4.85c.062-1.366.333-2.633 1.308-3.608C4.508 2.496 5.775 2.225 7.14 2.163 8.406 2.105 8.785 2.093 12 2.093M12 0C8.741 0 8.332.013 7.052.072 5.771.131 4.602.443 3.635 1.41 2.667 2.378 2.355 3.547 2.296 4.828.237 4.828 0 8.741 0 12s.237 7.172.296 8.452c.059 1.281.371 2.45 1.338 3.417.967.967 2.136 1.279 3.417 1.338C8.332 23.987 8.741 24 12 24s3.668-.013 4.948-.072c1.281-.059 2.45-.371 3.417-1.338.967-.967 1.279-2.136 1.338-3.417.059-1.28.072-1.689.072-4.948s-.013-3.668-.072-4.948c-.059-1.281-.371-2.45-1.338-3.417C19.398.443 18.229.131 16.948.072 15.668.013 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324A6.162 6.162 0 0 0 12 5.838zm0 10.162a3.999 3.999 0 1 1 0-8 3.999 3.999 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 1-1.44-1.44 1.44 1.44 0 0 1 1.44 1.44z"/>
            </svg>
          </a>
        </li>
        <li class="social">
          <a href="https://www.linkedin.com/in/eli-larson-b191152b1/" target="_blank">
            <!-- Inline LinkedIn SVG -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
              <path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1A53.79 53.79 0 1 1 107.58 54.32a53.8 53.8 0 0 1-53.79 53.79zM447.9 448h-92.68V302.4c0-34.73-.69-79.44-48.34-79.44-48.34 0-55.8 37.69-55.8 76.55V448H158.4V148.9h89V184h1.28a97.74 97.74 0 0 1 87.92-48.34c94 0 111.28 61.88 111.28 142.3V448z"/>
            </svg>
          </a>
        </li>
      </ul>
    </nav>
  </header>
  <main>
    <!-- Container provides blank space on the sides -->
    <div class="container">
      <section class="video-grid">
        <?php foreach ($videoFiles as $video): ?>
        <div class="video-item">
          <video controls>
            <source src="assets/videos/<?php echo $video; ?>" type="video/mp4">
            Your browser does not support the video tag.
          </video>
        </div>
        <?php endforeach; ?>
      </section>
    </div>
  </main>
  <footer class="site-footer">
    <p>&copy; <span id="year"></span> Eli Larson</p>
  </footer>

  <!-- Lightbox Preview Container for images (if needed) -->
  <div id="lightbox" class="lightbox">
    <span class="close">&times;</span>
    <span class="arrow prev">&#10094;</span>
    <img id="lightbox-img" src="" alt="Preview">
    <span class="arrow next">&#10095;</span>
  </div>

  <script src="js/script.js"></script>
</body>
</html>
