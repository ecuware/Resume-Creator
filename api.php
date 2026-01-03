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
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => safeError('Invalid JSON format')]);
    exit;
}

// Validasyon fonksiyonları
function validateHeader($header) {
    if (empty($header['name']) || empty($header['subtitle']) || empty($header['nav'])) {
        return false;
    }
    foreach ($header['nav'] as $nav) {
        if (empty($nav['label']) || empty($nav['icon'])) {
            return false;
        }
    }
    return true;
}

function validateSectionItems($items) {
    foreach ($items as $item) {
        if (empty($item['title'])) {
            return false;
        }
    }
    return true;
}

function validateSections($sections) {
    foreach ($sections as $section) {
        if (empty($section['name']) || empty($section['items'])) {
            return false;
        }
        if (!validateSectionItems($section['items'])) {
            return false;
        }
    }
    return true;
}

function validateResume($resume) {
    if (empty($resume['header']) || empty($resume['left']) || empty($resume['right'])) {
        return false;
    }
    if (!validateHeader($resume['header'])) {
        return false;
    }
    if (!validateSections($resume['left']) || !validateSections($resume['right'])) {
        return false;
    }
    return true;
}

if (!validateResume($data)) {
    http_response_code(400);
    echo json_encode(['error' => safeError('Invalid resume structure')]);
    exit;
}

// Sanitize data before saving
$data = sanitizeResumeData($data);

// JSON dosyasını kaydet
$jsonFile = __DIR__ . '/src/resume.json';
$result = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => safeError('Failed to save file')]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Resume updated successfully']);
?>

