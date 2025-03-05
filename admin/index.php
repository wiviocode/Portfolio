<?php
session_start();

// Simple authentication (replace with more secure method in production)
$username = 'admin';
$password = 'adminpass'; // Change this to a secure password!

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Check login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $username && $_POST['password'] === $password) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-login {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .admin-login h1 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            color: hsl(var(--accent-hsl));
        }
        .admin-login form {
            display: flex;
            flex-direction: column;
        }
        .admin-login label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        .admin-login input {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .admin-login button {
            padding: 12px;
            background-color: hsl(var(--accent-hsl));
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .admin-login button:hover {
            background-color: hsl(var(--darkAccent-hsl));
        }
        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="admin-login">
        <h1>Admin Login</h1>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html> 