# ✅ Checklist Deploy Lên InfinityFree

## 📋 Bước 1: Chuẩn Bị

- [ ] Đã có tài khoản InfinityFree
- [ ] Đã đăng nhập vào Control Panel
- [ ] Đã tạo database (hoặc có thông tin database sẵn có)
- [ ] Đã có Gmail App Password (nếu muốn dùng email)
- [ ] Đã chuẩn bị tất cả file cần upload

---

## 📤 Bước 2: Upload Code

### 2.1. Upload qua File Manager

1. [ ] Vào **File Manager** trong Control Panel
2. [ ] Vào thư mục `htdocs` hoặc `public_html`
3. [ ] Upload **TẤT CẢ** file và folder từ `DACS2SourcecodeTuanAnh_ThanhThao/`
4. [ ] Đảm bảo cấu trúc thư mục đúng:
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

### 2.2. Upload Files Quan Trọng

- [ ] Upload tất cả file PHP
- [ ] Upload folder `images/` (chứa ảnh bìa sách)
- [ ] Upload folder `assets/uploads/books/` (chứa PDF sách)
- [ ] Upload folder `vendor/` (PHPMailer)
- [ ] Upload file `database/DEPLOY_FOR_INFINITYFREE.sql`

---

## 🗄️ Bước 3: Import Database

1. [ ] Vào **phpMyAdmin** từ Control Panel
2. [ ] **Chọn database** của bạn (ví dụ: `if0_40750024_hoa`)
3. [ ] Vào tab **Import**
4. [ ] Chọn file `database/DEPLOY_FOR_INFINITYFREE.sql`
5. [ ] Click **Go** để import
6. [ ] Đợi đến khi thấy "Import has been successfully finished"
7. [ ] Kiểm tra các bảng đã được tạo: `SHOW TABLES;`

---

## ⚙️ Bước 4: Cấu Hình Website

### 4.1. Lấy Thông Tin Database

1. [ ] Vào **MySQL Databases** trong Control Panel
2. [ ] Copy các thông tin:
   - [ ] **Database Host**: `sqlXXX.infinityfree.com`
   - [ ] **Database Username**: `if0_40750024` (hoặc của bạn)
   - [ ] **Database Name**: `if0_40750024_hoa` (hoặc của bạn)
   - [ ] **Database Password**: (password bạn đã set)

### 4.2. Sửa File `includes/config.php`

1. [ ] Mở file `includes/config.php` trong File Manager
2. [ ] Sửa các thông tin sau:

```php
// MySQL Configuration
define('DB_HOST', 'sqlXXX.infinityfree.com'); // ✅ Đã sửa
define('DB_USER', 'if0_40750024'); // ✅ Đã sửa
define('DB_PASS', 'YOUR_DB_PASSWORD'); // ✅ Đã sửa
define('DB_NAME_MYSQL', 'if0_40750024_hoa'); // ✅ Đã sửa

// Site Configuration
define('SITE_URL', 'https://yourdomain.epizy.com'); // ✅ Đã sửa (HTTPS)
```

3. [ ] Lưu file

### 4.3. Cấu Hình Email (Tùy Chọn)

1. [ ] Tạo Gmail App Password (nếu chưa có)
2. [ ] Sửa trong `includes/config.php`:

```php
define('SMTP_USER', 'your-email@gmail.com'); // ✅ Đã sửa
define('SMTP_PASS', 'your-app-password'); // ✅ Đã sửa (16 ký tự)
define('SMTP_FROM_EMAIL', 'your-email@gmail.com'); // ✅ Đã sửa
```

---

## 🧪 Bước 5: Kiểm Tra

### 5.1. Kiểm Tra Website

- [ ] Truy cập: `https://yourdomain.epizy.com/`
- [ ] Trang chủ load được
- [ ] Không có lỗi 500 Internal Server Error
- [ ] Không có lỗi database connection

### 5.2. Kiểm Tra Đăng Nhập

- [ ] Vào `/register.php` → Đăng ký tài khoản mới
- [ ] Vào `/login.php` → Đăng nhập thành công
- [ ] Admin login: `admin@bookonline.com` / `password`

### 5.3. Kiểm Tra Ảnh Bìa

- [ ] Vào `/new-books.php`
- [ ] Ảnh bìa sách hiển thị đúng
- [ ] Không có lỗi 404 trong Console (F12)

### 5.4. Kiểm Tra Email (Nếu đã cấu hình)

- [ ] Đăng ký tài khoản mới
- [ ] Kiểm tra email có nhận được mã xác nhận không
- [ ] Hoặc vào Admin Panel → Test email

---

## 🔒 Bước 6: Bảo Mật

- [ ] Đổi password admin (mặc định: `password`)
- [ ] Kiểm tra file `.htaccess` có bảo vệ config.php không
- [ ] Kiểm tra permissions (files: 644, folders: 755)

---

## ✅ Hoàn Thành

- [ ] Tất cả các bước trên đã hoàn thành
- [ ] Website hoạt động bình thường
- [ ] Không có lỗi trong Console (F12)
- [ ] Không có lỗi trong Error Logs

---

## 📞 Nếu Gặp Lỗi

1. Kiểm tra **Error Logs** trong Control Panel
2. Kiểm tra **PHP Logs** trong Control Panel
3. Mở **Browser Console** (F12) → Xem lỗi
4. Mở **Network Tab** (F12) → Xem API calls

---

**Chúc bạn deploy thành công! 🎉**

