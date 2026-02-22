<?php
session_start();
include 'db.php';

// بررسی لاگین ادمین
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login");
    exit;
}

// تشخیص آدرس سایت برای ساخت لینک هوشمند کاربر
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
    } catch (PDOException $e) {
        $admin_error = ($e->getCode() == 23000) ? "خطا: این نام کاربری قبلاً رزرو شده است." : "خطا در دیتابیس.";
    }
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

// اضافه شدن عملیات ویرایش لینک
if (isset($_POST['edit_link_action'])) {
    $db->prepare("UPDATE links SET title = ?, url = ? WHERE id = ?")->execute([$_POST['edit_link_title'], $_POST['edit_link_url'], $_POST['link_id']]);
    header("Location: admin"); exit;
}

// دریافت ادمین فعلی و لیست یوزرها (مرتب شده الفبایی)
$current_admin = $db->query("SELECT username FROM users WHERE role = 'admin'")->fetchColumn();
$users = $db->query("SELECT * FROM users WHERE role = 'user' ORDER BY username ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* استایل اختصاصی برای آیکون چشم داخل باکس */
        .pass-container { position: relative; width: 100%; }
        .pass-container input { padding-left: 45px !important; }
        .toggle-eye {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            z-index: 10;
        }
        /* استایل کمکی برای تراز شدن باکس لینک کاربر */
        .user-link-row { background: rgba(13, 110, 253, 0.05) !important; border: 1px dashed rgba(13, 110, 253, 0.3) !important; }
        .user-link-label { background: #0d6efd !important; color: white !important; font-weight: bold; }
    </style>
</head>
<body>

    <div class="admin-header">
        <h3 style="margin:0">پنل مدیریت کاربرها</h3>
        <a href="logout" class="btn-logout">خروج</a>
    </div>

    <div class="tabs">
        <button class="tab-btn <?php echo ($admin_error == '') ? 'active' : ''; ?>" onclick="openTab('userList')">لیست کاربران</button>
        <button class="tab-btn" onclick="openTab('addUser')">افزودن کاربر</button>
        <button class="tab-btn <?php echo ($admin_error != '') ? 'active' : ''; ?>" onclick="openTab('adminSettings')" style="color: #ffc107; border-color: #ffc107;">تنظیمات ادمین</button>
    </div>

    <!-- تب لیست کاربران -->
    <div id="userList" class="tab-content <?php echo ($admin_error == '') ? 'active' : ''; ?>">
        <?php foreach ($users as $u): ?>
        <div class="user-card" id="card-<?php echo $u['id']; ?>">
            <!-- هدر کارت -->
            <div class="user-header" onclick="toggleUser(<?php echo $u['id']; ?>)">
                <div class="user-info">
                    <div class="arrow-icon"><i class="fas fa-chevron-down"></i></div>
                    <span class="user-name-text"><?php echo $u['username']; ?></span>
                </div>
                <div class="user-actions" onclick="event.stopPropagation()">
                    <button onclick="openEditModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>')" class="btn-action btn-edit"><i class="fas fa-edit"></i> <span>ویرایش</span></button>
                    <a href="?delete_user=<?php echo $u['id']; ?>" class="btn-action btn-del" onclick="return confirm('حذف کاربر و تمام لینک‌ها؟')"><i class="fas fa-trash"></i> <span>حذف</span></a>
                </div>
            </div>

            <!-- بدنه کارت (لینک‌ها) -->
            <div id="body-<?php echo $u['id']; ?>" class="user-body">
                
                <!-- باکس لینک هوشمند اختصاصی کاربر -->
                <?php $userLink = $base_url . "/u/" . $u['username']; ?>
                <div class="link-row user-link-row">
                    <div class="input-name user-link-label">صفحه کاربر</div>
                    <div class="link-display-url" style="color: #6ea8fe; cursor: pointer; flex-grow:1;" onclick="window.open('<?php echo $userLink; ?>', '_blank')">
                        <?php echo $userLink; ?>
                    </div>
                    <button onclick="copyToClipboard('<?php echo $userLink; ?>')" class="btn-link-action btn-add" style="background: #198754;">
                        کپی <i class="fas fa-copy ms-1"></i>
                    </button>
                </div>

                <!-- فرم افزودن لینک جدید -->
                <form method="POST" class="link-row">
                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                    <input type="text" name="title" class="input-name" placeholder="نام لینک" required>
                    <button name="add_link" class="btn-link-action btn-add">اضافه کردن لینک</button>
                    <input type="text" name="url" class="input-url" placeholder="لینک سابسکریپشن" required>
                </form>

                <!-- لیست لینک‌های فعلی -->
                <?php
                $links = $db->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY title ASC");
                $links->execute([$u['id']]);
                foreach ($links->fetchAll() as $l): ?>
                <div class="link-row">
                    <div class="input-name" style="background:#2c3036; border:none;"><?php echo htmlspecialchars($l['title'], ENT_QUOTES); ?></div>
                    <div class="link-display-url"><?php echo htmlspecialchars($l['url'], ENT_QUOTES); ?></div>
                    
                    <!-- دکمه‌های عملیات لینک (حذف و ویرایش) -->
                    <div class="link-actions-group">
                        <button type="button" onclick="openEditLinkModal(<?php echo $l['id']; ?>, '<?php echo htmlspecialchars(addslashes($l['title']), ENT_QUOTES); ?>', '<?php echo htmlspecialchars(addslashes($l['url']), ENT_QUOTES); ?>')" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> <span>ویرایش</span>
                        </button>
                        <a href="?delete_link=<?php echo $l['id']; ?>" class="btn-action btn-del" onclick="return confirm('حذف این لینک؟')">
                            <i class="fas fa-trash"></i> <span>حذف</span>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- تب افزودن کاربر -->
    <div id="addUser" class="tab-content">
        <div class="user-card" style="padding: 30px;">
            <h4>ساخت یوزر جدید</h4>
            <form method="POST" style="display:flex; flex-direction:column; max-width: 400px; margin: 0 auto; gap:15px;">
                <input type="text" name="new_user" class="input-name" style="width:100%; text-align:right;" placeholder="نام کاربری" required>
                <div class="pass-container">
                    <input type="password" name="new_pass" id="newPass" class="input-name" style="width:100%; text-align:right;" placeholder="رمز عبور" required>
                    <i class="fas fa-eye toggle-eye" onclick="togglePassword('newPass', this)"></i>
                </div>
                <button name="add_user" class="btn-link-action btn-add" style="width:100%;">ساخت کاربر</button>
            </form>
        </div>
    </div>

    <!-- تب تنظیمات ادمین -->
    <div id="adminSettings" class="tab-content <?php echo ($admin_error != '') ? 'active' : ''; ?>">
        <div class="user-card" style="padding: 30px; border-color: #ffc107;">
            <h4 style="color: #ffc107;">تنظیمات حساب مدیریت</h4>
            <?php if($admin_error): ?> <div style="background:#dc3545; color:white; padding:10px; border-radius:8px; margin-bottom:15px;"><?php echo $admin_error; ?></div> <?php endif; ?>
            <form method="POST" style="display:flex; flex-direction:column; max-width: 400px; margin: 0 auto; gap:15px;">
                <label>نام کاربری ادمین:</label>
                <input type="text" name="admin_user" class="input-name" style="width:100%; text-align:right;" value="<?php echo $current_admin; ?>" required>
                <label>رمز عبور جدید ادمین:</label>
                <div class="pass-container">
                    <input type="password" name="admin_pass" id="adminUpdatePass" class="input-name" style="width:100%; text-align:right;" placeholder="خالی بگذارید تا تغییر نکند">
                    <i class="fas fa-eye toggle-eye" onclick="togglePassword('adminUpdatePass', this)"></i>
                </div>
                <button name="update_admin" class="btn-link-action btn-edit" style="width:100%; color:black; font-weight:bold;">ذخیره و خروج</button>
            </form>
        </div>
    </div>

    <!-- مودال ویرایش یوزر -->
    <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
        <div class="user-card" style="padding:20px; width:90%; max-width:400px; background:#2b3035;">
            <h4>ویرایش کاربر</h4>
            <form method="POST">
                <input type="hidden" name="user_id" id="edit_uid">
                <label>نام کاربری:</label>
                <input type="text" name="edit_name" id="edit_name" class="input-url mb-3" style="width:100%; text-align:right;">
                <label>رمز عبور جدید:</label>
                <div class="pass-container mb-3">
                    <input type="password" name="edit_pass" id="editPass" class="input-url" style="width:100%; text-align:right;" placeholder="خالی بگذارید تا تغییر نکند">
                    <i class="fas fa-eye toggle-eye" onclick="togglePassword('editPass', this)"></i>
                </div>
                <div style="display:flex; gap:10px;">
                    <button name="edit_user" class="btn-link-action btn-add" style="flex:1">ذخیره</button>
                    <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn-link-action btn-remove-link" style="flex:1">لغو</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال ویرایش لینک -->
    <div id="editLinkModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
        <div class="user-card" style="padding:20px; width:90%; max-width:400px; background:#2b3035;">
            <h4>ویرایش لینک</h4>
            <form method="POST">
                <input type="hidden" name="link_id" id="edit_link_id">
                <label>نام لینک:</label>
                <input type="text" name="edit_link_title" id="edit_link_title" class="input-url mb-3" style="width:100%; text-align:right;" required>
                <label>آدرس لینک:</label>
                <input type="text" name="edit_link_url" id="edit_link_url" class="input-url mb-3" style="width:100%; direction:ltr;" required>
                <div style="display:flex; gap:10px;">
                    <button name="edit_link_action" class="btn-link-action btn-add" style="flex:1">ذخیره</button>
                    <button type="button" onclick="document.getElementById('editLinkModal').style.display='none'" class="btn-link-action btn-remove-link" style="flex:1">لغو</button>
                </div>
            </form>
        </div>
    </div>

    <!-- باکس نکته پایین صفحه -->
    <div class="admin-note" style="background: rgba(255, 193, 7, 0.1); border: 1px solid #ffc107; color: #ffc107; padding: 15px; margin: 30px auto; width: 95%; max-width: 900px; border-radius: 10px; display: flex; align-items: center; gap: 15px;">
        <span style="font-size: 1.5rem;">💡</span>
        <div>
            <strong></strong> آدرس اختصاصی هر کاربر در ابتدای لیست لینک‌های او نمایش داده شده است. آن را به همراه رمز عبور به کاربر تحویل دهید.
        </div>
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
            body.style.display = (body.style.display === 'block') ? 'none' : 'block';
        }
        function openEditModal(id, name) {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('edit_uid').value = id;
            document.getElementById('edit_name').value = name;
        }
        
        // تابع باز کردن پاپ‌آپ ویرایش لینک
        function openEditLinkModal(id, title, url) {
            document.getElementById('editLinkModal').style.display = 'flex';
            document.getElementById('edit_link_id').value = id;
            document.getElementById('edit_link_title').value = title;
            document.getElementById('edit_link_url').value = url;
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            alert('کپی شد!');
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
