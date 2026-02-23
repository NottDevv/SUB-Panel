<?php
session_start();
include 'lang.php';
include 'db.php';
$req_user = $_GET['username'];

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['user_auth_'.$req_user]);
    header("Location: ../u/" . $req_user);
    exit;
}

$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$req_user]);
$u = $stmt->fetch();

if (!$u) {
    echo "<!DOCTYPE html><html lang='{$lang}' dir='{$dir}'><head><meta charset='UTF-8'><link rel='stylesheet' href='../style.css'><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'></head><body><main style='display:flex; justify-content:center; align-items:center; height:100vh;'><div class='glass-panel text-center' style='width:90%; max-width:400px;'><h3 class='text-white'>"._t('not_found')."</h3></div></main></body></html>";
    exit;
}

if (isset($_POST['user_pass'])) {
    if (password_verify($_POST['pass'], $u['password'])) {
        $_SESSION['user_auth_'.$req_user] = true;
    } else { $error = _t('password_error'); }
}

$bs_css = ($dir == 'rtl') ? 'bootstrap.rtl.min.css' : 'bootstrap.min.css';

if (!isset($_SESSION['user_auth_'.$req_user])) {
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login <?php echo $req_user; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/<?php echo $bs_css; ?>">
    <style>
        .pass-container { position: relative; width: 100%; display: flex; align-items: center; }
        .toggle-eye { position: absolute; cursor: pointer; color: #aaa; z-index: 10; top: 50%; transform: translateY(-50%); font-size: 1.1rem; }
        <?php if($dir == 'rtl'): ?>
            .toggle-eye { left: 15px; }
            .input-glass { padding-left: 45px !important; padding-right: 15px !important; }
        <?php else: ?>
            .toggle-eye { right: 15px; }
            .input-glass { padding-right: 45px !important; padding-left: 15px !important; }
        <?php endif; ?>
    </style>
</head>
<body>
    <div style="position: absolute; top: 15px; <?php echo $dir == 'rtl' ? 'left: 15px;' : 'right: 15px;'; ?>">
        <a href="?setlang=<?php echo _t('next_lang'); ?>" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 8px; text-decoration: none; color: white; display: inline-flex; align-items: center; gap: 8px;">
            <span><?php echo _t('flag'); ?></span> <span><?php echo _t('lang_name'); ?></span>
        </a>
    </div>
    <main style="justify-content: center; display: flex; align-items: center; min-height: 100vh;">
        <div class="glass-panel text-center" style="width: 90%; max-width: 400px;">
            <h3 class="mb-4 text-white"><?php echo $req_user; ?></h3>
            <?php if(isset($error)): ?><div class="alert alert-danger py-2 mb-3" style="font-size:0.9rem"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST">
                <div class="pass-container">
                    <input type="password" name="pass" id="userPass" class="input-glass text-center" placeholder="<?php echo _t('password'); ?>" required>
                    <i class="fas fa-eye toggle-eye" onclick="togglePass()"></i>
                </div>
                <button name="user_pass" class="btn-custom btn-lg-custom btn-orange w-100 mt-4"><?php echo _t('login_btn'); ?></button>
            </form>
        </div>
    </main>
    <script>function togglePass(){const i=document.getElementById('userPass'),e=event.target;"password"===i.type?(i.type="text",e.classList.replace("fa-eye","fa-eye-slash")):(i.type="password",e.classList.replace("fa-eye-slash","fa-eye"))}</script>
</body>
</html>
<?php exit; }

$links = $db->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY title ASC");
$links->execute([$u['id']]);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $u['username']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/<?php echo $bs_css; ?>">
    <style>
        .user-nav { background: rgba(0,0,0,0.3); border-bottom: 1px solid rgba(255,255,255,0.1); padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; backdrop-filter: blur(10px); }
        .btn-header { padding: 8px 15px; border-radius: 8px; text-decoration: none; color: white; font-size: 0.9rem; display: inline-flex; align-items: center; transition: 0.3s; }
        .btn-lang-user { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }
        .btn-logout-user { background: var(--red); }
        .btn-logout-user:hover { background: #b02a37; }
        .user-badge-container { max-width: 250px; margin: 20px auto; }
        @media (max-width: 576px) { .user-nav { padding: 10px; } .btn-header span.d-none { display: none; } }
    </style>
</head>
<body>
    <nav class="user-nav">
        <a href="?setlang=<?php echo _t('next_lang'); ?>" class="btn-header btn-lang-user">
            <span><?php echo _t('flag'); ?></span> <span class="d-none d-sm-inline ms-2"><?php echo _t('lang_name'); ?></span>
        </a>
        <h5 class="m-0 text-white d-none d-md-block"><?php echo _t('welcome'); ?></h5>
        
        <!-- دکمه خروج اصلاح شده (متصل به ترجمه) -->
        <a href="?action=logout" class="btn-header btn-logout-user">
            <?php if($dir == 'rtl'): ?>
                <span><?php echo _t('logout'); ?></span> <i class="fas fa-sign-out-alt ms-2"></i>
            <?php else: ?>
                <i class="fas fa-sign-out-alt me-2"></i> <span><?php echo _t('logout'); ?></span>
            <?php endif; ?>
        </a>
    </nav>

    <main class="container">
        <div class="text-center mt-4">
            <h3 class="page-title d-md-none mb-4"><?php echo _t('welcome'); ?></h3>
            <div class="user-badge-container">
                <div class="name-badge shadow-sm"><?php echo $u['username']; ?></div>
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-md-9 col-lg-7">
                <?php foreach ($links->fetchAll() as $l): ?>
                <div class="glass-panel shadow-lg">
                    <div class="name-badge mb-3" style="background: rgba(255,255,255,0.05);"><?php echo $l['title']; ?></div>
                    <div class="btn-row">
                        <button onclick="showQR('<?php echo htmlspecialchars($l['url']); ?>')" class="btn-custom btn-action btn-orange flex-fill">
                            <?php if($dir == 'rtl'): ?>
                                <?php echo _t('qr_code'); ?> <i class="fas fa-qrcode ms-2"></i>
                            <?php else: ?>
                                <i class="fas fa-qrcode me-2"></i> <?php echo _t('qr_code'); ?>
                            <?php endif; ?>
                        </button>
                        <button onclick="copyTxt('<?php echo htmlspecialchars($l['url']); ?>')" class="btn-custom btn-action btn-orange flex-fill">
                            <?php if($dir == 'rtl'): ?>
                                <?php echo _t('copy'); ?> <i class="fas fa-copy ms-2"></i>
                            <?php else: ?>
                                <i class="fas fa-copy me-2"></i> <?php echo _t('copy'); ?>
                            <?php endif; ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <div id="qrModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); z-index:9999; align-items:center; justify-content:center;">
        <div class="bg-white p-4 rounded-4 text-center mx-3">
            <div id="qrcode"></div>
            <p class="mt-3 text-dark fw-bold mb-0" style="cursor:pointer;"><?php echo _t('close'); ?></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    function copyTxt(t){navigator.clipboard.writeText(t);alert("<?php echo _t('copied'); ?>")}
    function showQR(t){document.getElementById('qrcode').innerHTML="",new QRCode(document.getElementById("qrcode"),{text:t,width:250,height:250}),document.getElementById('qrModal').style.display='flex'}
    </script>
</body>
</html>
