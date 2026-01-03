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
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Resume JSON dosyasını yükle
$jsonFile = __DIR__ . '/src/resume.json';
$resume = json_decode(file_get_contents($jsonFile), true);

if ($resume === null) {
    $resume = ['header' => ['name' => '', 'subtitle' => '', 'nav' => []], 'left' => [], 'right' => []];
}

// GET istekleri
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'getSection') {
        $index = intval($_GET['index'] ?? -1);
        $side = $_GET['side'] ?? '';
        
        if ($index >= 0 && isset($resume[$side][$index])) {
            echo json_encode(['success' => true, 'section' => $resume[$side][$index]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Section not found']);
        }
        exit;
    }
    
    if ($action === 'getItem') {
        $sectionIndex = intval($_GET['sectionIndex'] ?? -1);
        $itemIndex = intval($_GET['itemIndex'] ?? -1);
        $side = $_GET['side'] ?? '';
        
        if ($sectionIndex >= 0 && $itemIndex >= 0 && isset($resume[$side][$sectionIndex]['items'][$itemIndex])) {
            echo json_encode(['success' => true, 'item' => $resume[$side][$sectionIndex]['items'][$itemIndex]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Item not found']);
        }
        exit;
    }
}

// POST istekleri
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => safeError('Invalid request')]);
    exit;
}

// CSRF token verification for POST requests
if (isset($input['csrf_token'])) {
    if (!verifyCSRFToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['error' => safeError('Invalid security token')]);
        exit;
    }
}

$action = sanitizeInput($input['action'] ?? '', 50);

// Header Kaydetme
if ($action === 'saveHeader') {
    $name = sanitizeInput($input['name'] ?? '', 200);
    $subtitle = sanitizeInput($input['subtitle'] ?? '', 200);
    
    if (empty($name) || empty($subtitle)) {
        echo json_encode(['success' => false, 'error' => safeError('Name and subtitle are required')]);
        exit;
    }
    
    $resume['header']['name'] = $name;
    $resume['header']['subtitle'] = $subtitle;
    
    if (saveResume($resume, $jsonFile)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save']);
    }
    exit;
}

// Nav Kaydetme
if ($action === 'saveNav') {
    $nav = $input['nav'] ?? [];
    if (!is_array($nav)) {
        echo json_encode(['success' => false, 'error' => safeError('Invalid navigation data')]);
        exit;
    }
    
    // Sanitize nav items
    $sanitizedNav = [];
    foreach ($nav as $item) {
        if (!is_array($item)) continue;
        $sanitizedItem = [
            'label' => sanitizeInput($item['label'] ?? '', 200),
            'icon' => sanitizeInput($item['icon'] ?? '', 50)
        ];
        if (!empty($item['href'])) {
            $href = sanitizeInput($item['href'], 500);
            if (validateURL($href)) {
                $sanitizedItem['href'] = $href;
            }
        }
        if (!empty($sanitizedItem['label']) && !empty($sanitizedItem['icon'])) {
            $sanitizedNav[] = $sanitizedItem;
        }
    }
    
    $resume['header']['nav'] = $sanitizedNav;
    
    if (saveResume($resume, $jsonFile)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save']);
    }
    exit;
}

// Section Kaydetme
if ($action === 'saveSection') {
    $index = isset($input['index']) ? intval($input['index']) : null;
    $side = sanitizeInput($input['side'] ?? '', 10);
    $name = sanitizeInput($input['name'] ?? '', 200);
    
    if (empty($name) || !in_array($side, ['left', 'right'])) {
        echo json_encode(['success' => false, 'error' => safeError('Invalid data')]);
        exit;
    }
    
    if ($index === null) {
        // Yeni section ekle
        if (!isset($resume[$side])) {
            $resume[$side] = [];
        }
        $resume[$side][] = ['name' => $name, 'items' => []];
    } else {
        // Mevcut section'ı güncelle
        if (isset($resume[$side][$index])) {
            $resume[$side][$index]['name'] = $name;
        } else {
            echo json_encode(['success' => false, 'error' => 'Section not found']);
            exit;
        }
    }
    
    if (saveResume($resume, $jsonFile)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => safeError('Failed to save')]);
    }
    exit;
}

// Section Silme
if ($action === 'deleteSection') {
    $index = intval($input['index'] ?? -1);
    $side = sanitizeInput($input['side'] ?? '', 10);
    
    if ($index >= 0 && in_array($side, ['left', 'right']) && isset($resume[$side][$index])) {
        array_splice($resume[$side], $index, 1);
        
        if (saveResume($resume, $jsonFile)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => safeError('Failed to save')]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => safeError('Section not found')]);
    }
    exit;
}

// Item Kaydetme
if ($action === 'saveItem') {
    $sectionIndex = intval($input['sectionIndex'] ?? -1);
    $itemIndex = isset($input['itemIndex']) ? intval($input['itemIndex']) : null;
    $side = sanitizeInput($input['side'] ?? '', 10);
    $item = $input['item'] ?? [];
    
    if (!is_array($item)) {
        echo json_encode(['success' => false, 'error' => safeError('Invalid item data')]);
        exit;
    }
    
    $title = sanitizeInput($item['title'] ?? '', 500);
    
    if ($sectionIndex < 0 || !in_array($side, ['left', 'right']) || empty($title)) {
        echo json_encode(['success' => false, 'error' => safeError('Invalid data')]);
        exit;
    }
    
    if (!isset($resume[$side][$sectionIndex])) {
        echo json_encode(['success' => false, 'error' => safeError('Section not found')]);
        exit;
    }
    
    // Sanitize and validate item data
    $cleanItem = ['title' => $title];
    if (!empty($item['subtitle'])) {
        $cleanItem['subtitle'] = sanitizeInput($item['subtitle'], 500);
    }
    if (!empty($item['href'])) {
        $href = sanitizeInput($item['href'], 500);
        if (validateURL($href)) {
            $cleanItem['href'] = $href;
        }
    }
    if (!empty($item['upper'])) {
        $cleanItem['upper'] = sanitizeInput($item['upper'], 200);
    }
    if (!empty($item['lower'])) {
        $cleanItem['lower'] = sanitizeInput($item['lower'], 200);
    }
    if (!empty($item['bullets']) && is_array($item['bullets'])) {
        $cleanBullets = [];
        foreach ($item['bullets'] as $bullet) {
            $cleanBullets[] = sanitizeInput($bullet, 1000);
        }
        $cleanItem['bullets'] = $cleanBullets;
    }
    
    if ($itemIndex === null) {
        // Yeni item ekle
        if (!isset($resume[$side][$sectionIndex]['items'])) {
            $resume[$side][$sectionIndex]['items'] = [];
        }
        $resume[$side][$sectionIndex]['items'][] = $cleanItem;
    } else {
        // Mevcut item'ı güncelle
        if (isset($resume[$side][$sectionIndex]['items'][$itemIndex])) {
            $resume[$side][$sectionIndex]['items'][$itemIndex] = $cleanItem;
        } else {
            echo json_encode(['success' => false, 'error' => 'Item not found']);
            exit;
        }
    }
    
    if (saveResume($resume, $jsonFile)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => safeError('Failed to save')]);
    }
    exit;
}

// Item Silme
if ($action === 'deleteItem') {
    $sectionIndex = intval($input['sectionIndex'] ?? -1);
    $itemIndex = intval($input['itemIndex'] ?? -1);
    $side = sanitizeInput($input['side'] ?? '', 10);
    
    if ($sectionIndex >= 0 && $itemIndex >= 0 && in_array($side, ['left', 'right']) && isset($resume[$side][$sectionIndex]['items'][$itemIndex])) {
        array_splice($resume[$side][$sectionIndex]['items'], $itemIndex, 1);
        
        if (saveResume($resume, $jsonFile)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => safeError('Failed to save')]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => safeError('Item not found')]);
    }
    exit;
}

// Helper: Resume kaydetme
function saveResume($resume, $file) {
    $json = json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file, $json) !== false;
}

echo json_encode(['success' => false, 'error' => safeError('Unknown action')]);
?>


