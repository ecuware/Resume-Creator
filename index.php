<?php
// Resume JSON dosyasını yükle
$resumeJson = file_get_contents(__DIR__ . '/src/resume.json');
$resume = json_decode($resumeJson, true);

// Helper fonksiyonlar
function getIconClass($icon) {
    $icons = [
        'pin' => 'fa fa-location-dot',
        'envelope' => 'fa fa-envelope',
        'github' => 'fab fa-github',
        'linkedin' => 'fab fa-linkedin'
    ];
    return $icons[$icon] ?? '';
}

function parseFormatted($text) {
    $segments = [];
    $current = '';
    $bold = false;
    
    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];
        
        if ($char === '*') {
            if (strlen($current) > 0) {
                $segments[] = ['style' => $bold ? 'bold' : 'normal', 'value' => $current];
                $current = '';
            }
            $bold = !$bold;
            continue;
        }
        
        $current .= $char;
        
        if ($i === strlen($text) - 1 && strlen($current) > 0) {
            $segments[] = ['style' => $bold ? 'bold' : 'normal', 'value' => $current];
        }
    }
    
    return $segments;
}

function renderFormattedSegments($text) {
    $segments = parseFormatted($text);
    $output = '';
    foreach ($segments as $segment) {
        if ($segment['style'] === 'bold') {
            $output .= '<b>' . htmlspecialchars($segment['value']) . '</b>';
        } else {
            $output .= htmlspecialchars($segment['value']);
        }
    }
    return $output;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume - <?php echo htmlspecialchars($resume['header']['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page-container">
        <div class="page-content" id="pageContent">
            <header id="header">
                <h1><?php echo htmlspecialchars($resume['header']['name']); ?></h1>
                <h2><?php echo htmlspecialchars($resume['header']['subtitle']); ?></h2>
                <nav>
                    <?php foreach ($resume['header']['nav'] as $nav): ?>
                        <?php if (!empty($nav['href'])): ?>
                            <a href="<?php echo htmlspecialchars($nav['href']); ?>">
                                <i class="<?php echo getIconClass($nav['icon']); ?> icon"></i>
                                <?php echo htmlspecialchars($nav['label']); ?>
                            </a>
                        <?php else: ?>
                            <span>
                                <i class="<?php echo getIconClass($nav['icon']); ?> icon"></i>
                                <?php echo htmlspecialchars($nav['label']); ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            </header>

            <main>
                <div class="left">
                    <?php foreach ($resume['left'] as $section): ?>
                        <section>
                            <h1><?php echo htmlspecialchars($section['name']); ?></h1>
                            <?php foreach ($section['items'] as $item): ?>
                                <div class="grouped-content">
                                    <header>
                                        <div class="info-left">
                                            <h1>
                                                <?php if (!empty($item['href'])): ?>
                                                    <a href="<?php echo htmlspecialchars($item['href']); ?>">
                                                        <?php echo htmlspecialchars($item['title']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                <?php endif; ?>
                                            </h1>
                                            <?php if (!empty($item['subtitle'])): ?>
                                                <h2><?php echo htmlspecialchars($item['subtitle']); ?></h2>
                                            <?php endif; ?>
                                        </div>
                                        <div class="info-right">
                                            <?php if (!empty($item['upper'])): ?>
                                                <div class="extra"><?php echo htmlspecialchars($item['upper']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['lower'])): ?>
                                                <div class="extra"><?php echo htmlspecialchars($item['lower']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </header>
                                    <?php if (!empty($item['bullets'])): ?>
                                        <ul>
                                            <?php foreach ($item['bullets'] as $bullet): ?>
                                                <li><?php echo renderFormattedSegments($bullet); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endforeach; ?>
                </div>

                <div class="right">
                    <?php foreach ($resume['right'] as $section): ?>
                        <section>
                            <h1><?php echo htmlspecialchars($section['name']); ?></h1>
                            <?php foreach ($section['items'] as $item): ?>
                                <div class="grouped-content">
                                    <header>
                                        <div class="info-left">
                                            <h1>
                                                <?php if (!empty($item['href'])): ?>
                                                    <a href="<?php echo htmlspecialchars($item['href']); ?>">
                                                        <?php echo htmlspecialchars($item['title']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                <?php endif; ?>
                                            </h1>
                                            <?php if (!empty($item['subtitle'])): ?>
                                                <h2><?php echo htmlspecialchars($item['subtitle']); ?></h2>
                                            <?php endif; ?>
                                        </div>
                                        <div class="info-right">
                                            <?php if (!empty($item['upper'])): ?>
                                                <div class="extra"><?php echo htmlspecialchars($item['upper']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['lower'])): ?>
                                                <div class="extra"><?php echo htmlspecialchars($item['lower']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </header>
                                    <?php if (!empty($item['bullets'])): ?>
                                        <ul>
                                            <?php foreach ($item['bullets'] as $bullet): ?>
                                                <li><?php echo renderFormattedSegments($bullet); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

