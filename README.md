<div align="left" dir="ltr">
  <a href="README.md"><img src="https://img.shields.io/badge/English-007ec6?style=for-the-badge" alt="English"></a>
  <a href="README-Fa.md"><img src="https://img.shields.io/badge/Persian-2ea44f?style=for-the-badge" alt="Persian"></a>
</div>

<div align="center">

# 🚀 Subscription Link Management System

**A lightweight, fast, and secure system for managing users and subscription links (V2Ray, Xray, etc.)**

</div>

---

This project is a lightweight and secure system written in **PHP** using an **SQLite** database. It requires zero complex database setups (like MySQL) and runs smoothly on almost all free and shared hosting platforms.

---

## ✨ Features

### 👤 User Panel
*   **Mobile-First Design:** Fully responsive UI for mobile and desktop screens.
*   **Modern UI:** Beautiful glassmorphism design with a dark mode aesthetic.
*   **Link Display:** Shows subscription configurations in distinct, elegant cards.
*   **Quick Tools:** Dedicated **Copy** and **QR Code** buttons for each link.
*   **Security:** Users must enter their password to access their private panel.

### 🛡️ Admin Panel
*   **Dual Language Support:** Fully bilingual admin panel (English LTR / Persian RTL) with smart UI mirroring.
*   **Tabbed Dashboard:** Clean separation for user list, user creation, and admin settings.
*   **Full Editing Capabilities:** Edit usernames, passwords, and **directly modify existing links** without deleting them.
*   **Smart Links:** Auto-generates unique user URLs based on the current domain with a 1-click copy button.
*   **Smart Accordions:** Displays user links in collapsible drawers. Uses SessionStorage to keep drawers open even after page reloads or edits.
*   **Admin Settings:** Dedicated tab to change the admin username and password directly from the panel.
*   **Auto-Sort:** Users list is automatically sorted alphabetically (A-Z) for quicker access.

### ⚙️ Technical & Infrastructure
*   **Zero Database Setup:** Powered by **SQLite** (the database is just a file created automatically).
*   **Clean URLs:** Uses `.htaccess` to remove `.php` extensions from URLs.
*   **Lightweight & Fast:** Built with pure PHP and native CSS/JS (No heavy frameworks).

---

## 🛠️ Requirements

*   Linux Hosting with Apache Web Server (for `.htaccess` support).
*   **PHP 8.0** or higher.
*   PHP Extensions: `pdo_sqlite` (enabled by default on 99% of hosts).

---

## 🚀 Deployment Guide

This project is highly optimized for free hosting platforms like **InfinityFree**.

### Installation on InfinityFree (or similar hosts)

1.  **Download Files:** Download all files from this repository.
2.  **Open File Manager:** Log in to your hosting panel and open the `File Manager`.
3.  **Root Directory:**
    *   For **InfinityFree**, navigate to the `htdocs` folder.
    *   For cPanel hosts, navigate to the `public_html` folder.
4.  **Clean Up:** Delete any default files (like `index2.html` or `default.php`).
5.  **Upload:** Upload all project files (`admin.php`, `user.php`, `login.php`, `lang.php`, `style.css`, `admin.css`, etc.).
6.  **`.htaccess` File:** Ensure the `.htaccess` file is uploaded (it might be hidden by default in your OS/File Manager).
7.  **Background Image:** Upload a nice background image named `bg.jpg` in the root directory.

---

## 📖 How to Use

### 1. Initial Login
After uploading the files, visit your site's `/login` path:
`https://your-site.com/login`

*   **Default Username:** `admin`
*   **Default Password:** `123456`

> ⚠️ **IMPORTANT:** Immediately after your first login, go to "Admin Settings" and change the default password.

### 2. Create a User
1.  Go to the **"Add User"** tab in the admin panel.
2.  Enter a username and password for the new user.
3.  Click **"Create User"**.

### 3. Manage Links
1.  In the **"Users List"** tab, click on a user's name to expand their panel.
2.  To add a link, fill in the "Link Name" and "Subscription Link" fields, then click **"Add Link"**.
3.  Use the **Yellow (Edit)** and **Red (Delete)** buttons to modify existing links.

### 4. Deliver to User
The unique URL for the user will look like this (also displayed at the top of their card):
`https://your-site.com/u/USERNAME`

The user can open this link, enter their password, and access their configurations.

---

## 📂 File Structure

*   `admin.php`: Admin panel for managing users, links, and settings.
*   `user.php`: User-facing panel for viewing, copying, and generating QR codes for links.
*   `login.php`: Secure login page for the admin.
*   `lang.php`: Translation system for the dual-language admin panel.
*   `db.php`: SQLite database connection and auto-table generation setup.
*   `style.css`: Global styles and glassmorphism UI for the user panel.
*   `admin.css`: Dedicated, responsive styles for the admin panel.
*   `.htaccess`: URL rewriting rules and database security.
*   `logout.php`: Secure logout script.

---

## 📸 Screenshots

<table>
  <tr>
    <td width="50%">
      <img src="images/11.jpg" alt="نمای پنل مدیریت (فارسی)">
    </td>
    <td width="50%">
      <img src="images/12.jpg" alt="نمای پنل مدیریت (انگلیسی)">
    </td>
  </tr>
  <tr>
    <td width="50%">
      <img src="images/13.jpg" alt="ویرایش کاربر و لینک‌ها">
    </td>
    <td width="50%">
      <img src="images/14.jpg" alt="تنظیمات ادمین">
    </td>
  </tr>
    <tr>
    <td width="50%">
      <img src="images/15.jpg" alt="ویرایش کاربر و لینک‌ها">
    </td>
    <td width="50%">
      <img src="images/16.jpg" alt="تنظیمات ادمین">
    </td>
  </tr>
   <tr>
    <td width="50%">
      <img src="images/17.jpg" alt="صفحه ورود کاربر">
    </td>
    <td width="50%">
      <img src="images/18.jpg" alt="پنل اختصاصی کاربر (دریافت لینک)">
    </td>
  </tr>
</table>

## ❤️ Support

This project was built to make configuration sharing simple, beautiful, and secure. If you found it helpful, please consider giving it a ⭐️ on GitHub!

**Made with Love & PHP**

---

<div align="left" dir="ltr">

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
<br>
This project is licensed under the **MIT License**.

</div>
