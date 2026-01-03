<?php
// Admin panel password settings
// Password is automatically hashed and stored here
// You can change password from admin panel -> Change Password tab

// Password hash (automatically updated when password is changed via admin panel)
// Default password: admin123 (CHANGE THIS via admin panel!)
define('ADMIN_PASSWORD_HASH', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

define('SESSION_NAME', 'resume_admin_session');

// Rate limiting settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 300); // 5 minutes in seconds
define('LOGIN_ATTEMPTS_FILE', __DIR__ . '/.login_attempts.json');

// CSRF token settings
define('CSRF_TOKEN_NAME', 'csrf_token');
?>
