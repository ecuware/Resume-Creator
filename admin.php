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

// Generate CSRF token for this session
$csrfToken = generateCSRFToken();

// Giriş kontrolü
if (!isset($_SESSION[SESSION_NAME]) || $_SESSION[SESSION_NAME] !== true) {
    header('Location: login.php');
    exit;
}

// Çıkış işlemi
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Resume JSON dosyasını yükle
$resumeJson = file_get_contents(__DIR__ . '/src/resume.json');
$resume = json_decode($resumeJson, true);

if ($resume === null) {
    $error = 'Unable to read JSON file!';
    $resume = ['header' => ['name' => '', 'subtitle' => '', 'nav' => []], 'left' => [], 'right' => []];
}

// Aktif tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'content';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Resume</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h1><i class="fa fa-briefcase"></i> Resume Admin</h1>
            </div>
            
            <nav class="sidebar-nav">
                <a href="?tab=content" class="nav-item <?php echo $activeTab === 'content' ? 'active' : ''; ?>">
                    <i class="fa fa-edit"></i>
                    <span>Content Management</span>
                </a>
                <a href="?tab=json" class="nav-item <?php echo $activeTab === 'json' ? 'active' : ''; ?>">
                    <i class="fa fa-code"></i>
                    <span>JSON Editor</span>
                </a>
                <a href="?tab=password" class="nav-item <?php echo $activeTab === 'password' ? 'active' : ''; ?>">
                    <i class="fa fa-key"></i>
                    <span>Change Password</span>
                </a>
                <a href="index.php" target="_blank" class="nav-item">
                    <i class="fa fa-eye"></i>
                    <span>Preview</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="?logout=1" class="nav-item logout">
                    <i class="fa fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-topbar">
                <h2>
                    <?php 
                    if ($activeTab === 'content') echo '<i class="fa fa-edit"></i> Content Management';
                    elseif ($activeTab === 'json') echo '<i class="fa fa-code"></i> JSON Editor';
                    elseif ($activeTab === 'password') echo '<i class="fa fa-key"></i> Change Password';
                    ?>
                </h2>
            </div>

            <div class="admin-content-area">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-success" id="successMessage" style="display: none;">
                    <i class="fa fa-check-circle"></i> <span id="successText">Changes saved successfully!</span>
                </div>

                <?php if ($activeTab === 'content'): ?>
                    <!-- Content Management Tab -->
                    <?php include 'admin_content.php'; ?>
                <?php elseif ($activeTab === 'password'): ?>
                    <!-- Password Change Tab -->
                    <?php include 'admin_password.php'; ?>
                <?php elseif ($activeTab === 'json'): ?>
                    <!-- JSON Editör Tab -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fa fa-file-code"></i> JSON Editor</h3>
                            <p class="text-muted">Edit directly in JSON format</p>
                        </div>
                        <div class="card-body">
                            <textarea id="jsonEditor" class="json-editor"><?php echo htmlspecialchars(json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></textarea>
                        </div>
                        <div class="card-footer">
                            <button id="saveBtn" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save
                            </button>
                            <button id="validateBtn" class="btn btn-secondary">
                                <i class="fa fa-check"></i> Validate
                            </button>
                            <span id="statusText" class="status-text"></span>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3><i class="fa fa-question-circle"></i> Help</h3>
                        </div>
                        <div class="card-body">
                            <ul class="help-list">
                                <li><strong>Bold text:</strong> Write text as <code>*bold*</code></li>
                                <li><strong>JSON format:</strong> Pay attention to quotes and commas</li>
                                <li><strong>Saving:</strong> Click "Save" button or changes will be saved automatically</li>
                                <li><strong>Preview:</strong> Use "Preview" option from the left menu</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // CSRF token for AJAX requests
        const CSRF_TOKEN = '<?php echo htmlspecialchars($csrfToken); ?>';
        
        // Global success message function
        function showSuccess(message) {
            const alert = document.getElementById('successMessage');
            if (alert) {
                const textEl = document.getElementById('successText');
                if (textEl) textEl.textContent = message;
                alert.style.display = 'flex';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 3000);
            } else {
                alert(message);
            }
        }
    </script>
    <script src="assets/js/admin.js"></script>
    <?php if ($activeTab === 'content'): ?>
        <script src="assets/js/admin_content.js"></script>
    <?php endif; ?>
</body>
</html>
