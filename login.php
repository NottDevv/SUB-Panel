<?php
session_start();
include 'lang.php'; // اتصال به فایل زبان
include 'db.php';

if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: admin");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = $user['username'];
        header("Location: admin");
        exit;
    } else {
        $error = _t('password_error');
    }
}

// تنظیم بوت‌استرپ
$bs_css = ($dir == 'rtl') ? 'bootstrap.rtl.min.css' : 'bootstrap.min.css';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo _t('login_title'); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/<?php echo $bs_css; ?>">
    <style>
        /* استایل اختصاصی برای فیکس کردن آیکون چشم دقیقا داخل کادر */
        .pass-container { position: relative; width: 100%; display: flex; align-items: center; }
        .toggle-eye {
            position: absolute;
            cursor: pointer;
            color: #aaa;
            z-index: 10;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
        }
        /* تنظیم فاصله چشم بر اساس زبان */
        <?php if($dir == 'rtl'): ?>
            .toggle-eye { left: 15px; }
            .input-glass { padding-left: 45px !important; padding-right: 15px !important; }
        <?php else: ?>
            .toggle-eye { right: 15px; }
            .input-glass { padding-right: 45px !important; padding-left: 15px !important; }
        <?php endif; ?>

        .input-glass::placeholder { color: rgba(255,255,255,0.5); }
    </style>
</head>
<body class="d-flex flex-column" style="min-height: 100vh;">

    <!-- دکمه تغییر زبان بالای صفحه -->
    <div class="user-page-header px-4 pt-3">
        <div class="header-side">
            <a href="?setlang=<?php echo _t('next_lang'); ?>" class="btn-lang" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 8px; text-decoration: none; color: white; display: inline-flex; align-items: center; gap: 8px;">
                <span class="flag-emoji"><?php echo _t('flag'); ?></span> <span><?php echo _t('lang_name'); ?></span>
            </a>
        </div>
    </div>

    <main class="d-flex align-items-center justify-content-center flex-grow-1">
        <div class="glass-panel text-center" style="width: 90%; max-width: 400px; padding: 30px; border-radius: 20px;">
            <h3 class="mb-4 fw-bold text-white"><?php echo _t('login_header'); ?></h3>
            
            <?php if($error): ?>
                <div class="alert alert-danger py-2 mb-3 rounded-3" style="font-size: 0.9rem;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <input type="text" name="username" class="input-glass text-center" placeholder="<?php echo _t('username'); ?>" style="height: 50px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: 12px; width: 100%;" required>
                </div>
                
                <div class="pass-container mb-3">
                    <input type="password" name="password" id="adminPass" class="input-glass text-center" placeholder="<?php echo _t('password'); ?>" style="height: 50px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: 12px; width: 100%;" required>
                    <i class="fas fa-eye toggle-eye" onclick="togglePassword('adminPass', this)"></i>
                </div>

                <button type="submit" class="btn-custom btn-lg-custom btn-orange w-100 mt-2" style="height: 50px; border-radius: 12px; font-weight: bold;"><?php echo _t('login_btn'); ?></button>
            </form>
        </div>
    </main>

    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</body>
</html>
