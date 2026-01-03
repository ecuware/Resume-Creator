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

// Authentication check
if (!isset($_SESSION[SESSION_NAME]) || $_SESSION[SESSION_NAME] !== true) {
    http_response_code(401);
    echo json_encode(['error' => safeError('Unauthorized')]);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => safeError('Method not allowed')]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => safeError('Invalid request')]);
    exit;
}

// CSRF token verification
if (isset($input['csrf_token'])) {
    if (!verifyCSRFToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['error' => safeError('Invalid security token')]);
        exit;
    }
}

$action = sanitizeInput($input['action'] ?? '', 50);

if ($action === 'changePassword') {
    $currentPassword = sanitizeInput($input['current_password'] ?? '', 255);
    $newPassword = sanitizeInput($input['new_password'] ?? '', 255);
    
    // Validate current password
    if (!verifyPassword($currentPassword)) {
        echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
        exit;
    }
    
    // Validate new password
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'error' => 'New password must be at least 8 characters long']);
        exit;
    }
    
    if ($newPassword === $currentPassword) {
        echo json_encode(['success' => false, 'error' => 'New password must be different from current password']);
        exit;
    }
    
    // Generate new password hash
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Read current config file
    $configFile = __DIR__ . '/admin_config.php';
    $configContent = file_get_contents($configFile);
    
    if ($configContent === false) {
        echo json_encode(['success' => false, 'error' => safeError('Unable to read config file')]);
        exit;
    }
    
    // Replace password hash
    // Pattern: define('ADMIN_PASSWORD_HASH', '...'); or define("ADMIN_PASSWORD_HASH", "...");
    $pattern = "/define\s*\(\s*['\"]ADMIN_PASSWORD_HASH['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/";
    
    if (preg_match($pattern, $configContent)) {
        // Replace existing hash
        $newConfig = preg_replace(
            $pattern,
            "define('ADMIN_PASSWORD_HASH', '" . addslashes($newHash) . "')",
            $configContent
        );
    } else {
        // If hash doesn't exist, add it before SESSION_NAME
        $newConfig = preg_replace(
            "/(define\s*\(\s*['\"]SESSION_NAME)/",
            "define('ADMIN_PASSWORD_HASH', '" . addslashes($newHash) . "');\n\n$1",
            $configContent
        );
    }
    
    // Write updated config
    $result = file_put_contents($configFile, $newConfig);
    
    if ($result === false) {
        echo json_encode(['success' => false, 'error' => safeError('Failed to update password')]);
        exit;
    }
    
    // Clear failed login attempts for this session
    $ip = getClientIP();
    clearFailedLogins($ip);
    
    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    exit;
}

echo json_encode(['success' => false, 'error' => safeError('Unknown action')]);
?>

