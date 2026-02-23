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

$bs_css = ($dir == 'rtl') ? 'bootstrap.rtl.min.css' : 'bootstrap.min.css';

if (!$u) { 
    echo "<!DOCTYPE html><html lang='{$lang}' dir='{$dir}'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><link rel='stylesheet' href='../style.css'><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/{$bs_css}'></head><body><main style='display:flex; justify-content:center; align-items:center; height:100vh;'><div class='glass-panel text-center' style='width:90%; max-width:400px;'><h3 class='text-white'>" . _t('not_found') . "</h3></div></main></body></html>";
    exit; 
}

if (isset($_POST['user_pass'])) {
    if (password_verify($_POST['pass'], $u['password'])) {
        $_SESSION['user_auth_'.$req_user] = true;
    } else { $error = _t('password_error'); }
}

if (!isset($_SESSION['user_auth_'.$req_user])) {
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $req_user; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/<?php echo $bs_css; ?>">
</head>
<body>
    <main style="justify-content: center; display: flex; align-items: center; min-height: 100vh;" class="position-relative">
        <div class="position-absolute top-0 end-0 p-3">
             <a href="?setlang=<?php echo _t('next_lang'); ?>" class="btn-custom px-3 py-2" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                 <span class="flag-emoji" style="font-size: 1.3rem;"><?php echo _t('flag'); ?></span> <span class="ms-2"><?php echo _t('lang_name'); ?></span>
             </a>
        </div>
        <div class="glass-panel text-center" style="width: 90%; max-width: 400px;">
            <h3 class="mb-4 text-white" style="font-family: monospace; font-weight: bold;"><?php echo htmlspecialchars($req_user, ENT_QUOTES); ?></h3>
            <?php if(isset($error)): ?><div class="alert alert-danger py-2 mb-3" style="font-size:0.9rem"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST">
                <div class="pass-container">
                    <input type="password" name="pass" id="userPass" class="input-glass text-center" style="direction: ltr;" placeholder="<?php echo _t('password'); ?>" required>
                    <i class="fas fa-eye toggle-eye" onclick="togglePass()"></i>
                </div>
                <button name="user_pass" class="btn-custom btn-lg-custom btn-orange w-100 mt-4"><?php echo _t('login_btn'); ?></button>
            </form>
        </div>
    </main>
    <script>
        function togglePass() {
            const input = document.getElementById('userPass');
            const icon = event.target;
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
<?php exit; } 

$links = $db->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY title ASC");
$links->execute([$u['id']]);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo _t('user_page') . " " . htmlspecialchars($u['username'], ENT_QUOTES); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/<?php echo $bs_css; ?>">
    <style>
        .user-nav-custom { display: flex; justify-content: space-between; align-items: center; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(10px); padding: 15px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); position: sticky; top: 0; z-index: 1000; }
        .nav-side { flex: 1; display: flex; align-items: center; }
        .nav-center { flex: 2; text-align: center; }
        .nav-side-end { justify-content: flex-end; }
        .btn-logout-user { background: var(--red) !important; color: white !important; }
        
        @media (max-width: 768px) {
            .user-nav-custom { padding: 10px 5px; }
            .btn-custom { padding: 10px 15px !important; font-size: 1rem !important; }
            .nav-center h3 { font-size: 1.1rem !important; margin: 0; }
            .flag-emoji { font-size: 1.4rem !important; }
        }
    </style>
</head>
<body>
    <nav class="user-nav-custom">
        <div class="nav-side">
            <a href="?setlang=<?php echo _t('next_lang'); ?>" class="btn-custom px-3 py-2" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                <span class="flag-emoji"><?php echo _t('flag'); ?></span> <span class="ms-2 d-none d-sm-inline"><?php echo _t('lang_name'); ?></span>
            </a>
        </div>
        <div class="nav-center">
            <h3 class="text-white fw-bold m-0"><?php echo _t('welcome'); ?></h3>
        </div>
        <div class="nav-side nav-side-end">
            <a href="?action=logout" class="btn-custom btn-red px-3 py-2 btn-logout-user"><?php echo _t('logout'); ?></a>
        </div>
    </nav>

    <main class="container pt-4">
        <div class="d-flex justify-content-center mb-5">
            <div class="name-badge shadow" style="max-width: 250px; font-family: monospace;"><?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?></div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <?php foreach ($links->fetchAll() as $l): ?>
                <div class="glass-panel mb-4 shadow-lg">
                    <div class="name-badge text-center mb-3" style="background: rgba(0,0,0,0.3);"><?php echo htmlspecialchars($l['title'], ENT_QUOTES); ?></div>
                    <div class="btn-row">
                        <button onclick="copyTxt('<?php echo htmlspecialchars(addslashes($l['url']), ENT_QUOTES); ?>')" class="btn-custom btn-action btn-orange flex-fill p-3">
                            <i class="fas fa-copy me-2"></i> <span><?php echo _t('copy'); ?></span>
                        </button>
                        <button onclick="showQR('<?php echo htmlspecialchars(addslashes($l['url']), ENT_QUOTES); ?>')" class="btn-custom btn-action btn-orange flex-fill p-3">
                            <i class="fas fa-qrcode me-2"></i> <span><?php echo _t('qr_code'); ?></span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- فوتر اورجینال شما -->
    <footer class="mt-5">
        <div class="footer-box"><i class="fas fa-heart"></i><span>Made with Love</span></div>
        <div class="footer-box"><i class="fas fa-laptop"></i><span>Up to Date</span></div>
    </footer>

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
