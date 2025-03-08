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
$description = $_POST['description'] ?? '';

// Validate input
if (empty($filename)) {
    echo json_encode(['success' => false, 'message' => 'Filename is required']);
    exit;
}

// Definitions
$descriptionsFile = "../data/image_descriptions.json";

// Load current descriptions
$currentData = [];
if (file_exists($descriptionsFile)) {
    $jsonContent = file_get_contents($descriptionsFile);
    $currentData = json_decode($jsonContent, true) ?: [];
}

// Update the description
if (trim($description) !== '') {
    $currentData[$filename] = $description;
} else {
    // Remove description if empty
    if (isset($currentData[$filename])) {
        unset($currentData[$filename]);
    }
}

// Save back to file
if (file_put_contents($descriptionsFile, json_encode($currentData, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true, 'message' => 'Description saved']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error saving description']);
}