<?php
$dbPath = __DIR__ . '/ai-web-guardian/data/guardian.db';

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ایجاد جدول whitelist اگر وجود نداره
    $pdo->exec("CREATE TABLE IF NOT EXISTS whitelist (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT UNIQUE,
        reason TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // ایجاد جدول custom_rules اگر وجود نداره
    $pdo->exec("CREATE TABLE IF NOT EXISTS custom_rules (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        pattern TEXT,
        severity REAL DEFAULT 0.7,
        enabled INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    echo "✅ جداول با موفقیت ساخته شدن!<br>";
    echo "📋 whitelist - ساخته شد<br>";
    echo "📋 custom_rules - ساخته شد<br>";
    echo "<br><a href='/project/guardian-admin' style='background:#4f46e5;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔐 ورود به پنل مدیریت</a>";

} catch (Exception $e) {
    echo "❌ خطا: " . $e->getMessage();
}
?>
