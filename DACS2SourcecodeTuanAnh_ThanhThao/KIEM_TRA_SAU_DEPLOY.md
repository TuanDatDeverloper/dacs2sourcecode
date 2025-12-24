# 🔍 Kiểm Tra Sau Khi Deploy - Website Không Chạy

## ⚠️ Vấn Đề Thường Gặp

### 1. Code Nằm Trong Subfolder (Phổ Biến Nhất!)

**Vấn đề:** Code đang ở `/htdocs/DACS2SourcecodeTuanAnh_ThanhThao/` thay vì `/htdocs/`

**Giải pháp:**

#### Cách 1: Di Chuyển File Lên Root (Khuyến Nghị)

1. Trong File Manager, vào folder `DACS2SourcecodeTuanAnh_ThanhThao`
2. **Select All** (Ctrl+A hoặc click "Select All")
3. Click **Cut** hoặc **Move**
4. Quay lại folder `htdocs` (parent folder)
5. Click **Paste** hoặc **Move Here**
6. Xóa folder `DACS2SourcecodeTuanAnh_ThanhThao` rỗng (nếu còn)

**Kết quả:** Tất cả file sẽ ở `/htdocs/` thay vì `/htdocs/DACS2SourcecodeTuanAnh_ThanhThao/`

#### Cách 2: Giữ Nguyên Subfolder (Nếu muốn)

Nếu muốn giữ code trong subfolder, cần sửa `config.php`:

```php
define('SITE_URL', 'https://yourdomain.epizy.com/DACS2SourcecodeTuanAnh_ThanhThao');
```

---

### 2. Config.php Chưa Được Sửa

**Kiểm tra:**
1. Mở file `includes/config.php`
2. Kiểm tra các dòng sau:

```php
// Phải sửa thành thông tin của bạn
define('DB_HOST', 'sqlXXX.infinityfree.com'); // ✅ Đã sửa chưa?
define('DB_USER', 'if0_40750024'); // ✅ Đã sửa chưa?
define('DB_PASS', 'YOUR_PASSWORD'); // ✅ Đã sửa chưa?
define('DB_NAME_MYSQL', 'if0_40750024_hoa'); // ✅ Đã sửa chưa?
define('SITE_URL', 'https://yourdomain.epizy.com'); // ✅ Đã sửa chưa?
```

**Nếu chưa sửa:**
- Sửa ngay các thông tin trên
- Lưu file

---

### 3. Database Chưa Được Import

**Kiểm tra:**
1. Vào **phpMyAdmin**
2. Chọn database của bạn
3. Chạy SQL: `SHOW TABLES;`
4. Kiểm tra có các bảng: `users`, `books`, `user_books`, etc.

**Nếu chưa có bảng:**
1. Vào tab **Import**
2. Chọn file `database/DEPLOY_FOR_INFINITYFREE.sql`
3. Click **Go**

---

### 4. Lỗi 404 Not Found

**Nguyên nhân:**
- File không tồn tại
- Path sai
- Code nằm trong subfolder

**Giải pháp:**
- Di chuyển file lên root (xem mục 1)
- Hoặc sửa SITE_URL (xem mục 1 - Cách 2)

---

### 5. Lỗi 500 Internal Server Error

**Nguyên nhân:**
- PHP error
- Config.php sai
- Database connection failed

**Kiểm tra:**
1. Vào **Error Logs** trong Control Panel
2. Xem lỗi cụ thể
3. Sửa theo lỗi

---

### 6. Lỗi "Database connection failed"

**Nguyên nhân:**
- Thông tin database trong `config.php` sai
- Database chưa được tạo

**Giải pháp:**
1. Kiểm tra lại `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME_MYSQL`
2. Đảm bảo database đã được tạo trong Control Panel
3. Test connection bằng phpMyAdmin

---

## 🔧 Các Bước Kiểm Tra Nhanh

### Bước 1: Kiểm Tra Cấu Trúc Thư Mục

Trong File Manager, kiểm tra:
- [ ] File `index.php` có ở `/htdocs/index.php` không?
- [ ] Hay đang ở `/htdocs/DACS2SourcecodeTuanAnh_ThanhThao/index.php`?

**Nếu ở subfolder:**
- Di chuyển tất cả file lên `/htdocs/` (xem mục 1)

### Bước 2: Kiểm Tra Config.php

- [ ] `DB_HOST` đã sửa chưa?
- [ ] `DB_USER` đã sửa chưa?
- [ ] `DB_PASS` đã sửa chưa?
- [ ] `DB_NAME_MYSQL` đã sửa chưa?
- [ ] `SITE_URL` đã sửa chưa?

### Bước 3: Kiểm Tra Database

- [ ] Database đã được import chưa?
- [ ] Có bảng `users` không?

### Bước 4: Test Website

1. Truy cập: `https://yourdomain.epizy.com/`
2. Xem lỗi gì:
   - **404** → Di chuyển file lên root
   - **500** → Xem Error Logs
   - **Database error** → Sửa config.php
   - **Blank page** → Xem Error Logs

---

## 📝 Checklist Nhanh

- [ ] Code đã di chuyển lên `/htdocs/` (không còn trong subfolder)
- [ ] `config.php` đã sửa đúng thông tin database
- [ ] `config.php` đã sửa `SITE_URL` đúng domain
- [ ] Database đã được import
- [ ] Test truy cập website → Không còn lỗi

---

## 🆘 Nếu Vẫn Không Chạy

1. **Xem Error Logs:**
   - Control Panel → Error Logs
   - Copy lỗi và gửi cho tôi

2. **Xem Browser Console:**
   - F12 → Console tab
   - Xem lỗi JavaScript

3. **Xem Network Tab:**
   - F12 → Network tab
   - Xem API calls có lỗi không

4. **Kiểm tra PHP Version:**
   - Control Panel → PHP Version
   - Cần PHP 7.4 trở lên

---

**Hãy kiểm tra từng bước trên và cho tôi biết bạn gặp lỗi gì cụ thể!**

