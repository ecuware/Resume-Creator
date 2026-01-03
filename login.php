<?php
// Session security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
session_start();
require_once 'admin_config.php';
require_once 'security_functions.php';

// Redirect to admin panel if already logged in
if (isset($_SESSION[SESSION_NAME]) && $_SESSION[SESSION_NAME] === true) {
    header('Location: admin.php');
    exit;
}

$error = '';
$csrfToken = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($token)) {
        $error = 'Invalid security token. Please refresh the page.';
    } else {
        // Rate limiting check
        $ip = getClientIP();
        $rateLimit = checkRateLimit($ip);
        
        if (!$rateLimit['allowed']) {
            $minutes = ceil($rateLimit['timeLeft'] / 60);
            $error = "Too many failed login attempts. Please try again in {$minutes} minute(s).";
        } else {
            $password = sanitizeInput($_POST['password'] ?? '', 255);
            
            if (verifyPassword($password)) {
                clearFailedLogins($ip);
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION[SESSION_NAME] = true;
                header('Location: admin.php');
                exit;
            } else {
                recordFailedLogin($ip);
                $error = 'Wrong password!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Resume</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Admin Panel</h1>
            <p>Login to edit your resume</p>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required autofocus maxlength="255">
                </div>
                <button type="submit" class="btn-primary">Login</button>
            </form>
            
            <div class="login-footer">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>

