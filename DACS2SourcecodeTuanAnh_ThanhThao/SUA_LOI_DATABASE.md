# 🔧 Sửa Lỗi Database Connection Failed

## ⚠️ Vấn Đề

Bạn gặp lỗi **"Database connection failed. Please check your configuration."**

**Nguyên nhân:**
- Thông tin database trong `config.php` sai
- Database chưa được tạo
- Database chưa được import
- Password hoặc username sai

---

## ✅ Giải Pháp

### Bước 1: Chạy Script Test Database

1. **Upload file `test-database.php` lên server** (vào `/htdocs/`)
2. **Truy cập:** `https://mapprod.great-site.net/test-database.php`
3. **Xem kết quả** - Script sẽ cho biết lỗi cụ thể

---

### Bước 2: Lấy Thông Tin Database Từ InfinityFree

1. **Đăng nhập InfinityFree Control Panel**
2. **Vào "MySQL Databases"**
3. **Copy các thông tin:**
   - **Database Host**: `sqlXXX.infinityfree.com` (XXX là số của bạn)
   - **Database Username**: `if0_40750024` (hoặc của bạn)
   - **Database Name**: `if0_40750024_hoa` (hoặc của bạn)
   - **Database Password**: (password bạn đã set)

---

### Bước 3: Sửa File `includes/config.php`

1. **Mở file `includes/config.php`** trong File Manager
2. **Tìm và sửa các dòng sau:**

```php
// MySQL Configuration
define('DB_HOST', 'sqlXXX.infinityfree.com'); // ✅ Thay XXX bằng số của bạn
define('DB_USER', 'if0_40750024'); // ✅ Username database của bạn
define('DB_PASS', 'YOUR_DB_PASSWORD'); // ✅ Password database của bạn
define('DB_NAME_MYSQL', 'if0_40750024_hoa'); // ✅ Tên database của bạn
```

3. **Lưu file**

---

### Bước 4: Kiểm Tra Database Đã Được Tạo Chưa

1. **Vào phpMyAdmin** từ Control Panel
2. **Kiểm tra có database của bạn không?**
   - Nếu không có → Tạo database mới trong Control Panel

---

### Bước 5: Import Database

1. **Vào phpMyAdmin**
2. **Chọn database** của bạn (ví dụ: `if0_40750024_hoa`)
3. **Vào tab "Import"**
4. **Chọn file `database/DEPLOY_FOR_INFINITYFREE.sql`**
5. **Click "Go"** để import
6. **Đợi đến khi thấy "Import has been successfully finished"**

---

### Bước 6: Kiểm Tra Bảng Đã Được Tạo

1. **Trong phpMyAdmin**, chạy SQL:
   ```sql
   SHOW TABLES;
   ```

2. **Bạn sẽ thấy các bảng:**
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

## 📋 Checklist Sửa Lỗi Database

- [ ] Đã lấy thông tin database từ Control Panel
- [ ] Đã sửa `DB_HOST` trong `config.php`
- [ ] Đã sửa `DB_USER` trong `config.php`
- [ ] Đã sửa `DB_PASS` trong `config.php`
- [ ] Đã sửa `DB_NAME_MYSQL` trong `config.php`
- [ ] Database đã được tạo trong Control Panel
- [ ] Database đã được import (file SQL)
- [ ] Đã test bằng `test-database.php`

---

## 🧪 Test Sau Khi Sửa

1. **Truy cập:** `https://mapprod.great-site.net/test-database.php`
2. **Kết quả mong đợi:**
   - ✅ Kết nối database thành công
   - ✅ Tìm thấy các bảng
   - ✅ Không còn lỗi

3. **Truy cập:** `https://mapprod.great-site.net/`
4. **Kết quả mong đợi:**
   - ✅ Trang chủ load được
   - ✅ Có thể đăng nhập/đăng ký
   - ✅ Không còn lỗi database

---

## 🆘 Các Lỗi Thường Gặp

### Lỗi: "Access denied for user"

**Nguyên nhân:** Username hoặc password sai

**Giải pháp:**
- Kiểm tra lại `DB_USER` và `DB_PASS` trong `config.php`
- Đảm bảo password đúng (copy chính xác từ Control Panel)

---

### Lỗi: "Unknown database"

**Nguyên nhân:** Database chưa được tạo hoặc tên sai

**Giải pháp:**
1. Kiểm tra database đã được tạo trong Control Panel chưa
2. Kiểm tra `DB_NAME_MYSQL` trong `config.php` có đúng không

---

### Lỗi: "Connection refused" hoặc "Host not found"

**Nguyên nhân:** DB_HOST sai

**Giải pháp:**
- Kiểm tra lại `DB_HOST` trong `config.php`
- Đảm bảo format đúng: `sqlXXX.infinityfree.com` (XXX là số)

---

## 💡 Lưu Ý Quan Trọng

1. **DB_HOST** - Phải đúng format: `sqlXXX.infinityfree.com`
2. **DB_USER** - Username database (không phải username Control Panel)
3. **DB_PASS** - Password database (có thể khác password Control Panel)
4. **DB_NAME_MYSQL** - Tên database (thường có format: `if0_XXXXXX_hoa`)

---

**Hãy làm theo từng bước trên, đặc biệt là Bước 2-3 (lấy thông tin và sửa config.php)!**

