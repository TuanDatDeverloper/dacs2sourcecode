# 🐛 Debug: Website Không Chạy

## 🔍 Kiểm Tra Nhanh

### Bước 1: Chạy Script Kiểm Tra

1. **Upload file `check-deploy.php` lên server** (vào `/htdocs/`)
2. **Truy cập:** `https://mapprod.great-site.net/check-deploy.php`
3. **Xem kết quả** - Script sẽ cho biết lỗi cụ thể

---

## ⚠️ Các Lỗi Thường Gặp

### Lỗi 1: Vẫn Thấy Trang "Your domain is ready!"

**Nguyên nhân:**
- File `index2.html` vẫn còn
- File `index.php` chưa có ở root

**Giải pháp:**
1. Vào File Manager
2. Vào `/htdocs/`
3. **Xóa file `index2.html`**
4. **Kiểm tra có file `index.php` không?**
   - Nếu không có → Upload file `index.php` lên `/htdocs/`
   - Nếu có trong subfolder → Di chuyển lên root

---

### Lỗi 2: 404 Not Found

**Nguyên nhân:**
- Code vẫn nằm trong subfolder
- File không tồn tại

**Giải pháp:**
1. Vào File Manager
2. Kiểm tra cấu trúc:
   - ❌ `/htdocs/DACS2SourcecodeTuanAnh_ThanhThao/index.php` (SAI)
   - ✅ `/htdocs/index.php` (ĐÚNG)

3. **Nếu code ở subfolder:**
   - Vào folder `DACS2SourcecodeTuanAnh_ThanhThao/`
   - Select All
   - Cut/Move
   - Quay lại `/htdocs/`
   - Paste/Move Here

---

### Lỗi 3: 500 Internal Server Error

**Nguyên nhân:**
- Config.php sai
- Database connection failed
- PHP error

**Giải pháp:**
1. **Xem Error Logs:**
   - Control Panel → Error Logs
   - Copy lỗi cụ thể

2. **Kiểm tra config.php:**
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com'); // ✅ Đã sửa?
   define('DB_USER', 'if0_40750024'); // ✅ Đã sửa?
   define('DB_PASS', 'YOUR_PASSWORD'); // ✅ Đã sửa?
   define('DB_NAME_MYSQL', 'if0_40750024_hoa'); // ✅ Đã sửa?
   define('SITE_URL', 'https://mapprod.great-site.net'); // ✅ Đã sửa?
   ```

3. **Kiểm tra database:**
   - Vào phpMyAdmin
   - Kiểm tra database đã được import chưa

---

### Lỗi 4: Blank Page (Trang Trắng)

**Nguyên nhân:**
- PHP error nhưng không hiển thị
- Config.php có lỗi syntax

**Giải pháp:**
1. **Bật error display tạm thời:**
   - Sửa `includes/config.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

2. **Xem Error Logs:**
   - Control Panel → Error Logs

---

## 📋 Checklist Debug

- [ ] Đã xóa file `index2.html`?
- [ ] File `index.php` có ở `/htdocs/index.php` không?
- [ ] Code có nằm trong subfolder không?
- [ ] `config.php` đã sửa `SITE_URL` thành `https://mapprod.great-site.net`?
- [ ] `config.php` đã sửa thông tin database?
- [ ] Database đã được import?
- [ ] Đã chạy `check-deploy.php` để kiểm tra?

---

## 🔧 Các Bước Sửa Lỗi

### Bước 1: Xóa File Mặc Định

1. Vào File Manager
2. Vào `/htdocs/`
3. **Xóa `index2.html`**

### Bước 2: Di Chuyển Code Lên Root

1. Vào File Manager
2. Nếu code ở `/htdocs/DACS2SourcecodeTuanAnh_ThanhThao/`:
   - Vào folder đó
   - Select All
   - Cut
   - Quay lại `/htdocs/`
   - Paste
   - Xóa folder rỗng

### Bước 3: Sửa Config.php

1. Mở `includes/config.php`
2. Sửa:
   ```php
   define('SITE_URL', 'https://mapprod.great-site.net');
   ```
3. Sửa thông tin database
4. Save

### Bước 4: Test

1. Truy cập: `https://mapprod.great-site.net/`
2. Nếu vẫn lỗi → Chạy `check-deploy.php`
3. Xem Error Logs

---

## 🆘 Nếu Vẫn Không Được

1. **Chạy script kiểm tra:**
   - Upload `check-deploy.php` lên `/htdocs/`
   - Truy cập: `https://mapprod.great-site.net/check-deploy.php`
   - Xem kết quả

2. **Xem Error Logs:**
   - Control Panel → Error Logs
   - Copy lỗi và gửi cho tôi

3. **Kiểm tra Browser Console:**
   - F12 → Console tab
   - Xem lỗi JavaScript

4. **Kiểm tra Network Tab:**
   - F12 → Network tab
   - Xem API calls có lỗi không

---

**Hãy làm theo từng bước trên và cho tôi biết kết quả!**

