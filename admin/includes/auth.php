<?php
session_start();

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

// Handle saving order data to JSON file
function save_order_data($data, $filename) {
    // Ensure data directory exists
    $dataDir = __DIR__ . '/../../data';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Write JSON data to file
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    $filePath = $dataDir . '/' . $filename;
    
    return file_put_contents($filePath, $jsonData) !== false;
}

// Load order data from JSON file
function load_order_data($filename) {
    $filePath = __DIR__ . '/../../data/' . $filename;
    
    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true);
    }
    
    return [];
}
?> 