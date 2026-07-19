<?php

session_start();

// اگر کاربر قبلاً وارد شده باشد
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: admin-panel.php');
    } else {
        header('Location: register-project.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($password === '12345678') {
        $_SESSION['user_type'] = 'user';
        $_SESSION['username'] = !empty($username) ? $username : 'کاربر عادی';
        header('Location: register-project.php');
        exit();
    } elseif ($password === '842113109') {
        $_SESSION['user_type'] = 'admin';
        $_SESSION['username'] = !empty($username) ? $username : 'مدیر سیستم';
        header('Location: admin-panel.php');
        exit();
    } else {
        $error = 'شناسه کاربری یا رمز عبور اشتباه است.';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود - اداره کل امور عشایر استان چهارمحال و بختیاری</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        .developer-info{
            color: red;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon">🏛️</div>
            <h1>اداره کل امور عشایر</h1>
            <p>استان چهارمحال و بختیاری</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="error-message">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">شناسه کاربری</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" id="username" name="username" class="form-control"
                               placeholder="شناسه کاربری خود را وارد کنید" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">رمز عبور</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="رمز عبور خود را وارد کنید" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    ورود به سامانه
                </button>
            </form>
        </div>

        <div class="login-footer">
            سامانه ثبت و مدیریت پروژه‌های عمرانی عشایری
        </div>

        <div class="developer-info" align="center">
            ساخته شده توسط: <strong>محمد مهدی حسینی</strong><br>
            📧 <a href="mailto:mohammadmehdih269@gmail.com" class="link">mohammadmehdih269@gmail.com</a>
        </div>
    </div>
<body>
</html>
