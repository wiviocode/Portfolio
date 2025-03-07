<?php
// Simple CSS test file to diagnose loading issues

// Get server information
$serverInfo = [
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'Script Filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
    'PHP Version' => phpversion(),
];

// Check CSS file
$cssPath = __DIR__ . '/css/style.css';
$cssExists = file_exists($cssPath);
$cssSize = $cssExists ? filesize($cssPath) : 0;
$cssPermissions = $cssExists ? substr(sprintf('%o', fileperms($cssPath)), -4) : 'N/A';
$cssModified = $cssExists ? date('Y-m-d H:i:s', filemtime($cssPath)) : 'N/A';

// Check directory permissions
$cssDir = __DIR__ . '/css';
$cssDirExists = is_dir($cssDir);
$cssDirPermissions = $cssDirExists ? substr(sprintf('%o', fileperms($cssDir)), -4) : 'N/A';
$cssDirReadable = $cssDirExists ? is_readable($cssDir) : false;

// Output as plain text for easy debugging
header('Content-Type: text/plain');

echo "=== CSS TEST RESULTS ===\n\n";

echo "SERVER INFORMATION:\n";
foreach ($serverInfo as $key => $value) {
    echo "- $key: $value\n";
}

echo "\nCSS FILE STATUS:\n";
echo "- CSS File Path: $cssPath\n";
echo "- CSS File Exists: " . ($cssExists ? 'Yes' : 'No') . "\n";
echo "- CSS File Size: " . ($cssSize ? "$cssSize bytes" : 'N/A') . "\n";
echo "- CSS File Permissions: $cssPermissions\n";
echo "- CSS File Last Modified: $cssModified\n";

echo "\nCSS DIRECTORY STATUS:\n";
echo "- CSS Directory Path: $cssDir\n";
echo "- CSS Directory Exists: " . ($cssDirExists ? 'Yes' : 'No') . "\n";
echo "- CSS Directory Permissions: $cssDirPermissions\n";
echo "- CSS Directory Readable: " . ($cssDirReadable ? 'Yes' : 'No') . "\n";

echo "\nFILE LISTING OF CSS DIRECTORY:\n";
if ($cssDirExists && $cssDirReadable) {
    $files = scandir($cssDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $fullPath = "$cssDir/$file";
            $isDir = is_dir($fullPath);
            $size = $isDir ? 'Directory' : filesize($fullPath) . ' bytes';
            $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
            echo "- $file ($size, $perms)\n";
        }
    }
} else {
    echo "Cannot list directory contents.\n";
}

echo "\n=== END OF TEST ===\n";
?> 