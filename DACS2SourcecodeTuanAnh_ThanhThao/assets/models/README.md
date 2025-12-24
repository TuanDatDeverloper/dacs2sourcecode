# 3D Models Directory

Thư mục này chứa các model 3D cho kệ sách 3D.

## Cấu trúc thư mục

```
assets/models/
├── bookshelf/     - Model kệ sách 3D
├── books/         - Model sách 3D
├── furniture/     - Model nội thất (bàn, ghế, đèn, cây)
└── environment/   - Model môi trường (phòng đọc sách)
```

## Format hỗ trợ

- **GLTF** (.gltf) - Text format, có thể đọc được
- **GLB** (.glb) - Binary format, nhỏ gọn hơn, khuyến nghị

## Nguồn tải model miễn phí

### 1. Sketchfab (https://sketchfab.com)
- Tìm kiếm: "bookshelf free", "library furniture free"
- License: CC0 hoặc CC-BY (cần credit)
- Download format: GLTF/GLB

### 2. Poly Haven (https://polyhaven.com)
- Model và texture chất lượng cao
- License: CC0 (không cần credit)
- Format: GLTF, FBX, OBJ

### 3. Free3D (https://free3d.com)
- Nhiều model đa dạng
- License: Tùy model
- Format: Đa dạng

## Model cần tải

### Kệ sách (Bookshelf)
- [ ] bookshelf-classic.glb - Kệ sách cổ điển
- [ ] bookshelf-modern.glb - Kệ sách hiện đại
- [ ] bookshelf-vintage.glb - Kệ sách vintage

### Sách (Books)
- [ ] book-base.glb - Model sách cơ bản
- [ ] book-thick.glb - Model sách dày

### Nội thất (Furniture)
- [ ] desk-reading.glb - Bàn đọc sách
- [ ] chair-reading.glb - Ghế đọc sách
- [ ] lamp-desk.glb - Đèn bàn
- [ ] plant-indoor.glb - Cây cảnh trong nhà

### Môi trường (Environment)
- [ ] room-library.glb - Phòng thư viện

## Hướng dẫn tải model

1. Truy cập Sketchfab hoặc Poly Haven
2. Tìm model phù hợp với từ khóa
3. Kiểm tra license (nên chọn CC0)
4. Download format GLB
5. Đặt file vào thư mục tương ứng
6. Đảm bảo tên file khớp với MODEL_PATHS trong code

## Tối ưu hóa model

Trước khi sử dụng, nên tối ưu hóa model:
- Giảm polygon count nếu quá cao
- Compress texture
- Merge materials nếu có thể
- Sử dụng DRACO compression cho geometry

## Lưu ý

- Model sẽ tự động fallback về geometry đơn giản nếu không tải được
- Kích thước file nên < 2MB cho mỗi model
- Test model trên trình duyệt trước khi commit

