<?php
echo "<h1>🔧 تعمیر خودکار گاردین</h1>";

$guardianPath = __DIR__ . '/ai-web-guardian';

// ============================================
// 1. ساخت پوشه‌ها
// ============================================
$dirs = ['config', 'data', 'data/cache', 'data/rules', 'data/ml_models', 'logs', 'core', 'admin'];
foreach ($dirs as $dir) {
    $path = $guardianPath . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "✅ پوشه ساخته شد: {$dir}<br>";
    }
}

// ============================================
// 2. ساخت فایل config.json
// ============================================
$configFile = $guardianPath . '/config/config.json';
$config = [
    'version' => '6.0.0',
    'installed_at' => date('Y-m-d H:i:s'),
    'environment' => 'production',
    'protection' => [
        'level' => 'high',
        'block_threshold' => 0.70,
        'auto_block_attackers' => true,
        'block_duration' => 3600,
    ],
    'rate_limit' => [
        'enabled' => true,
        'requests_per_minute' => 120,
        'max_violations' => 3,
    ],
    'ai' => [
        'enabled' => true,
        'learning_enabled' => true,
    ],
    'csp' => ['enabled' => true],
    'cookie_protection' => ['enabled' => true],
];
file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "✅ config.json ساخته شد<br>";

// ============================================
// 3. ساخت فایل admin.json
// ============================================
$adminFile = $guardianPath . '/config/admin.json';
$admin = [
    'username' => 'admin',
    'email' => 'admin@guardian.local',
    'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
    'created_at' => date('Y-m-d H:i:s'),
    'last_login' => null,
];
file_put_contents($adminFile, json_encode($admin, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "✅ admin.json ساخته شد<br>";

// ============================================
// 4. ساخت دیتابیس و جداول
// ============================================
$dbPath = $guardianPath . '/data/guardian.db';
try {
    // حذف دیتابیس قدیمی اگر وجود داره
    if (file_exists($dbPath)) {
        unlink($dbPath);
        echo "🔄 دیتابیس قدیمی حذف شد<br>";
    }

    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // جدول requests
    $pdo->exec("CREATE TABLE requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT NOT NULL,
        method TEXT,
        uri TEXT,
        user_agent TEXT,
        score REAL DEFAULT 0,
        action TEXT DEFAULT 'allow',
        attacks TEXT,
        details TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ جدول requests ساخته شد<br>";

    // جدول attacks
    $pdo->exec("CREATE TABLE attacks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT NOT NULL,
        type TEXT,
        severity REAL DEFAULT 0,
        payload TEXT,
        country TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ جدول attacks ساخته شد<br>";

    // جدول blocked_ips
    $pdo->exec("CREATE TABLE blocked_ips (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT UNIQUE NOT NULL,
        reason TEXT,
        blocked_until DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ جدول blocked_ips ساخته شد<br>";

    // جدول whitelist
    $pdo->exec("CREATE TABLE whitelist (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT UNIQUE NOT NULL,
        reason TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ جدول whitelist ساخته شد<br>";

    // جدول custom_rules
    $pdo->exec("CREATE TABLE custom_rules (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        pattern TEXT NOT NULL,
        severity REAL DEFAULT 0.7,
        enabled INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ جدول custom_rules ساخته شد<br>";

    // ایندکس‌ها
    $pdo->exec("CREATE INDEX idx_requests_ip ON requests(ip)");
    $pdo->exec("CREATE INDEX idx_attacks_ip ON attacks(ip)");
    $pdo->exec("CREATE INDEX idx_blocked_ip ON blocked_ips(ip)");
    echo "✅ ایندکس‌ها ساخته شدند<br>";

} catch (Exception $e) {
    echo "❌ خطا در ساخت دیتابیس: " . $e->getMessage() . "<br>";
}

// ============================================
// 5. ساخت htaccess ها
// ============================================
$htaccessFiles = [
    $guardianPath . '/config/.htaccess' => "Order deny,allow\nDeny from all",
    $guardianPath . '/data/.htaccess' => "Order deny,allow\nDeny from all",
    $guardianPath . '/logs/.htaccess' => "Order deny,allow\nDeny from all",
];

foreach ($htaccessFiles as $file => $content) {
    if (!file_exists($file)) {
        file_put_contents($file, $content);
        echo "✅ " . basename(dirname($file)) . "/.htaccess ساخته شد<br>";
    }
}

// ============================================
// 6. تست
// ============================================
echo "<br><hr><br>";
echo "<h2>✅ همه چیز آماده است!</h2>";
echo "<p>الان می‌تونی از سایت استفاده کنی:</p>";
echo "<ul>";
echo "<li><a href='/project/' style='color:#10b981;'>🏠 سایت اصلی</a></li>";
echo "<li><a href='/project/guardian-admin' style='color:#6366f1;'>🔐 پنل مدیریت (admin / admin123)</a></li>";
echo "</ul>";
echo "<p><strong>تست‌های امنیتی:</strong></p>";
echo "<ul>";
echo "<li><a href='/project/?id=1%27%20OR%20%271%27=%271' style='color:#ef4444;'>تست SQL Injection (باید بلاک بشه)</a></li>";
echo "<li><a href='/project/?name=%3Cscript%3Ealert(1)%3C/script%3E' style='color:#ef4444;'>تست XSS (باید بلاک بشه)</a></li>";
echo "<li><a href='/project/?search=سلام' style='color:#10b981;'>تست عادی (باید کار کنه)</a></li>";
echo "</ul>";
?>
