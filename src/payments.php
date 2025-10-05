<?php
require_once __DIR__ . '/../config/db.php';

function payments_ensure_tables(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS order_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  method VARCHAR(40) NOT NULL,
  status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uniq_order (order_id),
  CONSTRAINT fk_order_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

    db()->exec($sql);
    $ensured = true;
}

function payment_methods_options(): array
{
    return [
        [
            'value' => 'cod',
            'label' => 'Thanh toán khi nhận hàng',
            'description' => 'Giao nhận và thanh toán tiền mặt trực tiếp cho tài xế.',
            'badge' => 'Phổ biến',
            'icon' => '&#128181;',
        ],
        [
            'value' => 'momo',
            'label' => 'Ví MoMo',
            'description' => 'Quét mã hoặc chuyển nhanh trong ứng dụng MoMo.',
            'badge' => 'Ưu đãi 5%',
            'icon' => '&#128184;',
        ],
        [
            'value' => 'vnpay',
            'label' => 'VNPay QR',
            'description' => 'Thanh toán qua ứng dụng ngân hàng hỗ trợ VNPay.',
            'badge' => 'QR tiện lợi',
            'icon' => '&#128179;',
        ],
        [
            'value' => 'zalopay',
            'label' => 'ZaloPay',
            'description' => 'Liên kết với ZaloPay để thanh toán siêu tốc.',
            'badge' => 'Tiết kiệm 10k',
            'icon' => '&#128241;',
        ],
    ];
}

function payment_methods_map(): array
{
    static $map = null;
    if ($map === null) {
        $map = [];
        foreach (payment_methods_options() as $item) {
            $map[$item['value']] = $item;
        }
    }
    return $map;
}

function payment_method_label(string $value): string
{
    $map = payment_methods_map();
    return $map[$value]['label'] ?? strtoupper($value);
}
