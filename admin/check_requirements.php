<?php
session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Requirements for image conversion
$requirements = [
    'GD Library' => [
        'required' => true,
        'installed' => extension_loaded('gd'),
        'message' => 'Required for image processing and WebP conversion'
    ],
    'FileInfo Extension' => [
        'required' => true,
        'installed' => extension_loaded('fileinfo'),
        'message' => 'Required for detecting file MIME types'
    ],
    'EXIF Extension' => [
        'required' => false,
        'installed' => extension_loaded('exif'),
        'message' => 'Recommended for preserving image metadata'
    ],
    'WebP Support in GD' => [
        'required' => true,
        'installed' => function_exists('imagewebp'),
        'message' => 'Required for WebP conversion'
    ],
    'PNG Support in GD' => [
        'required' => true,
        'installed' => function_exists('imagecreatefrompng'),
        'message' => 'Required for PNG conversion'
    ],
    'JPEG Support in GD' => [
        'required' => true,
        'installed' => function_exists('imagecreatefromjpeg'),
        'message' => 'Required for JPEG conversion'
    ],
    'File Uploads Enabled' => [
        'required' => true,
        'installed' => ini_get('file_uploads'),
        'message' => 'Required for file uploads'
    ],
    'Upload Max Filesize' => [
        'required' => false,
        'installed' => true,
        'message' => 'Current: ' . ini_get('upload_max_filesize'),
        'value' => ini_get('upload_max_filesize')
    ],
    'Post Max Size' => [
        'required' => false,
        'installed' => true,
        'message' => 'Current: ' . ini_get('post_max_size'),
        'value' => ini_get('post_max_size')
    ]
];

// Check if system meets all required requirements
$allRequirementsMet = true;
foreach ($requirements as $requirement) {
    if ($requirement['required'] && !$requirement['installed']) {
        $allRequirementsMet = false;
        break;
    }
}

// Check directory permissions
$directories = [
    '../assets/images/' => false,
    '../assets/videos/' => false,
    '../assets/video-thumbnails/' => false,
    '../assets/temp/' => false
];

foreach ($directories as $directory => &$writable) {
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }
    $writable = is_writable($directory);
    if (!$writable) {
        $allRequirementsMet = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Requirements - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-check {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .admin-check h1 {
            margin-top: 0;
            margin-bottom: 30px;
            color: hsl(var(--accent-hsl));
            border-bottom: 2px solid hsla(var(--accent-hsl), 0.2);
            padding-bottom: 15px;
        }
        .requirements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .requirements-table th, 
        .requirements-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .requirements-table th {
            background-color: hsla(var(--lightAccent-hsl), 0.2);
            font-weight: bold;
        }
        .requirements-table tr:last-child td {
            border-bottom: none;
        }
        .status {
            font-weight: bold;
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
        }
        .status.warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .admin-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: hsl(var(--accent-hsl));
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .admin-btn:hover {
            background-color: hsl(var(--darkAccent-hsl));
        }
        .system-status {
            margin-bottom: 30px;
            padding: 15px;
            border-radius: 4px;
        }
        .system-status.success {
            background-color: #d4edda;
            color: #155724;
        }
        .system-status.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="admin-check">
        <h1>System Requirements Check</h1>
        
        <div class="system-status <?php echo $allRequirementsMet ? 'success' : 'error'; ?>">
            <?php if ($allRequirementsMet): ?>
                <strong>Your system meets all the requirements for image conversion and uploads.</strong>
            <?php else: ?>
                <strong>Your system does not meet all requirements. Please fix the issues highlighted below.</strong>
            <?php endif; ?>
        </div>
        
        <h2>PHP Extensions and Settings</h2>
        <table class="requirements-table">
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th>Status</th>
                    <th>Info</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $name => $requirement): ?>
                <tr>
                    <td><?php echo $name; ?></td>
                    <td>
                        <?php if ($requirement['installed']): ?>
                            <span class="status success">Installed</span>
                        <?php else: ?>
                            <span class="status <?php echo $requirement['required'] ? 'error' : 'warning'; ?>">
                                <?php echo $requirement['required'] ? 'Missing' : 'Optional'; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $requirement['message']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>Directory Permissions</h2>
        <table class="requirements-table">
            <thead>
                <tr>
                    <th>Directory</th>
                    <th>Status</th>
                    <th>Info</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($directories as $directory => $writable): ?>
                <tr>
                    <td><?php echo $directory; ?></td>
                    <td>
                        <?php if ($writable): ?>
                            <span class="status success">Writable</span>
                        <?php else: ?>
                            <span class="status error">Not Writable</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $writable ? 'Ready for uploads' : 'Permissions need to be updated to allow writing'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="admin-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 