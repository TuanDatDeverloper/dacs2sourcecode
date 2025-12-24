# 🚀 Hướng Dẫn Deploy Lên InfinityFree

## 📋 Mục Lục
1. [Chuẩn Bị](#1-chuẩn-bị)
2. [Upload Code](#2-upload-code)
3. [Cấu Hình Database](#3-cấu-hình-database)
4. [Cấu Hình Website](#4-cấu-hình-website)
5. [Kiểm Tra](#5-kiểm-tra)
6. [Xử Lý Lỗi](#6-xử-lý-lỗi)

---

## 1. Chuẩn Bị

### 1.1. Tài Khoản InfinityFree
- Đăng ký tại: https://www.infinityfree.net/
- Đăng nhập vào Control Panel

### 1.2. Thông Tin Cần Chuẩn Bị
- **Domain**: `yourdomain.epizy.com` (hoặc domain riêng)
- **Database Name**: `if0_40750024_hoa` (hoặc tên database của bạn)
- **Database Username**: `if0_40750024` (hoặc username của bạn)
- **Database Password**: (password bạn đã set)
- **Database Host**: `sqlXXX.infinityfree.com` (XXX là số của bạn)

### 1.3. File Cần Upload
- Toàn bộ code trong folder `DACS2SourcecodeTuanAnh_ThanhThao/`
- File database: `database/DEPLOY_FOR_INFINITYFREE.sql`
- Tất cả file ảnh trong folder `images/`
- Tất cả file PDF trong folder `assets/uploads/books/`
- **Folder `khosach/`** - PDF sách gốc (quan trọng - xem `HUONG_DAN_DEPLOY_KHOSACH.md`)

---

## 2. Upload Code

### Cách 1: File Manager (Khuyến Nghị)

1. **Đăng nhập InfinityFree Control Panel**
2. Vào **File Manager**
3. Vào thư mục `htdocs` hoặc `public_html`
4. **Upload** tất cả file và folder từ `DACS2SourcecodeTuanAnh_ThanhThao/`
5. Đảm bảo cấu trúc thư mục:
   ```
   htdocs/
   ├── index.php
   ├── api/
   ├── includes/
   ├── images/
   ├── assets/
   ├── database/
   └── ...
   ```

### Cách 2: FTP

1. Lấy thông tin FTP từ Control Panel
2. Dùng FileZilla hoặc FTP client
3. Kết nối và upload toàn bộ code

---

## 3. Cấu Hình Database

### 3.1. Tạo Database (Nếu Chưa Có)

1. Vào **MySQL Databases** trong Control Panel
2. Tạo database mới (nếu chưa có)
3. Ghi nhớ:
   - Database Name
   - Database Username
   - Database Password
   - Database Host

### 3.2. Import Database

1. Vào **phpMyAdmin** từ Control Panel
2. **Chọn database** của bạn (ví dụ: `if0_40750024_hoa`)
3. Vào tab **Import**
4. Chọn file `database/DEPLOY_FOR_INFINITYFREE.sql`
5. Click **Go** để import
6. Đợi đến khi thấy "Import has been successfully finished"

### 3.3. Kiểm Tra Database

Chạy SQL để kiểm tra:
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
- `shop_items`

---

## 4. Cấu Hình Website

### 4.1. Sửa File `includes/config.php`

**QUAN TRỌNG**: Bạn **PHẢI** sửa file này sau khi upload!

1. Mở file `includes/config.php` trong File Manager
2. Sửa các thông tin sau:

```php
// MySQL Configuration
define('DB_HOST', 'sqlXXX.infinityfree.com'); // Thay XXX bằng số của bạn
define('DB_USER', 'if0_40750024'); // Username database của bạn
define('DB_PASS', 'YOUR_DB_PASSWORD'); // Password database của bạn
define('DB_NAME_MYSQL', 'if0_40750024_hoa'); // Tên database của bạn

// Site Configuration
define('SITE_URL', 'https://yourdomain.epizy.com'); // URL website của bạn (HTTPS)

// Email/SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com'); // Email Gmail của bạn
define('SMTP_PASS', 'your-app-password'); // App Password từ Gmail (16 ký tự)
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'BookOnline');

// Google OAuth Configuration (Tùy chọn)
define('GOOGLE_CLIENT_ID', 'your-google-client-id');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');
```

### 4.2. Lấy Thông Tin Database

1. Vào **MySQL Databases** trong Control Panel
2. Copy các thông tin:
   - **Database Host**: `sqlXXX.infinityfree.com`
   - **Database Username**: `if0_40750024`
   - **Database Name**: `if0_40750024_hoa`
   - **Database Password**: (password bạn đã set)

### 4.3. Tạo Gmail App Password

1. Vào [Google Account](https://myaccount.google.com/)
2. **Security** → **2-Step Verification** (bật nếu chưa bật)
3. **Security** → **App passwords**
4. Chọn **Mail** và **Other (Custom name)**
5. Nhập tên: `BookOnline`
6. Copy **App Password** (16 ký tự, có thể có dấu cách)

---

## 5. Kiểm Tra

### 5.1. Kiểm Tra Website

1. Truy cập: `https://yourdomain.epizy.com/`
2. Kiểm tra các trang:
   - Trang chủ: `/`
   - Đăng ký: `/register.php`
   - Đăng nhập: `/login.php`
   - Dashboard: `/dashboard.php`
   - Admin Panel: `/admin/index.php`

### 5.2. Kiểm Tra Database Connection

1. Đăng nhập vào website
2. Nếu đăng nhập thành công → Database OK
3. Nếu lỗi → Kiểm tra lại `config.php`

### 5.3. Kiểm Tra Ảnh Bìa Sách

1. Vào trang `/new-books.php`
2. Kiểm tra ảnh bìa có hiển thị không
3. Mở Console (F12) xem có lỗi 404 không

### 5.4. Kiểm Tra Email

1. Đăng ký tài khoản mới
2. Kiểm tra email có nhận được mã xác nhận không
3. Hoặc vào Admin Panel → **Gửi Email** → Test email

---

## 6. Xử Lý Lỗi

### Lỗi: "Database connection failed"

**Nguyên nhân**: Thông tin database trong `config.php` sai

**Giải pháp**:
1. Kiểm tra lại `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME_MYSQL`
2. Đảm bảo database đã được tạo trong InfinityFree
3. Test connection bằng phpMyAdmin

### Lỗi: "404 Not Found"

**Nguyên nhân**: File không tồn tại hoặc path sai

**Giải pháp**:
1. Kiểm tra file có tồn tại không
2. Kiểm tra case-sensitive (Linux phân biệt hoa/thường)
3. Kiểm tra `.htaccess` có block file không

### Lỗi: "500 Internal Server Error"

**Nguyên nhân**: PHP error hoặc permission issue

**Giải pháp**:
1. Kiểm tra error logs trong InfinityFree Control Panel
2. Kiểm tra PHP version (cần PHP 7.4+)
3. Kiểm tra file permissions (644 cho files, 755 cho folders)

### Lỗi: "Email không gửi được"

**Nguyên nhân**: SMTP config sai hoặc App Password không đúng

**Giải pháp**:
1. Kiểm tra lại `SMTP_USER` và `SMTP_PASS`
2. Đảm bảo đã bật 2-Step Verification trên Gmail
3. Sử dụng App Password, không dùng password thường

### Lỗi: "Ảnh bìa không hiển thị (404)"

**Nguyên nhân**: File ảnh chưa được upload hoặc đường dẫn sai

**Giải pháp**:
1. Upload tất cả file ảnh từ folder `images/` lên server
2. Kiểm tra database có `cover_url` dạng `/images/tên-file.jpg`
3. Test truy cập trực tiếp: `https://yourdomain.epizy.com/images/tên-file.jpg`

---

## 7. Checklist Cuối Cùng

Trước khi website chính thức hoạt động:

- [ ] Code đã được upload lên server
- [ ] Database đã được import thành công
- [ ] File `config.php` đã được cập nhật với thông tin đúng
- [ ] Tất cả file ảnh đã được upload lên folder `images/`
- [ ] Tất cả file PDF đã được upload lên folder `assets/uploads/books/`
- [ ] Admin password đã được đổi (mặc định: `password`)
- [ ] Email/SMTP đã test thành công
- [ ] Tất cả các trang chính đều load được
- [ ] Không có lỗi JavaScript trong console
- [ ] Không có lỗi PHP trong error logs
- [ ] Ảnh bìa sách hiển thị đúng

---

## 8. Thông Tin Quan Trọng

### Admin Account Mặc Định
- **Email**: `admin@bookonline.com`
- **Password**: `password`

⚠️ **QUAN TRỌNG**: Đổi password ngay sau khi deploy!

### Database Info
- **File SQL**: `database/DEPLOY_FOR_INFINITYFREE.sql`
- **Database Name**: `if0_40750024_hoa` (hoặc của bạn)
- **Tất cả bảng đã được tạo tự động**

### File Permissions
- **Files**: `644`
- **Folders**: `755`
- **Upload folders**: `755` (có thể ghi)

---

## 9. Hỗ Trợ

Nếu gặp vấn đề:
1. Kiểm tra **Error Logs** trong InfinityFree Control Panel
2. Kiểm tra **PHP Logs** trong Control Panel
3. Mở **Browser Console** (F12) → Console tab
4. Mở **Network Tab** (F12) → Xem API calls

---

## ✅ Hoàn Thành!

Sau khi hoàn thành tất cả các bước trên, website của bạn đã sẵn sàng để sử dụng!

**Chúc bạn deploy thành công! 🎉**

