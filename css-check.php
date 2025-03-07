<?php
// Helper function to get the base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol . $domainName;
}

$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Diagnostic Test</title>
    
    <!-- Try loading CSS directly from absolute URL -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/style.css">
    
    <!-- Inline essential styles as a fallback -->
    <style>
        body {
            font-family: 'Newsreader', serif;
            line-height: 1.6;
            color: #333;
            background-color: #FAFAFA;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: white;
        }
        .button-test {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .cta-button {
            display: inline-block;
            padding: 14px 28px;
            font-size: 1.1rem;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
            text-align: center;
        }
        .cta-button.primary {
            background-color: #446084;
            color: white;
        }
        .cta-button.secondary {
            background-color: transparent;
            color: #333;
            border: 1px solid rgba(51, 51, 51, 0.2);
        }
        .css-info {
            margin-top: 30px;
            font-family: monospace;
            background-color: #f5f5f5;
            padding: 20px;
            white-space: pre-wrap;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>CSS Diagnostic Test</h1>
        
        <div class="test-section">
            <h2>CSS Loading Test</h2>
            <p>This section tests if the external CSS file is loading properly.</p>
            <p>If you see styling from the external CSS, the text below should have proper styling:</p>
            
            <div class="hero-content">
                <h2>This should be styled by external CSS</h2>
                <p>This paragraph should have styling from the external CSS file.</p>
            </div>
            
            <div class="button-test">
                <a href="#" class="cta-button primary">Primary Button</a>
                <a href="#" class="cta-button secondary">Secondary Button</a>
            </div>
        </div>
        
        <div class="test-section">
            <h2>Path Information</h2>
            <div class="css-info">
Base URL: <?php echo $baseUrl; ?>
Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?>
CSS Path: <?php echo $_SERVER['DOCUMENT_ROOT'] . '/css/style.css'; ?>
CSS File Exists: <?php echo file_exists($_SERVER['DOCUMENT_ROOT'] . '/css/style.css') ? 'Yes' : 'No'; ?>
<?php if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/css/style.css')): ?>
CSS File Size: <?php echo filesize($_SERVER['DOCUMENT_ROOT'] . '/css/style.css'); ?> bytes
CSS File Permissions: <?php echo substr(sprintf('%o', fileperms($_SERVER['DOCUMENT_ROOT'] . '/css/style.css')), -4); ?>
<?php endif; ?>

CSS Include Path: <?php echo $baseUrl; ?>/css/style.css
            </div>
        </div>
        
        <div class="test-section">
            <h2>CSS Rules Test</h2>
            <p>The following elements test specific CSS rules:</p>
            
            <div class="featured-card">
                <div class="card-content">
                    <h3>Featured Card Heading</h3>
                    <p>This should have styling from the featured-card class.</p>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <div class="portfolio-item">
                    <img src="<?php echo $baseUrl; ?>/assets/images/<?php 
                    // Get first image from directory if available
                    $imgDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images';
                    $imgFile = '';
                    if (is_dir($imgDir)) {
                        $files = scandir($imgDir);
                        foreach($files as $file) {
                            if(preg_match("/\.(jpg|jpeg|png)$/i", $file)) {
                                $imgFile = $file;
                                break;
                            }
                        }
                    }
                    echo $imgFile;
                    ?>" alt="Test image" style="max-width: 300px; height: auto;">
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if external CSS is applied
        const hero = document.querySelector('.hero-content');
        const computed = window.getComputedStyle(hero);
        
        // Add info about applied styles
        const cssInfo = document.createElement('div');
        cssInfo.className = 'css-info';
        cssInfo.innerHTML = `
Applied Styles for .hero-content:
- Font Size: ${computed.fontSize}
- Color: ${computed.color}
- Background: ${computed.backgroundColor}
- Padding: ${computed.padding}
        `;
        
        document.querySelector('.test-section').appendChild(cssInfo);
    });
    </script>
</body>
</html> 