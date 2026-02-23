<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['setlang'])) {
    $_SESSION['lang'] = $_GET['setlang'];
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: " . $url);
    exit;
}

$lang = $_SESSION['lang'] ?? 'en';
$dir = ($lang == 'fa') ? 'rtl' : 'ltr';

$translations = [
    'en' => [
        'lang_name' => 'فارسی', 'flag' => '🇮🇷', 'next_lang' => 'fa',
        'login_title' => 'Admin Login', 'login_header' => 'Login to Admin Panel',
        'username' => 'Username', 'password' => 'Password', 'login_btn' => 'Login',
        'password_error' => 'Incorrect username or password', 'admin_title' => 'Admin Panel',
        'logout' => 'Logout', 'admin_title' => 'Admin Panel',
        'admin_users_panel' => 'Users Management',
        'user_list' => 'Users List', 'add_user' => 'Add User',
        'user_settings' => 'Admin Settings', 'username' => 'Username',
        'password' => 'Password', 'save' => 'Save', 'edit' => 'Edit',
        'delete' => 'Delete', 'add_link' => 'Add Link', 'cancel' => 'Cancel',
        'link_name' => 'Link Name', 'link_sub' => 'Subscription Link',
        'user_page' => 'User Page', 'copy' => 'Copy', 'note' => 'Note',
        'note_text' => 'Give the specific link and password to the user.',
        'new_user_title' => 'Create New User',
        'admin_settings_title' => 'Admin Account Settings',
        'admin_username' => 'Admin Username:',
        'admin_new_pass' => 'Admin New Password:',
        'leave_blank' => 'Leave blank to keep unchanged',
        'save_exit' => 'Save & Exit',
        'edit_user' => 'Edit User',
        'edit_link' => 'Edit Link',
        'link_address' => 'Link URL:',
        'copied' => 'Copied!',
        'del_confirm' => 'Delete user and all their links?',
        'del_link_confirm' => 'Delete this link?',
        'btn_create_user' => 'Create User',
        'add_link_btn' => 'Add Link',
        'qr_code' => 'QR Code',
        'welcome' => 'Subscription Page',
        'logout' => 'Logout',
        'login_btn' => 'Login',
        'not_found' => 'User Not Found',
        'password_error' => 'Incorrect password',
        'close' => 'Close',
        'error_duplicate' => 'Error: Username exists.',
        'login_title' => 'Admin Panel Login'
    ],
    'fa' => [
        'lang_name' => 'English', 'flag' => '🇺🇸', 'next_lang' => 'en',
        'login_title' => 'ورود مدیریت', 'login_header' => 'ورود به پنل مدیریت',
        'username' => 'نام کاربری', 'password' => 'رمز عبور', 'login_btn' => 'ورود',
        'password_error' => 'نام کاربری یا رمز عبور اشتباه است', 'admin_title' => 'پنل مدیریت',
        'logout' => 'خروج', 'admin_title' => 'پنل مدیریت',
        'admin_users_panel' => 'پنل مدیریت کاربرها',
        'user_list' => 'لیست کاربران', 'add_user' => 'افزودن کاربر',
        'user_settings' => 'تنظیمات ادمین', 'username' => 'نام کاربری',
        'password' => 'رمز عبور', 'save' => 'ذخیره', 'edit' => 'ویرایش',
        'delete' => 'حذف', 'add_link' => 'افزودن لینک', 'cancel' => 'لغو',
        'link_name' => 'نام لینک', 'link_sub' => 'لینک سابسکریپشن',
        'user_page' => 'صفحه کاربر', 'copy' => 'کپی', 'note' => 'نکته',
        'note_text' => 'آدرس اختصاصی هر کاربر در ابتدای لیست لینک‌های او نمایش داده شده است. آن را به همراه رمز عبور به کاربر تحویل دهید.',
        'new_user_title' => 'ساخت یوزر جدید',
        'admin_settings_title' => 'تنظیمات حساب مدیریت',
        'admin_username' => 'نام کاربری ادمین:',
        'admin_new_pass' => 'رمز عبور جدید ادمین:',
        'leave_blank' => 'خالی بگذارید تا تغییر نکند',
        'save_exit' => 'ذخیره و خروج',
        'edit_user' => 'ویرایش کاربر',
        'edit_link' => 'ویرایش لینک',
        'link_address' => 'آدرس لینک:',
        'copied' => 'کپی شد!',
        'del_confirm' => 'حذف کاربر و تمام لینک‌ها؟',
        'del_link_confirm' => 'حذف این لینک؟',
        'btn_create_user' => 'ساخت کاربر',
        'add_link_btn' => 'اضافه کردن لینک',
        'qr_code' => 'کد QR',
        'welcome' => 'صفحه دریافت لینک',
        'logout' => 'خروج',
        'login_btn' => 'ورود',
        'not_found' => 'کاربر یافت نشد',
        'password_error' => 'رمز عبور اشتباه است',
        'close' => 'بستن',
        'error_duplicate' => 'خطا: نام کاربری تکراری است.',
        'login_title' => 'ورود به پنل مدیریت'

    ]
];

function _t($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $key;
}
?>
