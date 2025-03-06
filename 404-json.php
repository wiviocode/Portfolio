<?php
// Set headers for JSON response
header('Content-Type: application/json');
http_response_code(404);

// Return JSON error response
echo json_encode([
    'error' => true,
    'code' => 404,
    'message' => 'Resource not found',
    'timestamp' => date('c')
]);
?> 