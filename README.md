
# FoodShop PHP Starter

## Yêu cầu
- XAMPP / WAMP (PHP 8+, MySQL)
- Bật Apache mod_rewrite (để .htaccess hoạt động)

## Cài đặt
1. Tạo DB `foodshop` trong MySQL.
2. Import file `sql/schema.sql`.
3. Copy toàn bộ thư mục lên `htdocs/foodshop/`.
4. Mở `config/config.php` và chỉnh `BASE_URL` nếu cần.
5. Truy cập: `http://localhost/foodshop/public/`

### Tài khoản mẫu
- Admin: `admin@foodshop.local` / `123456`
- User: `user@example.com` / `123456`

## Ghi chú
- Thanh toán đang mock (COD). Có thể tích hợp VNPay/MoMo sau.
- Khu vực admin: thống kê doanh thu, đơn hàng, CRUD đơn giản.
- Mã chào mừng: `WELCOME10`.
