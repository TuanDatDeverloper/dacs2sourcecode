# 🔧 Sửa Lỗi 404 Not Found

## ⚠️ Vấn Đề

Bạn gặp lỗi **404 Not Found** khi truy cập `https://mapprod.great-site.net/`

**Nguyên nhân:**
- File `index.php` không tồn tại ở `/htdocs/index.php`
- Code vẫn nằm trong subfolder
- File chưa được upload

---

## ✅ Giải Pháp

### Bước 1: Kiểm Tra File Có Tồn Tại Không

1. **Vào File Manager**
2. **Vào folder `/htdocs/`** (root folder)
3. **Kiểm tra có file `index.php` không?**

**Nếu KHÔNG có:**
- Code chưa được upload
- Hoặc code đang ở trong subfolder

---

### Bước 2: Kiểm Tra Code Có Ở Subfolder Không

1. **Vào File Manager**
2. **Kiểm tra cấu trúc:**

**SAI (Code ở subfolder):**
```
htdocs/
└── DACS2SourcecodeTuanAnh_ThanhThao/
    ├── index.php          ❌ Ở đây
    ├── api/
    └── ...
```

**ĐÚNG (Code ở root):**
```
htdocs/
├── index.php              ✅ Ở đây
├── api/
├── includes/
└── ...
```

---

### Bước 3: Di Chuyển Code Lên Root

**Nếu code đang ở subfolder:**

1. **Vào File Manager**
2. **Vào folder `DACS2SourcecodeTuanAnh_ThanhThao/`** (hoặc subfolder tương tự)
3. **Select All** (Ctrl+A hoặc click "Select All")
4. **Click "Cut" hoặc "Move"**
5. **Quay lại folder `/htdocs/`** (parent folder - click ".." hoặc "Up")
6. **Click "Paste" hoặc "Move Here"**
7. **Xóa folder `DACS2SourcecodeTuanAnh_ThanhThao` rỗng** (nếu còn)

**Kết quả:** Tất cả file sẽ ở `/htdocs/` thay vì trong subfolder.

---

### Bước 4: Upload File Nếu Chưa Có

**Nếu file `index.php` không tồn tại:**

1. **Upload file `index.php`** từ local lên `/htdocs/`
2. **Upload tất cả file và folder** cần thiết:
   - `index.php`
   - `api/`
   - `includes/`
   - `images/`
   - `assets/`
   - `css/`
   - `js/`
   - `admin/`
   - `database/`
   - `vendor/`
   - `.htaccess`

---

### Bước 5: Kiểm Tra Permissions

1. **File permissions:**
   - Files: `644`
   - Folders: `755`

2. **Kiểm tra trong File Manager:**
   - Right-click file → Properties
   - Đảm bảo permissions đúng

---

## 📋 Checklist Sửa Lỗi 404

- [ ] File `index.php` có ở `/htdocs/index.php` không?
- [ ] Code có nằm trong subfolder không?
- [ ] Đã di chuyển tất cả file lên `/htdocs/` chưa?
- [ ] File permissions đúng chưa? (644 cho files, 755 cho folders)
- [ ] Đã xóa folder subfolder rỗng chưa?

---

## 🧪 Test Sau Khi Sửa

1. **Truy cập:** `https://mapprod.great-site.net/`
2. **Kết quả mong đợi:**
   - ✅ Thấy trang chủ BookOnline
   - ✅ Không còn lỗi 404

---

## 🆘 Nếu Vẫn 404

### Kiểm Tra Thêm:

1. **Kiểm tra đường dẫn:**
   - Truy cập: `https://mapprod.great-site.net/index.php`
   - Nếu vẫn 404 → File chưa tồn tại

2. **Kiểm tra File Manager:**
   - Vào `/htdocs/`
   - Xem danh sách file
   - Có `index.php` không?

3. **Kiểm tra Case Sensitivity:**
   - Linux phân biệt hoa/thường
   - Đảm bảo tên file đúng: `index.php` (không phải `Index.php`)

4. **Kiểm tra .htaccess:**
   - File `.htaccess` có block file không?
   - Kiểm tra rules trong `.htaccess`

---

## 💡 Lưu Ý Quan Trọng

1. **File `index.php` PHẢI ở `/htdocs/index.php`** - Không phải trong subfolder
2. **Tất cả file phải ở root** - Không có subfolder
3. **Case sensitive** - Linux phân biệt hoa/thường
4. **Permissions** - Files: 644, Folders: 755

---

**Hãy làm theo từng bước trên, đặc biệt là Bước 3 (di chuyển code lên root)!**

