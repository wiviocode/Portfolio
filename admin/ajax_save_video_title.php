<?php
// Include authentication
include_once 'includes/auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the data from POST
$filename = $_POST['filename'] ?? '';
$title = $_POST['title'] ?? '';

// Validate input
if (empty($filename)) {
    echo json_encode(['success' => false, 'message' => 'Filename is required']);
    exit;
}

// Definitions
$titlesDataFile = "../data/video_titles.json";

// Load current titles
$currentData = [];
if (file_exists($titlesDataFile)) {
    $jsonContent = file_get_contents($titlesDataFile);
    $currentData = json_decode($jsonContent, true) ?: [];
}

// Update the title
if (trim($title) !== '') {
    $currentData[$filename] = $title;
} else {
    // Remove title if empty
    if (isset($currentData[$filename])) {
        unset($currentData[$filename]);
    }
}

// Save back to file
if (file_put_contents($titlesDataFile, json_encode($currentData, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true, 'message' => 'Title saved']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error saving title']);
}