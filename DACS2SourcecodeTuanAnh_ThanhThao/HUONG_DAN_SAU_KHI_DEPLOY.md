# Hướng Dẫn Sau Khi Deploy Lên InfinityFree

## 📋 Mục Lục
1. [Kiểm Tra Cấu Hình](#1-kiểm-tra-cấu-hình)
2. [Cấu Hình Database](#2-cấu-hình-database)
3. [Cấu Hình Email/SMTP](#3-cấu-hình-emailsmtp)
4. [Cấu Hình Google OAuth](#4-cấu-hình-google-oauth)
5. [Cấu Hình Hugging Face AI](#5-cấu-hình-hugging-face-ai)
6. [Kiểm Tra Website](#6-kiểm-tra-website)
7. [Cấu Hình Cron Jobs](#7-cấu-hình-cron-jobs)
8. [Bảo Mật](#8-bảo-mật)
9. [Xử Lý Lỗi Thường Gặp](#9-xử-lý-lỗi-thường-gặp)

---

## 1. Kiểm Tra Cấu Hình

### 1.1. Kiểm tra file `includes/config.php`

Sau khi upload code, bạn **PHẢI** sửa file `includes/config.php` với thông tin của InfinityFree:

```php
// MySQL Configuration
define('DB_HOST', 'sqlXXX.infinityfree.com'); // Thay XXX bằng số của bạn
define('DB_USER', 'if0_40750024'); // Username database của bạn
define('DB_PASS', 'YOUR_DB_PASSWORD'); // Password database của bạn
define('DB_NAME_MYSQL', 'if0_40750024_hoa'); // Tên database của bạn

// Site Configuration
define('SITE_URL', 'https://yourdomain.epizy.com'); // URL website của bạn

// Email/SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com'); // Email Gmail của bạn
define('SMTP_PASS', 'your-app-password'); // App Password từ Gmail
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'BookOnline');

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'your-google-client-id');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');

// Hugging Face AI (nếu muốn dùng)
// define('HUGGINGFACE_API_KEY', 'your-huggingface-api-key');
```

### 1.2. Lấy thông tin Database từ InfinityFree

1. Đăng nhập vào **InfinityFree Control Panel**
2. Vào **MySQL Databases**
3. Copy các thông tin:
   - **Database Host**: `sqlXXX.infinityfree.com`
   - **Database Username**: `if0_40750024`
   - **Database Name**: `if0_40750024_hoa`
   - **Database Password**: (password bạn đã set)

---

## 2. Cấu Hình Database

### 2.1. Import Database

1. Đăng nhập vào **phpMyAdmin** từ InfinityFree Control Panel
2. Chọn database `if0_40750024_hoa`
3. Click tab **Import**
4. Chọn file `database/DEPLOY_FOR_INFINITYFREE.sql`
5. Click **Go** để import
6. Đợi đến khi thấy thông báo "Import has been successfully finished"

### 2.2. Kiểm Tra Database

Sau khi import, kiểm tra các bảng đã được tạo:

```sql
SHOW TABLES;
```

Bạn sẽ thấy các bảng:
- `users`
- `books`
- `user_books`
- `reading_progress`
- `coins_transactions`
- `user_inventory`
- `bookshelf_layouts`
- `quiz_attempts`
- `verification_codes`
- `admin_logs`
- `email_logs`

### 2.3. Kiểm Tra Admin Account

Admin mặc định:
- **Email**: `admin@bookonline.com`
- **Password**: `password`

⚠️ **QUAN TRỌNG**: Sau khi deploy, hãy đổi password admin ngay!

---

## 3. Cấu Hình Email/SMTP

### 3.1. Tạo App Password cho Gmail

1. Vào [Google Account](https://myaccount.google.com/)
2. **Security** → **2-Step Verification** (bật nếu chưa bật)
3. **Security** → **App passwords**
4. Chọn **Mail** và **Other (Custom name)**
5. Nhập tên: `BookOnline`
6. Copy **App Password** (16 ký tự)

### 3.2. Cập Nhật Config

Sửa `includes/config.php`:

```php
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'xxxx xxxx xxxx xxxx'); // App Password (16 ký tự, có thể có dấu cách)
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
```

### 3.3. Test Email

1. Đăng nhập vào website
2. Vào Admin Panel → **Gửi Email**
3. Gửi test email cho chính mình
4. Kiểm tra inbox

---

## 4. Cấu Hình Google OAuth

### 4.1. Tạo OAuth Credentials

1. Vào [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới hoặc chọn project hiện có
3. **APIs & Services** → **Credentials**
4. **Create Credentials** → **OAuth client ID**
5. Chọn **Web application**
6. **Authorized redirect URIs**: 
   ```
   https://yourdomain.epizy.com/api/google-auth.php
   ```
7. Copy **Client ID** và **Client Secret**

### 4.2. Cập Nhật Config

Sửa `includes/config.php`:

```php
define('GOOGLE_CLIENT_ID', 'your-client-id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'your-client-secret');
```

### 4.3. Test Google Login

1. Vào trang đăng nhập
2. Click **Đăng nhập bằng Google**
3. Chọn tài khoản Google
4. Kiểm tra xem có đăng nhập thành công không

---

## 5. Cấu Hình Hugging Face AI (Tùy Chọn)

### 5.1. Lấy API Key

1. Đăng ký tại [Hugging Face](https://huggingface.co/)
2. Vào **Settings** → **Access Tokens**
3. **New token** → Copy token

### 5.2. Cập Nhật Config

Sửa `api/quiz.php` dòng 22:

```php
define('HUGGINGFACE_API_KEY', 'hf_your_token_here');
```

⚠️ **Lưu ý**: Nếu không có API key, AI Quiz vẫn hoạt động với fallback mechanism.

---

## 6. Kiểm Tra Website

### 6.1. Kiểm Tra Các Trang Chính

1. **Trang chủ**: `https://yourdomain.epizy.com/`
2. **Đăng ký**: `https://yourdomain.epizy.com/register.php`
3. **Đăng nhập**: `https://yourdomain.epizy.com/login.php`
4. **Dashboard**: `https://yourdomain.epizy.com/dashboard.php`
5. **Admin Panel**: `https://yourdomain.epizy.com/admin/index.php`

### 6.2. Kiểm Tra Chức Năng

- [ ] Đăng ký tài khoản mới
- [ ] Xác nhận email
- [ ] Đăng nhập
- [ ] Xem sách
- [ ] Đọc sách
- [ ] Mua sách (shop)
- [ ] Làm quiz
- [ ] Xem kệ sách 3D
- [ ] Admin panel

### 6.3. Kiểm Tra Lỗi

Mở **Browser Console** (F12) và kiểm tra:
- Không có lỗi JavaScript
- Không có lỗi 404 (file không tìm thấy)
- Không có lỗi 500 (server error)

---

## 7. Cấu Hình Cron Jobs

### 7.1. Tạo Cron Job cho Email Reminders

1. Vào **InfinityFree Control Panel**
2. **Cron Jobs** → **Add Cron Job**
3. Cấu hình:
   - **Command**: `php /home/volXXX_XXX/epizy_XXX/public_html/cron/send-email-reminders.php`
   - **Schedule**: `0 9 * * *` (9:00 AM mỗi ngày)
4. **Save**

### 7.2. Test Cron Job

Sau khi tạo, đợi 1 ngày và kiểm tra:
- Email logs trong Admin Panel
- Users nhận được email reminder

---

## 8. Bảo Mật

### 8.1. Đổi Password Admin

1. Đăng nhập vào Admin Panel
2. Vào **Users** → Tìm admin account
3. Click **Edit** → Đổi password
4. Hoặc dùng SQL:

```sql
UPDATE users 
SET password_hash = '$2y$10$NEW_HASH_HERE' 
WHERE email = 'admin@bookonline.com';
```

### 8.2. Kiểm Tra File .htaccess

Đảm bảo file `.htaccess` có các rules bảo mật:

```apache
# Protect sensitive files
<FilesMatch "\.(sql|md|txt|log)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect config files
<FilesMatch "config\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 8.3. Kiểm Tra Permissions

Đảm bảo các thư mục có permissions đúng:
- Files: `644`
- Folders: `755`
- `assets/uploads/`: `755` (có thể ghi)

---

## 9. Xử Lý Lỗi Thường Gặp

### 9.1. Lỗi "Database connection failed"

**Nguyên nhân**: Thông tin database trong `config.php` sai

**Giải pháp**:
1. Kiểm tra lại `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME_MYSQL`
2. Đảm bảo database đã được tạo trong InfinityFree
3. Test connection bằng phpMyAdmin

### 9.2. Lỗi "Email không gửi được"

**Nguyên nhân**: SMTP config sai hoặc App Password không đúng

**Giải pháp**:
1. Kiểm tra lại `SMTP_USER` và `SMTP_PASS`
2. Đảm bảo đã bật 2-Step Verification trên Gmail
3. Sử dụng App Password, không dùng password thường
4. Kiểm tra email logs trong Admin Panel

### 9.3. Lỗi "404 Not Found"

**Nguyên nhân**: File không tồn tại hoặc path sai

**Giải pháp**:
1. Kiểm tra file có tồn tại không
2. Kiểm tra case-sensitive (Linux phân biệt hoa/thường)
3. Kiểm tra `.htaccess` có block file không

### 9.4. Lỗi "500 Internal Server Error"

**Nguyên nhân**: PHP error hoặc permission issue

**Giải pháp**:
1. Kiểm tra error logs trong InfinityFree Control Panel
2. Kiểm tra PHP version (cần PHP 7.4+)
3. Kiểm tra file permissions

### 9.5. Lỗi "Session cannot be started"

**Nguyên nhân**: Session path không có quyền ghi

**Giải pháp**:
1. Kiểm tra `session.save_path` trong `config.php`
2. Tạo thư mục `tmp/` và set permission `755`

---

## 10. Checklist Cuối Cùng

Trước khi website chính thức hoạt động, kiểm tra:

- [ ] Database đã import thành công
- [ ] Config.php đã được cập nhật với thông tin đúng
- [ ] Admin password đã được đổi
- [ ] Email/SMTP đã test thành công
- [ ] Google OAuth đã hoạt động
- [ ] Tất cả các trang chính đều load được
- [ ] Không có lỗi JavaScript trong console
- [ ] Không có lỗi PHP trong error logs
- [ ] Cron job đã được setup
- [ ] File .htaccess đã được cấu hình
- [ ] Permissions đã được set đúng

---

## 11. Liên Hệ Hỗ Trợ

Nếu gặp vấn đề, kiểm tra:
1. **Error Logs**: InfinityFree Control Panel → Error Logs
2. **PHP Logs**: InfinityFree Control Panel → PHP Logs
3. **Browser Console**: F12 → Console tab
4. **Network Tab**: F12 → Network tab (xem API calls)

---

## ✅ Hoàn Thành!

Sau khi hoàn thành tất cả các bước trên, website của bạn đã sẵn sàng để sử dụng!

**Chúc bạn thành công! 🎉**

