<?php
/**
 * Pre-launch check script
 * Run this script to check for common issues before publishing the site
 */

// Buffer all output
ob_start();

echo "====== PRE-LAUNCH WEBSITE CHECK ======\n";
echo "Starting checks at: " . date('Y-m-d H:i:s') . "\n\n";

// Important files to check
$importantFiles = [
    'index.php',
    'photography.php',
    'videography.php',
    'about.php',
    'robots.txt',
    'sitemap.xml',
    '.htaccess',
    'favicon.ico',
    '404.php',
    '500.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/error_config.php',
    'css/style.css',
    'js/script.js'
];

echo "=== CHECKING REQUIRED FILES ===\n";
foreach ($importantFiles as $file) {
    if (file_exists($file)) {
        echo "✓ Found: $file\n";
    } else {
        echo "✗ MISSING: $file\n";
    }
}
echo "\n";

// Check for favicon
echo "=== CHECKING FAVICON ===\n";
if (file_exists('favicon.ico')) {
    echo "✓ Favicon exists\n";
} else {
    echo "✗ Favicon is missing\n";
}

// Check for Open Graph image
echo "=== CHECKING OPEN GRAPH IMAGE ===\n";
if (file_exists('assets/images/og-image.jpg')) {
    echo "✓ Open Graph image exists\n";
    $ogImageSize = getimagesize('assets/images/og-image.jpg');
    if ($ogImageSize[0] >= 1200 && $ogImageSize[1] >= 630) {
        echo "✓ Open Graph image dimensions are good: {$ogImageSize[0]}x{$ogImageSize[1]}\n";
    } else {
        echo "✗ Open Graph image dimensions are incorrect: {$ogImageSize[0]}x{$ogImageSize[1]} (should be at least 1200x630)\n";
    }
} else {
    echo "✗ Open Graph image is missing\n";
    echo "  Run create_og_image.php to generate one\n";
}
echo "\n";

// Check for WebP images
echo "=== CHECKING WEBP IMAGES ===\n";
$originalImagesCount = count(array_filter(scandir('assets/images'), function($file) {
    return !is_dir("assets/images/$file") && preg_match('/\.(jpg|jpeg|png)$/i', $file);
}));

if (file_exists('assets/images/webp')) {
    $webpImagesCount = count(array_filter(scandir('assets/images/webp'), function($file) {
        return !is_dir("assets/images/webp/$file") && preg_match('/\.webp$/i', $file);
    }));
    
    echo "Original images: $originalImagesCount\n";
    echo "WebP images: $webpImagesCount\n";
    
    if ($webpImagesCount >= $originalImagesCount) {
        echo "✓ All images have WebP versions\n";
    } else {
        echo "✗ Some images are missing WebP versions\n";
        echo "  Run convert_images.php to generate WebP versions\n";
    }
} else {
    echo "✗ WebP directory doesn't exist\n";
    echo "  Run convert_images.php to generate WebP versions\n";
}
echo "\n";

// Check robots.txt
echo "=== CHECKING ROBOTS.TXT ===\n";
if (file_exists('robots.txt')) {
    $robotsContent = file_get_contents('robots.txt');
    if (strpos($robotsContent, 'Sitemap: https://eli-larson.com/sitemap.xml') !== false) {
        echo "✓ robots.txt references correct sitemap URL\n";
    } else {
        echo "✗ robots.txt doesn't reference the correct sitemap URL\n";
    }
} else {
    echo "✗ robots.txt is missing\n";
}
echo "\n";

// Check for console.log statements in JS
echo "=== CHECKING FOR CONSOLE.LOG STATEMENTS ===\n";
$jsFiles = glob('js/*.js');
$foundConsoleLogs = false;

foreach ($jsFiles as $jsFile) {
    $jsContent = file_get_contents($jsFile);
    if (preg_match('/console\.(log|warn|error|debug|info)/i', $jsContent)) {
        echo "✗ Found console.log statements in $jsFile\n";
        $foundConsoleLogs = true;
    }
}

if (!$foundConsoleLogs) {
    echo "✓ No console.log statements found in JS files\n";
}
echo "\n";

// Check for any PHP files with display_errors enabled
echo "=== CHECKING FOR EXPOSED PHP ERRORS ===\n";
$exposedErrors = false;
$phpFiles = array_merge(glob('*.php'), glob('includes/*.php'));

foreach ($phpFiles as $phpFile) {
    $phpContent = file_get_contents($phpFile);
    if (
        strpos($phpFile, 'error_config.php') === false && // Skip the error config file
        (
            strpos($phpContent, 'display_errors = 1') !== false ||
            strpos($phpContent, 'display_errors=1') !== false ||
            strpos($phpContent, 'ini_set(\'display_errors\', 1)') !== false ||
            strpos($phpContent, 'ini_set("display_errors", 1)') !== false
        )
    ) {
        echo "✗ Found display_errors enabled in $phpFile\n";
        $exposedErrors = true;
    }
}

if (!$exposedErrors) {
    echo "✓ No PHP files with display_errors enabled\n";
}

echo "\n=== ALL CHECKS COMPLETED ===\n";
echo "Checks completed at: " . date('Y-m-d H:i:s') . "\n";

// Get the buffer and display
$output = ob_get_clean();
echo $output;

// Also save to a log file
file_put_contents('pre_launch_check_' . date('Y-m-d_H-i-s') . '.log', $output);
?> 