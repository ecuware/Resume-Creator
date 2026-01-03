<?php
// Security helper functions

/**
 * Verify password
 */
function verifyPassword($password, $hash = null) {
    if (defined('ADMIN_PASSWORD_HASH')) {
        return password_verify($password, ADMIN_PASSWORD_HASH);
    } elseif (defined('ADMIN_PASSWORD')) {
        return $password === ADMIN_PASSWORD;
    }
    return false;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Check rate limiting for login
 */
function checkRateLimit($ip) {
    $attemptsFile = LOGIN_ATTEMPTS_FILE;
    $attempts = [];
    
    if (file_exists($attemptsFile)) {
        $attempts = json_decode(file_get_contents($attemptsFile), true) ?? [];
    }
    
    // Clean old entries
    $currentTime = time();
    foreach ($attempts as $key => $data) {
        if ($currentTime - $data['time'] > LOGIN_LOCKOUT_TIME) {
            unset($attempts[$key]);
        }
    }
    
    // Check current IP
    if (isset($attempts[$ip])) {
        $data = $attempts[$ip];
        if ($data['count'] >= MAX_LOGIN_ATTEMPTS) {
            $timeLeft = LOGIN_LOCKOUT_TIME - ($currentTime - $data['time']);
            if ($timeLeft > 0) {
                return [
                    'allowed' => false,
                    'timeLeft' => $timeLeft,
                    'attempts' => $data['count']
                ];
            } else {
                // Lockout expired, reset
                unset($attempts[$ip]);
            }
        }
    }
    
    return ['allowed' => true, 'attempts' => $attempts[$ip]['count'] ?? 0];
}

/**
 * Record failed login attempt
 */
function recordFailedLogin($ip) {
    $attemptsFile = LOGIN_ATTEMPTS_FILE;
    $attempts = [];
    
    if (file_exists($attemptsFile)) {
        $attempts = json_decode(file_get_contents($attemptsFile), true) ?? [];
    }
    
    if (!isset($attempts[$ip])) {
        $attempts[$ip] = ['count' => 0, 'time' => time()];
    }
    
    $attempts[$ip]['count']++;
    $attempts[$ip]['time'] = time();
    
    file_put_contents($attemptsFile, json_encode($attempts));
}

/**
 * Clear failed login attempts for IP
 */
function clearFailedLogins($ip) {
    $attemptsFile = LOGIN_ATTEMPTS_FILE;
    $attempts = [];
    
    if (file_exists($attemptsFile)) {
        $attempts = json_decode(file_get_contents($attemptsFile), true) ?? [];
    }
    
    unset($attempts[$ip]);
    file_put_contents($attemptsFile, json_encode($attempts));
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Sanitize input
 */
function sanitizeInput($input, $maxLength = 10000) {
    if (is_array($input)) {
        return array_map(function($item) use ($maxLength) {
            return sanitizeInput($item, $maxLength);
        }, $input);
    }
    
    $input = trim($input);
    if (strlen($input) > $maxLength) {
        $input = substr($input, 0, $maxLength);
    }
    return $input;
}

/**
 * Validate URL
 */
function validateURL($url) {
    if (empty($url)) {
        return true; // Optional field
    }
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Safe error message (no sensitive info)
 */
function safeError($message) {
    // In production, log detailed errors but show generic message
    error_log("Resume System Error: " . $message);
    return "An error occurred. Please try again later.";
}

/**
 * Sanitize resume data recursively
 */
function sanitizeResumeData($data, $maxDepth = 10) {
    if ($maxDepth <= 0) {
        return '';
    }
    
    if (is_array($data)) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitizedKey = sanitizeInput($key, 100);
            $sanitized[$sanitizedKey] = sanitizeResumeData($value, $maxDepth - 1);
        }
        return $sanitized;
    } elseif (is_string($data)) {
        // Limit string length based on context
        $maxLength = 10000; // Default max length
        return sanitizeInput($data, $maxLength);
    } else {
        return $data;
    }
}
?>

