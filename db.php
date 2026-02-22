<?php
try {
    // اتصال به دیتابیس
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ساخت جدول کاربران (اگر نباشد)
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password TEXT,
        role TEXT DEFAULT 'user'
    )");

    // ساخت جدول لینک‌ها (اگر نباشد)
    $db->exec("CREATE TABLE IF NOT EXISTS links (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        title TEXT,
        url TEXT,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");

    // *** اصلاح مهم برای جلوگیری از خطا ***
    // چک می‌کنیم آیا هیچ ادمینی در سیستم هست یا نه؟
    $checkAdmin = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

    // فقط اگر هیچ ادمینی نبود، ادمین پیش‌فرض را بساز
    if ($checkAdmin == 0) {
        $pass = password_hash('123456', PASSWORD_DEFAULT);
        // از INSERT OR IGNORE استفاده می‌کنیم که اگر به هر دلیلی تداخل داشت، ارور ندهد
        $db->exec("INSERT OR IGNORE INTO users (username, password, role) VALUES ('admin', '$pass', 'admin')");
    }

} catch (PDOException $e) {
    // اگر خطایی رخ داد، به جای کرش کردن سایت، پیام ساده بده
    die("خطا در اتصال به دیتابیس: " . $e->getMessage());
}
?>
