<?php
$dbPath = __DIR__ . '/ai-web-guardian/data/guardian.db';

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // حذف تمام IP های مسدود
    $pdo->exec("DELETE FROM blocked_ips");
    echo "✅ تمام IP های مسدود آزاد شدند<br>";

    // نمایش وضعیت فعلی
    $count = $pdo->query("SELECT COUNT(*) FROM blocked_ips")->fetchColumn();
    echo "📊 تعداد IP های مسدود: {$count}<br>";

    // اضافه کردن 127.0.0.1 به whitelist
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO whitelist (ip, reason, created_at) VALUES (?, ?, datetime('now'))");
    $stmt->execute(['127.0.0.1', 'Localhost - Admin']);
    echo "✅ 127.0.0.1 به لیست سفید اضافه شد<br><br>";

    echo "<a href='/project/' style='background:#10b981;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🏠 برگشت به سایت</a><br><br>";
    echo "<a href='/project/guardian-admin' style='background:#6366f1;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔐 پنل مدیریت</a>";

} catch (Exception $e) {
    echo "❌ خطا: " . $e->getMessage();
}
?>
