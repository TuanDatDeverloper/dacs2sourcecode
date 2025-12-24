# 🌐 Cách Tìm Domain Của Bạn Trên InfinityFree

## 📍 Cách 1: Xem Trong Control Panel (Dễ Nhất)

1. **Đăng nhập vào InfinityFree Control Panel**
   - Truy cập: https://www.infinityfree.net/
   - Đăng nhập với tài khoản của bạn

2. **Vào phần "Websites" hoặc "My Websites"**
   - Trong Control Panel, tìm mục **"Websites"** hoặc **"My Websites"**
   - Click vào để xem danh sách website

3. **Xem Domain**
   - Bạn sẽ thấy domain của bạn hiển thị ở đây
   - Ví dụ: `yourname.epizy.com` hoặc `yourname.epizy.net`
   - Hoặc domain riêng nếu bạn đã add domain

---

## 📍 Cách 2: Xem Trong File Manager

1. **Vào File Manager**
   - Trong Control Panel, click **"File Manager"**

2. **Xem đường dẫn**
   - Đường dẫn thường là: `/home/volXXX_XXX/epizy_XXX/public_html/`
   - Domain của bạn thường là: `epizy_XXX.epizy.com` (XXX là số)

---

## 📍 Cách 3: Xem Trong Account Details

1. **Vào "Account" hoặc "Account Details"**
   - Trong Control Panel, tìm mục **"Account"**

2. **Xem thông tin website**
   - Domain sẽ được hiển thị trong phần thông tin tài khoản

---

## 📍 Cách 4: Kiểm Tra Email Đăng Ký

1. **Kiểm tra email đăng ký InfinityFree**
   - InfinityFree thường gửi email với thông tin domain khi bạn tạo website
   - Tìm email từ InfinityFree

---

## 📍 Cách 5: Xem Trong Domain Manager

1. **Vào "Domain Manager" hoặc "Domains"**
   - Trong Control Panel, tìm mục **"Domain Manager"**

2. **Xem danh sách domain**
   - Bạn sẽ thấy tất cả domain đã đăng ký
   - Domain mặc định thường có dạng: `yourname.epizy.com`

---

## 🔍 Các Dạng Domain Thường Gặp

### Domain Mặc Định (Free):
- `yourname.epizy.com`
- `yourname.epizy.net`
- `yourname.rf.gd`

### Domain Riêng (Nếu đã add):
- `yourdomain.com`
- `yourdomain.net`
- `yourdomain.org`

---

## ✅ Sau Khi Tìm Được Domain

Sau khi biết domain của bạn, sửa trong `includes/config.php`:

```php
// Ví dụ nếu domain của bạn là: mybook.epizy.com
define('SITE_URL', 'https://mybook.epizy.com');
```

**Lưu ý:**
- Phải dùng **HTTPS** (không phải HTTP)
- Không có dấu `/` ở cuối
- Thay `mybook.epizy.com` bằng domain thực tế của bạn

---

## 🆘 Nếu Không Tìm Thấy Domain

1. **Kiểm tra lại email đăng ký**
2. **Liên hệ Support InfinityFree**
3. **Tạo website mới** (nếu chưa có)

---

## 📝 Ví Dụ Cụ Thể

Giả sử domain của bạn là: `bookonline123.epizy.com`

Thì trong `config.php` sẽ là:
```php
define('SITE_URL', 'https://bookonline123.epizy.com');
```

---

**Chúc bạn tìm được domain! 🎉**

