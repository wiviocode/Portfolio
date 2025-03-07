<?php
// Test file to check configuration and paths

// Echo server information
echo "<h2>Server Information</h2>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Server Name: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>HTTP Host: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Calculate base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol . $domainName;
}

$baseUrl = getBaseUrl();
echo "<p>Calculated Base URL: " . $baseUrl . "</p>";

// Check if CSS file exists
$cssPath = $_SERVER['DOCUMENT_ROOT'] . "/css/style.css";
echo "<p>CSS physical path: " . $cssPath . "</p>";
echo "<p>CSS file exists: " . (file_exists($cssPath) ? "Yes" : "No") . "</p>";

// Check file permissions
if (file_exists($cssPath)) {
    echo "<p>CSS file permissions: " . substr(sprintf('%o', fileperms($cssPath)), -4) . "</p>";
}

// Test CSS link
echo "<h2>CSS Test</h2>";
echo "<p>This should be styled if CSS is working:</p>";
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<link rel='stylesheet' href='" . $baseUrl . "/css/style.css'>";
echo "<h3>Test Heading</h3>";
echo "<p>This is a test paragraph.</p>";
echo "<a href='#' class='cta-button primary'>Test Button</a>";
echo "</div>";

// Output PHP information
echo "<h2>PHP Info Summary</h2>";
ob_start();
phpinfo(INFO_CONFIGURATION);
$phpinfo = ob_get_clean();

// Extract only the table from phpinfo
if (preg_match('/<table.*?>.*<\/table>/is', $phpinfo, $matches)) {
    echo $matches[0];
}
?> 