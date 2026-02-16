<?php
session_start();
include 'db.php';

// بررسی لاگین ادمین
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login");
    exit;
}

// افزودن کاربر
if (isset($_POST['add_user'])) {
    $u = $_POST['new_user'];
    $p = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
    $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)")->execute([$u, $p]);
    header("Location: admin"); exit;
}

// حذف کاربر
if (isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    $db->prepare("DELETE FROM links WHERE user_id = ?")->execute([$uid]);
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
    header("Location: admin"); exit;
}

// ویرایش کاربر
if (isset($_POST['edit_user'])) {
    $uid = $_POST['user_id'];
    $new_name = $_POST['edit_name'];
    if (!empty($_POST['edit_pass'])) {
        $new_pass = password_hash($_POST['edit_pass'], PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?")->execute([$new_name, $new_pass, $uid]);
    } else {
        $db->prepare("UPDATE users SET username = ? WHERE id = ?")->execute([$new_name, $uid]);
    }
    header("Location: admin"); exit;
}

// افزودن لینک
if (isset($_POST['add_link'])) {
    $db->prepare("INSERT INTO links (user_id, title, url) VALUES (?, ?, ?)")->execute([$_POST['user_id'], $_POST['title'], $_POST['url']]);
    header("Location: admin"); exit;
}

// حذف لینک
if (isset($_GET['delete_link'])) {
    $db->prepare("DELETE FROM links WHERE id = ?")->execute([$_GET['delete_link']]);
    header("Location: admin"); exit;
}

// دریافت لیست کاربران (مرتب‌سازی بر اساس حروف الفبا)
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
</head>
<body>

    <!-- هدر -->
    <div class="admin-header">
        <h3 style="margin:0">پنل مدیریت کاربرها</h3>
        <a href="logout" class="btn-logout">خروج</a>
    </div>

    <!-- تب‌ها -->
    <div class="tabs">
        <button class="tab-btn active" onclick="openTab('userList')">لیست کاربران</button>
        <button class="tab-btn" onclick="openTab('addUser')">افزودن کاربر</button>
    </div>

    <!-- تب لیست کاربران -->
    <div id="userList" class="tab-content active">
        <?php foreach ($users as $u): ?>
        <div class="user-card" id="card-<?php echo $u['id']; ?>">
            <!-- هدر کارت (کلیک باز می‌کند) -->
            <div class="user-header" onclick="toggleUser(<?php echo $u['id']; ?>)">
                <div class="user-info">
                    <div class="arrow-icon"><i class="fas fa-chevron-down"></i></div>
                    <span class="user-name-text"><?php echo $u['username']; ?></span>
                </div>
                <!-- دکمه‌ها -->
                <div class="user-actions" onclick="event.stopPropagation()">
                    <button onclick="openEditModal(<?php echo $u['id']; ?>, '<?php echo $u['username']; ?>')" class="btn-action btn-edit"><i class="fas fa-edit"></i> ویرایش</button>
                    <a href="?delete_user=<?php echo $u['id']; ?>" class="btn-action btn-del" onclick="return confirm('حذف کاربر؟')"><i class="fas fa-trash"></i> حذف</a>
                </div>
            </div>

            <!-- بدنه کارت -->
            <div id="body-<?php echo $u['id']; ?>" class="user-body">
                
                <!-- فرم افزودن لینک -->
                <form method="POST" class="link-row">
                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                    <input type="text" name="title" class="input-name" placeholder="نام لینک" required>
                    <button name="add_link" class="btn-link-action btn-add">اضافه کردن لینک</button>
                    <input type="text" name="url" class="input-url" placeholder="لینک سابسکریپشن" required>
                </form>

                <!-- لیست لینک‌ها -->
                <?php
                $links = $db->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY id DESC");
                $links->execute([$u['id']]);
                foreach ($links->fetchAll() as $l): ?>
                <div class="link-row">
                    <div class="input-name" style="background:#2c3036; border:none;"><?php echo $l['title']; ?></div>
                    <a href="?delete_link=<?php echo $l['id']; ?>" class="btn-link-action btn-remove-link" onclick="return confirm('حذف لینک؟')">حذف لینک</a>
                    <div class="link-display-url"><?php echo $l['url']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- تب افزودن کاربر -->
    <div id="addUser" class="tab-content">
        <div class="user-card" style="padding: 30px;">
            <h4 style="margin-top:0">ساخت یوزر جدید</h4>
            <form method="POST" style="display:flex; flex-direction:column; gap:15px;">
                <input type="text" name="new_user" class="input-name" style="width:100%; text-align:right;" placeholder="نام کاربری" required>
                <input type="password" name="new_pass" class="input-name" style="width:100%; text-align:right;" placeholder="رمز عبور" required>
                <button name="add_user" class="btn-link-action btn-add" style="width:100%;">ساخت کاربر</button>
            </form>
        </div>
    </div>

    <!-- مودال ویرایش -->
    <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
        <div class="user-card" style="padding:20px; width:90%; max-width:400px; background:#2b3035;">
            <h4>ویرایش کاربر</h4>
            <form method="POST">
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="user_id" id="edit_uid">
                <label>نام کاربری:</label>
                <input type="text" name="edit_name" id="edit_name" class="input-url mb-2" style="width:100%; text-align:right; margin-bottom:10px;">
                <label>رمز عبور جدید:</label>
                <input type="text" name="edit_pass" class="input-url mb-2" style="width:100%; text-align:right; margin-bottom:15px;" placeholder="خالی بگذارید تا تغییر نکند">
                <div style="display:flex; gap:10px;">
                    <button class="btn-link-action btn-add" style="flex:1">ذخیره</button>
                    <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn-link-action btn-remove-link" style="flex:1">لغو</button>
                </div>
            </form>
        </div>
    </div>

    <!-- باکس نکته راهنما -->
    <div class="admin-note" style="background: rgba(255, 193, 7, 0.1); border: 1px solid #ffc107; color: #ffc107; padding: 15px; margin: 30px auto; width: 95%; max-width: 900px; border-radius: 10px; display: flex; align-items: center; gap: 15px;">
        <span style="font-size: 1.5rem;">💡</span>
        <div>
            <strong>نکته:</strong> برای دسترسی کاربر به لینک‌های ساب، آدرس زیر و پسورد تعیین شده را به او تحویل دهید:
            <br>
            <span style="background: rgba(0, 0, 0, 0.3); padding: 4px 8px; border-radius: 5px; font-family: monospace; direction: ltr; display: inline-block; margin-top: 5px; color: #fff;">https://your-domain-name/u/(USERNAME)</span>
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
            const card = document.getElementById('card-' + id);
            if (body.style.display === 'block') {
                body.style.display = 'none';
                body.classList.remove('open');
                card.classList.remove('active');
            } else {
                body.style.display = 'block';
                body.classList.add('open');
                card.classList.add('active');
            }
        }
        function openEditModal(id, name) {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('edit_uid').value = id;
            document.getElementById('edit_name').value = name;
        }
    </script>
</body>
</html>
