# 🔧 Sửa Lỗi: Vẫn Hiển Thị Trang Mặc Định InfinityFree

## ⚠️ Vấn Đề

Bạn đã sửa `config.php` nhưng vẫn thấy trang "Your domain is ready!" của InfinityFree.

**Domain của bạn:** `mapprod.great-site.net`

---

## ✅ Giải Pháp

### Bước 1: Xóa File `index2.html`

**QUAN TRỌNG:** InfinityFree tạo file `index2.html` mặc định, file này được ưu tiên hiển thị!

1. **Vào File Manager**
2. **Vào folder `/htdocs/`** (root folder)
3. **Tìm file `index2.html`**
4. **Xóa file này** (click Delete)

---

### Bước 2: Kiểm Tra File `index.php` Có Ở Root Không

1. **Vào File Manager**
2. **Vào folder `/htdocs/`**
3. **Kiểm tra có file `index.php` không?**

**Nếu KHÔNG có:**
- Code đang nằm trong subfolder `DACS2SourcecodeTuanAnh_ThanhThao/`
- Cần di chuyển file `index.php` lên `/htdocs/`

**Cách di chuyển:**
1. Vào folder `DACS2SourcecodeTuanAnh_ThanhThao/`
2. Tìm file `index.php`
3. Click **Cut** hoặc **Move**
4. Quay lại folder `htdocs/`
5. Click **Paste** hoặc **Move Here**

---

### Bước 3: Di Chuyển TẤT CẢ File Lên Root (Khuyến Nghị)

**Nếu code vẫn nằm trong subfolder:**

1. **Vào folder `DACS2SourcecodeTuanAnh_ThanhThao/`**
2. **Select All** (Ctrl+A hoặc click "Select All")
3. **Click "Cut" hoặc "Move"**
4. **Quay lại folder `htdocs/`** (parent folder)
5. **Click "Paste" hoặc "Move Here"**
6. **Xóa folder `DACS2SourcecodeTuanAnh_ThanhThao` rỗng**

**Kết quả:** Tất cả file sẽ ở `/htdocs/` thay vì trong subfolder.

---

### Bước 4: Kiểm Tra Config.php

1. **Mở file `includes/config.php`**
2. **Kiểm tra dòng `SITE_URL`:**

```php
// Phải là:
define('SITE_URL', 'https://mapprod.great-site.net');
```

**Lưu ý:**
- Phải dùng **HTTPS** (không phải HTTP)
- Không có dấu `/` ở cuối
- Đúng domain: `mapprod.great-site.net`

---

### Bước 5: Kiểm Tra Database Config

Đảm bảo đã sửa đúng thông tin database:

```php
define('DB_HOST', 'sqlXXX.infinityfree.com'); // ✅ Đã sửa?
define('DB_USER', 'if0_40750024'); // ✅ Đã sửa?
define('DB_PASS', 'YOUR_PASSWORD'); // ✅ Đã sửa?
define('DB_NAME_MYSQL', 'if0_40750024_hoa'); // ✅ Đã sửa?
```

---

## 📋 Checklist

- [ ] Đã xóa file `index2.html` trong `/htdocs/`
- [ ] File `index.php` đã có ở `/htdocs/index.php` (không phải trong subfolder)
- [ ] Tất cả file đã được di chuyển lên `/htdocs/`
- [ ] `config.php` đã sửa `SITE_URL` thành `https://mapprod.great-site.net`
- [ ] `config.php` đã sửa đúng thông tin database
- [ ] Database đã được import

---

## 🧪 Test Sau Khi Sửa

1. **Truy cập:** `https://mapprod.great-site.net/`
2. **Kết quả mong đợi:**
   - ✅ Thấy trang chủ BookOnline (không phải trang mặc định InfinityFree)
   - ✅ Có thể đăng nhập/đăng ký
   - ✅ Không có lỗi database

---

## 🆘 Nếu Vẫn Không Chạy

### Kiểm Tra Lỗi:

1. **Xem Error Logs:**
   - Control Panel → Error Logs
   - Copy lỗi và gửi cho tôi

2. **Xem Browser Console:**
   - F12 → Console tab
   - Xem lỗi JavaScript

3. **Kiểm Tra File Permissions:**
   - Files: `644`
   - Folders: `755`

---

## 📝 Lưu Ý Quan Trọng

1. **File `index2.html` phải được xóa** - Đây là nguyên nhân chính!
2. **File `index.php` phải ở root `/htdocs/`** - Không phải trong subfolder
3. **SITE_URL phải dùng HTTPS** - `https://mapprod.great-site.net`
4. **Không có dấu `/` ở cuối SITE_URL**

---

**Hãy làm theo từng bước trên, đặc biệt là Bước 1 (xóa index2.html)!**

