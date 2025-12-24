# 📁 Cấu Trúc Dự Án Để Deploy

## ✅ Cấu Trúc Thư Mục Sạch

Sau khi dọn dẹp, cấu trúc dự án như sau:

```
htdocs/ (hoặc public_html/)
├── index.php                    # Trang chủ
├── login.php                    # Đăng nhập
├── register.php                 # Đăng ký
├── dashboard.php                # Dashboard
├── profile.php                  # Thông tin cá nhân
├── new-books.php                # Sách mới
├── history.php                  # Sách của tôi
├── book-info.php                # Chi tiết sách
├── reading.php                  # Đọc sách
├── quiz.php                     # AI Quiz
├── shop.php                     # Cửa hàng
├── inventory.php                # Túi đồ
├── bookshelf-3d.php             # Kệ sách 3D
├── about.php                    # Về chúng tôi
├── forgot-password.php          # Quên mật khẩu
├── reset-password.php           # Đặt lại mật khẩu
├── verify-email.php             # Xác nhận email
├── logout.php                   # Đăng xuất
│
├── .htaccess                    # Apache config
├── README.md                    # Hướng dẫn
│
├── api/                         # API endpoints
│   ├── auth.php
│   ├── books.php
│   ├── public-books.php
│   ├── progress.php
│   ├── quiz.php
│   ├── shop.php
│   ├── inventory.php
│   ├── bookshelf.php
│   ├── stats.php
│   ├── email.php
│   ├── verification.php
│   ├── google-auth.php
│   ├── upload-book.php
│   ├── update-book-cover.php
│   ├── profile.php
│   └── admin/
│       ├── users.php
│       ├── stats.php
│       └── logs.php
│
├── includes/                    # PHP includes
│   ├── config.php              # ⚠️ PHẢI SỬA KHI DEPLOY
│   ├── database.php
│   ├── auth.php
│   ├── admin.php
│   ├── email.php
│   ├── verification.php
│   ├── header.php
│   ├── header-auth.php
│   ├── admin-header.php
│   └── footer.php
│
├── admin/                       # Admin Panel
│   ├── index.php
│   ├── users.php
│   ├── user-edit.php
│   ├── send-email.php
│   └── logs.php
│
├── assets/                      # Static files
│   ├── uploads/
│   │   └── books/              # PDF sách đã upload (17 files)
│   ├── khosach/                 # ⭐ PDF sách gốc (backup/reference)
│   │   └── [PDF files từ khosach]
│   └── models/                 # 3D models
│       ├── bookshelf/
│       ├── books/
│       ├── furniture/
│       └── environment/
│
├── images/                      # Ảnh bìa sách (18 files)
│   ├── anhtrangchu.jpg
│   └── [17 ảnh bìa sách khác]
│
├── css/                         # Stylesheets
│   └── style.css
│
├── js/                          # JavaScript
│   ├── api-client.js
│   ├── auth.js
│   ├── books-api.js
│   ├── books-api-simple.js
│   ├── bookshelf-3d.js
│   ├── bookshelf-3d-enhanced.js
│   ├── bookshelf-3d-new.js
│   ├── bookshelf-3d-v2.js
│   ├── bookshelf-3d-v2-improved.js
│   ├── bookshelf-procedural-enhanced.js
│   ├── google-auth.js
│   ├── main.js
│   ├── model-loader.js
│   ├── navigation.js
│   ├── verification.js
│   └── admin.js
│
├── database/                    # Database files
│   ├── DEPLOY_FOR_INFINITYFREE.sql  # ⭐ File import database
│   └── init.php
│
├── vendor/                      # Third-party libraries
│   ├── autoload.php
│   └── phpmailer/
│       └── phpmailer/
│           └── src/
│               ├── Exception.php
│               ├── PHPMailer.php
│               └── SMTP.php
│
├── cron/                        # Cron jobs
│   └── send-email-reminders.php
│
└── [Các file .md hướng dẫn]     # Documentation
    ├── HUONG_DAN_DEPLOY.md
    ├── HUONG_DAN_SAU_KHI_DEPLOY.md
    ├── CHECKLIST_DEPLOY.md
    └── ...
```

---

## ❌ Các Folder/File KHÔNG CẦN (Đã Xóa)

- ❌ `anhgiaodienmau/` - Không được sử dụng
- ❌ `book-reading-website/` - Không được sử dụng
- ❌ `dacs2sourcecode/` - Folder cha, không cần deploy

## ✅ Folder KHOSACH (Quan Trọng - Cần Deploy)

- ✅ `khosach/` - **CẦN DEPLOY** - Chứa PDF sách gốc (backup/reference)
  - Có thể đặt ở root `/htdocs/khosach/` hoặc trong `assets/khosach/`
  - Dùng để backup và quản lý PDF sách gốc
  - Code sẽ tự động tìm PDF từ `assets/uploads/books/` (đã được upload)

---

## 📋 Checklist Trước Khi Deploy

### 1. File Cần Upload

- [ ] Tất cả file PHP (root và trong folders)
- [ ] Folder `api/` và tất cả file bên trong
- [ ] Folder `includes/` và tất cả file bên trong
- [ ] Folder `admin/` và tất cả file bên trong
- [ ] Folder `images/` và tất cả ảnh (18 files)
- [ ] Folder `assets/uploads/books/` và tất cả PDF (17 files)
- [ ] Folder `assets/khosach/` và tất cả PDF gốc (nếu có) ⭐ QUAN TRỌNG
- [ ] Folder `assets/models/` (nếu có)
- [ ] Folder `css/` và file `style.css`
- [ ] Folder `js/` và tất cả file JavaScript
- [ ] Folder `vendor/` (PHPMailer)
- [ ] Folder `database/` và file `DEPLOY_FOR_INFINITYFREE.sql`
- [ ] File `.htaccess`
- [ ] File `README.md` (tùy chọn)

### 2. File KHÔNG Cần Upload

- [ ] `dacs2sourcecode/` - Folder cha
- [ ] Các file `.md` hướng dẫn (tùy chọn, có thể xóa)
- [ ] File `.docx` (tùy chọn, có thể xóa)

### 2.1. Folder KHOSACH (Cần Upload)

- [ ] **Upload folder `khosach/`** - Chứa PDF sách gốc
  - Có thể đặt ở: `/htdocs/khosach/` hoặc `/htdocs/assets/khosach/`
  - Dùng để backup và quản lý PDF sách gốc
  - Code sẽ tự động tìm PDF từ `assets/uploads/books/` (đã được upload vào database)

### 3. Cấu Hình Sau Khi Upload

- [ ] Sửa `includes/config.php`:
  - [ ] `DB_HOST`
  - [ ] `DB_USER`
  - [ ] `DB_PASS`
  - [ ] `DB_NAME_MYSQL`
  - [ ] `SITE_URL`
- [ ] Import database: `database/DEPLOY_FOR_INFINITYFREE.sql`
- [ ] Xóa file `index2.html` (nếu có)

---

## 🎯 Cấu Trúc Tối Ưu Cho Deploy

### Tất Cả File Ở Root `/htdocs/`

```
htdocs/
├── index.php
├── api/
├── includes/
├── images/
├── assets/
├── css/
├── js/
├── admin/
├── database/
├── vendor/
└── .htaccess
```

**KHÔNG** có subfolder như:
- ❌ `/htdocs/DACS2SourcecodeTuanAnh_ThanhThao/`
- ❌ `/htdocs/dacs2sourcecode/`

---

## 📝 Lưu Ý

1. **Tất cả file phải ở root `/htdocs/`** - Không có subfolder
2. **Xóa file `index2.html`** - File mặc định của InfinityFree
3. **Sửa `config.php`** - Điền thông tin database và domain
4. **Import database** - Chạy file SQL trong phpMyAdmin
5. **Upload đầy đủ** - Đảm bảo tất cả file và folder đã được upload

---

**Cấu trúc này đã được tối ưu và sẵn sàng để deploy! 🚀**

