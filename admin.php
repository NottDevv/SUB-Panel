<?php
session_start();
include 'lang.php';
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login");
    exit;
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'];
$admin_error = "";

// --- عملیات مدیریت ادمین ---
if (isset($_POST['update_admin'])) {
    $new_user = $_POST['admin_user'];
    $new_pass = $_POST['admin_pass'];
    try {
        if (!empty($new_pass)) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET username = ?, password = ? WHERE role = 'admin'")->execute([$new_user, $hashed_pass]);
        } else {
            $db->prepare("UPDATE users SET username = ? WHERE role = 'admin'")->execute([$new_user]);
        }
        session_destroy();
        header("Location: login?msg=updated");
        exit;
    } catch (PDOException $e) { $admin_error = _t('error_duplicate'); }
}

// --- عملیات مدیریت کاربران معمولی ---
if (isset($_POST['add_user'])) {
    $u = $_POST['new_user'];
    $p = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
    try {
        $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)")->execute([$u, $p]);
        header("Location: admin"); exit;
    } catch (PDOException $e) { header("Location: admin?err=exists"); exit; }
}

if (isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    $db->prepare("DELETE FROM links WHERE user_id = ?")->execute([$uid]);
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
    header("Location: admin"); exit;
}

if (isset($_POST['edit_user'])) {
    $uid = $_POST['user_id'];
    $new_name = $_POST['edit_name'];
    try {
        if (!empty($_POST['edit_pass'])) {
            $new_pass = password_hash($_POST['edit_pass'], PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?")->execute([$new_name, $new_pass, $uid]);
        } else {
            $db->prepare("UPDATE users SET username = ? WHERE id = ?")->execute([$new_name, $uid]);
        }
        header("Location: admin"); exit;
    } catch (PDOException $e) { header("Location: admin?err=exists"); exit; }
}

// --- عملیات لینک‌ها ---
if (isset($_POST['add_link'])) {
    $db->prepare("INSERT INTO links (user_id, title, url) VALUES (?, ?, ?)")->execute([$_POST['user_id'], $_POST['title'], $_POST['url']]);
    header("Location: admin"); exit;
}

if (isset($_GET['delete_link'])) {
    $db->prepare("DELETE FROM links WHERE id = ?")->execute([$_GET['delete_link']]);
    header("Location: admin"); exit;
}

if (isset($_POST['edit_link_action'])) {
    $db->prepare("UPDATE links SET title = ?, url = ? WHERE id = ?")->execute([$_POST['edit_link_title'], $_POST['edit_link_url'], $_POST['link_id']]);
    header("Location: admin"); exit;
}

$current_admin = $db->query("SELECT username FROM users WHERE role = 'admin'")->fetchColumn();
$users = $db->query("SELECT * FROM users WHERE role = 'user' ORDER BY username ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo _t('admin_title'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <div class="admin-header">
        <div class="header-side">
            <a href="?setlang=<?php echo _t('next_lang'); ?>" class="btn-lang">
                <span class="flag-emoji"><?php echo _t('flag'); ?></span> <span class="lang-text"><?php echo _t('lang_name'); ?></span>
            </a>
        </div>
        <div class="header-center">
            <h3><?php echo _t('admin_users_panel'); ?></h3>
        </div>
        <div class="header-side header-side-end">
            <a href="logout" class="btn-logout"><?php echo _t('logout'); ?></a>
        </div>
    </div>

    <div class="tabs">
        <button class="tab-btn <?php echo ($admin_error == '') ? 'active' : ''; ?>" onclick="openTab('userList')"><?php echo _t('user_list'); ?></button>
        <button class="tab-btn" onclick="openTab('addUser')"><?php echo _t('add_user'); ?></button>
        <button class="tab-btn <?php echo ($admin_error != '') ? 'active' : ''; ?>" onclick="openTab('adminSettings')" style="color: #ffc107; border-color: #ffc107;"><?php echo _t('user_settings'); ?></button>
    </div>

    <div id="userList" class="tab-content <?php echo ($admin_error == '') ? 'active' : ''; ?>">
        <?php foreach ($users as $u): ?>
        <div class="user-card" id="card-<?php echo $u['id']; ?>">
            <div class="user-header" onclick="toggleUser(<?php echo $u['id']; ?>)">
                <div class="user-info">
                    <div class="arrow-icon"><i class="fas fa-chevron-down"></i></div>
                    <span class="user-name-text"><?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?></span>
                </div>
                <div class="user-actions" onclick="event.stopPropagation()">
                    <button onclick="openEditModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>')" class="btn-action btn-edit"><i class="fas fa-edit"></i> <span><?php echo _t('edit'); ?></span></button>
                    <a href="?delete_user=<?php echo $u['id']; ?>" class="btn-action btn-del" onclick="return confirm('<?php echo _t('del_confirm'); ?>')"><i class="fas fa-trash"></i> <span><?php echo _t('delete'); ?></span></a>
                </div>
            </div>

            <div id="body-<?php echo $u['id']; ?>" class="user-body">
                <?php $userLink = $base_url . "/u/" . $u['username']; ?>
                <div class="link-row user-link-row">
                    <div class="input-name user-link-label"><?php echo _t('user_page'); ?></div>
                    <div class="link-display-url text-ltr" style="color: #6ea8fe; cursor: pointer; flex-grow:1;" onclick="window.open('<?php echo $userLink; ?>', '_blank')">
                        <?php echo $userLink; ?>
                    </div>
                    <!-- اصلاح دکمه کپی (آیکون اول، متن دوم) -->
                    <button onclick="copyToClipboard('<?php echo $userLink; ?>')" class="btn-link-action btn-add" style="background: #198754;">
                        <i class="fas fa-copy"></i> <span><?php echo _t('copy'); ?></span>
                    </button>
                </div>

                <form method="POST" class="link-row">
                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                    <input type="text" name="title" class="input-name text-dir-auto" placeholder="<?php echo _t('link_name'); ?>" required>
                    <input type="text" name="url" class="input-url text-ltr" placeholder="<?php echo _t('link_sub'); ?>" required>
                    <!-- اضافه شدن آیکون به دکمه افزودن لینک برای زیبایی بیشتر -->
                    <button name="add_link" class="btn-link-action btn-add">
                        <i class="fas fa-plus"></i> <span><?php echo _t('add_link_btn'); ?></span>
                    </button>
                </form>

                <?php
                $links = $db->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY title ASC");
                $links->execute([$u['id']]);
                foreach ($links->fetchAll() as $l): ?>
                <div class="link-row">
                    <div class="input-name text-dir-auto" style="background:#2c3036; border:none;"><?php echo htmlspecialchars($l['title'], ENT_QUOTES); ?></div>
                    <div class="link-display-url text-ltr"><?php echo htmlspecialchars($l['url'], ENT_QUOTES); ?></div>
                    
                    <div class="link-actions-group">
                        <button type="button" onclick="openEditLinkModal(<?php echo $l['id']; ?>, '<?php echo htmlspecialchars(addslashes($l['title']), ENT_QUOTES); ?>', '<?php echo htmlspecialchars(addslashes($l['url']), ENT_QUOTES); ?>')" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> <span><?php echo _t('edit'); ?></span>
                        </button>
                        <a href="?delete_link=<?php echo $l['id']; ?>" class="btn-action btn-del" onclick="return confirm('<?php echo _t('del_link_confirm'); ?>')">
                            <i class="fas fa-trash"></i> <span><?php echo _t('delete'); ?></span>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div id="addUser" class="tab-content">
        <div class="user-card" style="padding: 30px;">
            <h4 class="form-title"><?php echo _t('new_user_title'); ?></h4>
            <form method="POST" class="form-container">
                <input type="text" name="new_user" class="input-name input-ltr-type" placeholder="<?php echo _t('username'); ?>" required>
                <div class="pass-container">
                    <input type="password" name="new_pass" id="newPass" class="input-name input-ltr-type" placeholder="<?php echo _t('password'); ?>" required>
                    <i class="fas fa-eye toggle-eye" onclick="togglePassword('newPass', this)"></i>
                </div>
                <button name="add_user" class="btn-link-action btn-add" style="width:100%;"><?php echo _t('btn_create_user'); ?></button>
            </form>
        </div>
    </div>

    <div id="adminSettings" class="tab-content <?php echo ($admin_error != '') ? 'active' : ''; ?>">
        <div class="user-card" style="padding: 30px; border-color: #ffc107;">
            <h4 class="form-title" style="color: #ffc107;"><?php echo _t('admin_settings_title'); ?></h4>
            <?php if($admin_error): ?> <div style="background:#dc3545; color:white; padding:10px; border-radius:8px; margin-bottom:15px; text-align:center;"><?php echo $admin_error; ?></div> <?php endif; ?>
            <form method="POST" class="form-container">
                <label><?php echo _t('admin_username'); ?></label>
                <input type="text" name="admin_user" class="input-name input-ltr-type" value="<?php echo htmlspecialchars($current_admin, ENT_QUOTES); ?>" required>
                <label><?php echo _t('admin_new_pass'); ?></label>
                <div class="pass-container">
                    <input type="password" name="admin_pass" id="adminUpdatePass" class="input-name input-ltr-type" placeholder="<?php echo _t('leave_blank'); ?>">
                    <i class="fas fa-eye toggle-eye" onclick="togglePassword('adminUpdatePass', this)"></i>
                </div>
                <button name="update_admin" class="btn-link-action btn-edit" style="width:100%; color:black; font-weight:bold;"><?php echo _t('save_exit'); ?></button>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal-overlay">
        <div class="user-card modal-box">
            <h4 class="form-title"><?php echo _t('edit_user'); ?></h4>
            <form method="POST" class="form-container">
                <input type="hidden" name="user_id" id="edit_uid">
                <label><?php echo _t('username'); ?></label>
                <input type="text" name="edit_name" id="edit_name" class="input-url input-ltr-type mb-3">
                <label><?php echo _t('password'); ?></label>
                <div class="pass-container mb-3">
                    <input type="password" name="edit_pass" id="editPass" class="input-url input-ltr-type" placeholder="<?php echo _t('leave_blank'); ?>">
                    <i class="fas fa-eye toggle-eye" onclick="togglePassword('editPass', this)"></i>
                </div>
                <div style="display:flex; gap:10px; width:100%;">
                    <button name="edit_user" class="btn-link-action btn-add" style="flex:1"><?php echo _t('save'); ?></button>
                    <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn-link-action btn-remove-link" style="flex:1"><?php echo _t('cancel'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div id="editLinkModal" class="modal-overlay">
        <div class="user-card modal-box">
            <h4 class="form-title"><?php echo _t('edit_link'); ?></h4>
            <form method="POST" class="form-container">
                <input type="hidden" name="link_id" id="edit_link_id">
                <label><?php echo _t('link_name'); ?></label>
                <input type="text" name="edit_link_title" id="edit_link_title" class="input-url text-dir-auto mb-3" required>
                <label><?php echo _t('link_address'); ?></label>
                <input type="text" name="edit_link_url" id="edit_link_url" class="input-url text-ltr mb-3" required>
                <div style="display:flex; gap:10px; width:100%;">
                    <button name="edit_link_action" class="btn-link-action btn-add" style="flex:1"><?php echo _t('save'); ?></button>
                    <button type="button" onclick="document.getElementById('editLinkModal').style.display='none'" class="btn-link-action btn-remove-link" style="flex:1"><?php echo _t('cancel'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-note">
        <span class="note-icon">💡</span>
        <div class="note-text"><strong><?php echo _t('note'); ?>:</strong> <?php echo _t('note_text'); ?></div>
    </div>

    <script>
        function openTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        function toggleUser(id) {
            const body = document.getElementById('body-' + id);
            const card = document.getElementById('card-' + id);
            if (body.style.display === 'block') {
                body.style.display = 'none';
                card.classList.remove('active');
                sessionStorage.removeItem('openUser');
            } else {
                body.style.display = 'block';
                card.classList.add('active');
                sessionStorage.setItem('openUser', id);
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            const openUserId = sessionStorage.getItem('openUser');
            if (openUserId) {
                const body = document.getElementById('body-' + openUserId);
                const card = document.getElementById('card-' + openUserId);
                if (body && card) {
                    body.style.display = 'block';
                    card.classList.add('active');
                }
            }
        });

        function openEditModal(id, name) {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('edit_uid').value = id;
            document.getElementById('edit_name').value = name;
        }
        
        function openEditLinkModal(id, title, url) {
            document.getElementById('editLinkModal').style.display = 'flex';
            document.getElementById('edit_link_id').value = id;
            document.getElementById('edit_link_title').value = title;
            document.getElementById('edit_link_url').value = url;
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            alert("<?php echo _t('copied'); ?>");
        }

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
