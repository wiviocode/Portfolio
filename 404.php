<?php
// Include error configuration
include_once __DIR__ . '/includes/error_config.php';

// Set the HTTP response code
http_response_code(404);

// Include header
include 'includes/header.php';
?>

<main class="error-page">
  <div class="container">
    <div class="error-content">
      <h1>404</h1>
      <h2>Page Not Found</h2>
      <p>The page you're looking for doesn't exist or has been moved.</p>
      <div class="error-actions">
        <a href="index.php" class="cta-button primary">Return Home</a>
        <a href="photography.php" class="cta-button secondary">View Photography</a>
        <a href="videography.php" class="cta-button secondary">Watch Videography</a>
      </div>
    </div>
  </div>
</main>

<?php include 'includes/footer.php'; ?> 