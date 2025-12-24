# 📚 Hướng Dẫn Deploy Folder KHOSACH

## ✅ Folder KHOSACH Là Gì?

Folder `khosach/` chứa **PDF sách gốc** - đây là phần quan trọng của dự án.

---

## 📁 Cấu Trúc

### Trước Khi Deploy (Local):
```
DACS2SourcecodeTuanAnh_ThanhThao/
├── khosach/              # PDF sách gốc (17 files)
│   ├── book1.pdf
│   ├── book2.pdf
│   └── ...
└── assets/
    └── uploads/
        └── books/        # PDF đã được upload vào database
```

### Sau Khi Deploy (Production):
```
htdocs/
├── khosach/              # ⭐ PDF sách gốc (backup/reference)
│   ├── book1.pdf
│   ├── book2.pdf
│   └── ...
└── assets/
    └── uploads/
        └── books/        # PDF đã được upload vào database
```

---

## 🚀 Cách Deploy Folder KHOSACH

### Cách 1: Đặt Ở Root (Khuyến Nghị)

1. **Upload folder `khosach/` lên `/htdocs/khosach/`**
2. **Cấu trúc:**
   ```
   htdocs/
   ├── khosach/           # PDF sách gốc
   ├── assets/
   ├── images/
   └── ...
   ```

### Cách 2: Đặt Trong Assets

1. **Upload folder `khosach/` lên `/htdocs/assets/khosach/`**
2. **Cấu trúc:**
   ```
   htdocs/
   ├── assets/
   │   ├── khosach/       # PDF sách gốc
   │   ├── uploads/
   │   └── models/
   └── ...
   ```

---

## ⚙️ Cấu Hình (Nếu Cần)

### Nếu Muốn Code Tự Động Tìm PDF Từ KHOSACH

Có thể thêm vào `includes/config.php`:

```php
// KHOSACH Configuration
define('KHOSACH_DIR', __DIR__ . '/../khosach/'); // Nếu ở root
// hoặc
define('KHOSACH_DIR', __DIR__ . '/../assets/khosach/'); // Nếu trong assets
```

---

## 📋 Checklist Deploy KHOSACH

- [ ] Upload folder `khosach/` lên server
- [ ] Đặt ở `/htdocs/khosach/` (hoặc `/htdocs/assets/khosach/`)
- [ ] Đảm bảo tất cả PDF files đã được upload
- [ ] Kiểm tra permissions (755 cho folder, 644 cho files)
- [ ] Test truy cập: `https://yourdomain.epizy.com/khosach/` (nếu cần)

---

## 🔒 Bảo Mật

### Bảo Vệ Folder KHOSACH (Tùy Chọn)

Nếu không muốn người dùng truy cập trực tiếp vào PDF, thêm vào `.htaccess`:

```apache
# Protect khosach folder (optional)
<IfModule mod_rewrite.c>
    RewriteRule ^khosach/ - [F,L]
</IfModule>
```

Hoặc chỉ cho phép truy cập từ code:

```apache
# Allow access only from PHP scripts
<Directory "khosach">
    Options -Indexes
    AllowOverride None
    Require all denied
</Directory>
```

---

## 📝 Lưu Ý

1. **Folder `khosach/` chứa PDF gốc** - Dùng để backup/reference
2. **PDF đã được upload vào database** - Lưu trong `assets/uploads/books/`
3. **Code tự động tìm PDF** - Từ `assets/uploads/books/` (không cần `khosach/`)
4. **KHOSACH là backup** - Có thể không cần nếu đã có trong `assets/uploads/books/`

---

## ✅ Kết Luận

- ✅ **Nên deploy folder `khosach/`** - Để backup và quản lý PDF gốc
- ✅ **Đặt ở `/htdocs/khosach/`** - Dễ quản lý
- ✅ **Không bắt buộc** - Nếu PDF đã có trong `assets/uploads/books/`

---

**Folder `khosach/` là phần quan trọng của dự án, nên được deploy! 📚**

