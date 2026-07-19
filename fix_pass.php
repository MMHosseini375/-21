<?php
// ایجاد هش جدید برای رمز "2"
$password = '2';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "رمز: " . $password . "<br>";
echo "هش: " . $hash . "<br><br>";

// ساخت فایل admin.json
$adminData = array(
    "username" => "1",
    "password_hash" => $hash,
    "email" => "admin@guardian.local"
);

$jsonString = json_encode($adminData);
file_put_contents(__DIR__ . '/ai-web-guardian/config/admin.json', $jsonString);

echo "✅ فایل admin.json با موفقیت ساخته شد!<br>";
echo "📁 مسیر: " . __DIR__ . '/ai-web-guardian/config/admin.json<br><br>';
echo "🔑 اطلاعات لاگین:<br>";
echo "نام کاربری: <strong>1</strong><br>";
echo "رمز عبور: <strong>2</strong><br><br>";
echo "<a href='/project/guardian-admin' style='background:#4f46e5;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>ورود به پنل مدیریت</a>";
?>
