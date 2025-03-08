<?php
session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-dashboard {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .admin-dashboard h1 {
            margin-top: 0;
            margin-bottom: 30px;
            color: hsl(var(--accent-hsl));
            border-bottom: 2px solid hsla(var(--accent-hsl), 0.2);
            padding-bottom: 15px;
        }
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .admin-card {
            background-color: hsla(var(--lightAccent-hsl), 0.3);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        .admin-card h2 {
            margin-top: 0;
            color: hsl(var(--accent-hsl));
            font-size: 1.4rem;
        }
        .admin-card p {
            margin-bottom: 20px;
            color: #666;
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
        .admin-btn.logout {
            background-color: #e74c3c;
            margin-top: 20px;
        }
        .admin-btn.logout:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>
        
        <div class="admin-menu">
            <div class="admin-card">
                <h2>Photography</h2>
                <p>Manage the order of images in your photography portfolio.</p>
                <a href="sort_images.php" class="admin-btn">Manage Images</a>
            </div>
            
            <div class="admin-card">
                <h2>Videography</h2>
                <p>Manage the order of videos in your videography portfolio.</p>
                <a href="sort_videos.php" class="admin-btn">Manage Videos</a>
            </div>
            
            <div class="admin-card">
                <h2>Video Titles</h2>
                <p>Customize the titles of videos in your videography portfolio.</p>
                <a href="edit_video_titles.php" class="admin-btn">Edit Video Titles</a>
            </div>
            
            <div class="admin-card">
                <h2>Upload Files</h2>
                <p>Upload new images and videos to your portfolio.</p>
                <a href="upload.php" class="admin-btn">Upload Files</a>
            </div>
            
            <div class="admin-card">
                <h2>Video Thumbnails</h2>
                <p>Generate thumbnails for videos that don't have one.</p>
                <a href="generate_thumbnails.php" class="admin-btn">Generate Thumbnails</a>
            </div>
            
            <div class="admin-card">
                <h2>Image Descriptions</h2>
                <p>Add hover tooltips with descriptions for your photography.</p>
                <a href="edit_descriptions.php" class="admin-btn">Edit Descriptions</a>
            </div>
        </div>
        
        <div style="text-align: center;">
            <a href="check_requirements.php" class="admin-btn" style="margin-right: 10px; background-color: #3498db;">Check System Requirements</a>
            <a href="?logout=1" class="admin-btn logout">Logout</a>
        </div>
    </div>
</body>
</html> 