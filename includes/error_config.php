<?php
// Error handling configuration
ini_set('display_startup_errors', 0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);
ini_set('docref_root', 0);
ini_set('docref_ext', 0);

// In development, you might want to enable errors
// Uncomment this section during development
/*
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1' || $_SERVER['SERVER_ADDR'] === '::1') {
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    ini_set('html_errors', 1);
    error_reporting(E_ALL);
}
*/
?> 