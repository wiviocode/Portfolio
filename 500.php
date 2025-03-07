<?php
// Include error configuration
include_once __DIR__ . '/includes/error_config.php';

// Set the HTTP response code
http_response_code(500);

// Include header
include 'includes/header.php';
?>

<main class="error-page">
  <div class="container">
    <div class="error-content">
      <h1>500</h1>
      <h2>Server Error</h2>
      <p>We're experiencing some technical difficulties. Please try again later.</p>
      <div class="error-actions">
        <a href="index.php" class="cta-button primary">Return Home</a>
        <a href="javascript:history.back()" class="cta-button secondary">Go Back</a>
      </div>
    </div>
  </div>
</main>

<?php include 'includes/footer.php'; ?> 