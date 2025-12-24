# 📚 BookOnline - Nền tảng đọc sách trực tuyến

Website đọc sách với PHP backend, MySQL database, AI Quiz, và 3D Bookshelf.

---

## 🚀 CÀI ĐẶT NHANH

### 1. Yêu cầu
- XAMPP (Apache + MySQL + PHP 7.4+)
- MySQL Database

### 2. Setup Database
1. Mở **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Chạy file SQL: `database/DEPLOY_FOR_INFINITYFREE.sql`
   - File này sẽ tạo tất cả bảng tự động
   - ⚠️ Lưu ý: File này dùng cho cả local và production

### 3. Di chuyển vào htdocs
1. Copy toàn bộ folder `DACS2SourcecodeTuanAnh_ThanhThao` vào:
   ```
   C:\xampp\htdocs\DACS2SourcecodeTuanAnh_ThanhThao\
   ```

### 4. Cấu hình
- File: `includes/config.php`
- Database: `book_online`
- User: `root`
- Password: `1234` (đổi nếu cần)

### 5. Chạy website
```
http://localhost/DACS2SourcecodeTuanAnh_ThanhThao/
```

---

## 📁 CẤU TRÚC DỰ ÁN

```
DACS2SourcecodeTuanAnh_ThanhThao/
├── api/                    # API endpoints
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
│   ├── profile.php
│   └── admin/
│       ├── users.php
│       ├── stats.php
│       └── logs.php
├── includes/               # PHP includes
│   ├── config.php         # ⚠️ Database config (PHẢI SỬA KHI DEPLOY)
│   ├── database.php       # Database class
│   ├── auth.php           # Authentication
│   ├── admin.php          # Admin functions
│   ├── email.php          # Email service
│   ├── verification.php   # Email verification
│   ├── header.php
│   ├── header-auth.php
│   ├── admin-header.php
│   └── footer.php
├── admin/                  # Admin Panel
│   ├── index.php
│   ├── users.php
│   ├── send-email.php
│   └── logs.php
├── database/              # Database files
│   ├── DEPLOY_FOR_INFINITYFREE.sql  # ⭐ File import database
│   └── init.php
├── assets/                # Static files
│   ├── uploads/
│   │   └── books/         # PDF sách
│   └── models/            # 3D models
├── images/                # Ảnh bìa sách
├── css/                   # Stylesheets
│   └── style.css
├── js/                    # JavaScript
│   ├── api-client.js
│   ├── auth.js
│   ├── books-api.js
│   ├── bookshelf-3d.js
│   └── ...
├── vendor/                # Third-party (PHPMailer)
├── cron/                  # Cron jobs
├── index.php              # Trang chủ
├── login.php
├── register.php
├── dashboard.php
├── profile.php            # Thông tin cá nhân
├── new-books.php          # Sách mới
├── history.php            # Sách của tôi
├── book-info.php
├── reading.php
├── quiz.php
├── shop.php
├── inventory.php
├── bookshelf-3d.php
├── about.php
├── verify-email.php
├── forgot-password.php
└── .htaccess              # Apache config
```

---

## 🎯 TÍNH NĂNG

- ✅ **Authentication**: Đăng ký, đăng nhập, session management
- ✅ **Books Management**: Thêm sách, quản lý thư viện
- ✅ **Reading Progress**: Theo dõi tiến độ đọc, bookmark
- ✅ **AI Quiz**: Tạo quiz với Hugging Face AI, nhận Book Coins
- ✅ **Shop System**: Mua vật phẩm trang trí với Book Coins
- ✅ **3D Bookshelf**: Kệ sách 3D với Three.js
- ✅ **Statistics**: Dashboard với thống kê đọc sách

---

## 🔧 TROUBLESHOOTING

### Lỗi 404 Not Found
- Đảm bảo folder nằm trong `C:\xampp\htdocs\`
- Kiểm tra Apache đã start chưa

### Lỗi Database Connection
- Kiểm tra MySQL đã start
- Kiểm tra `includes/config.php` (user, password)
- Đảm bảo đã chạy `COMPLETE_DATABASE_SETUP.sql`

### Shop items không hiển thị
- Chạy lại `database/COMPLETE_DATABASE_SETUP.sql` (phần INSERT shop items)

---

## 📝 GHI CHÚ

- **Hugging Face API**: Quiz generation cần API key (optional)
- **Google Books API**: Thêm sách từ Google Books cần API key (optional)
- **Session**: Timeout 1 giờ (có thể cấu hình trong `config.php`)

---

## 📄 LICENSE

© 2025 BookOnline. All rights reserved.
