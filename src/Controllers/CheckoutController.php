<?php
require_once __DIR__.'/../payments.php';

function checkout_payment_methods(): array
{
  return payment_methods_options();
}

function checkout_selected_method(): string
{
  $methods = checkout_payment_methods();
  $default = $methods[0]['value'] ?? 'cod';
  return $_SESSION['checkout']['payment_method'] ?? $default;
}

function checkout_index(){
  require_login();
  payments_ensure_tables();
  $items = cart_items_detailed();
  if (!$items) { flash('error','Giỏ hàng trống'); redirect('home'); }
  $totals = cart_totals();
  $paymentMethods = checkout_payment_methods();
  $selectedMethod = checkout_selected_method();
  include __DIR__.'/../Views/checkout/index.php';
}

function checkout_place(){
  require_login();
  payments_ensure_tables();
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $addr = trim($_POST['address'] ?? '');
  $note = trim($_POST['note'] ?? '');
  $methods = checkout_payment_methods();
  $allowed = array_column($methods, 'value');
  $postedMethod = $_POST['payment_method'] ?? ($methods[0]['value'] ?? 'cod');
  $paymentMethod = in_array($postedMethod, $allowed, true) ? $postedMethod : ($methods[0]['value'] ?? 'cod');

  if (!$name || !$phone || !$addr) {
    flash('error','Vui lòng nhập đủ Họ tên, Số điện thoại và Địa chỉ.');
    redirect('checkout');
  }

  $_SESSION['checkout']['payment_method'] = $paymentMethod;

  $pdo = db(); $pdo->beginTransaction();
  try {
    $t = cart_totals(); $coupon = $_SESSION['cart']['coupon']['code'] ?? null;

    $pdo->prepare('INSERT INTO orders(user_id,customer_name,phone,address,note,
                                      subtotal,discount,shipping,total,coupon_code,status,created_at)
                   VALUES (?,?,?,?,?,?,?,?,?,?,"pending",NOW())')
        ->execute([ current_user()['id'],$name,$phone,$addr,$note,
                    $t['subtotal'],$t['discount'],$t['shipping'],$t['total'],$coupon ]);

    $oid = $pdo->lastInsertId();
    foreach (cart_items_detailed() as $it) {
      $pdo->prepare('INSERT INTO order_items(order_id,product_id,price,qty)
                     VALUES (?,?,?,?)')->execute([$oid,$it['id'],$it['price'],$it['qty']]);
    }

    $now = date('Y-m-d H:i:s');
    $pdo->prepare('INSERT INTO order_payments(order_id, method, status, created_at, updated_at)
                   VALUES (?,?,"pending",?,?)
                   ON DUPLICATE KEY UPDATE method = VALUES(method), updated_at = VALUES(updated_at)')
        ->execute([$oid, $paymentMethod, $now, $now]);

    $pdo->commit();
    cart_clear();
    flash('ok',"Đặt hàng thành công. Mã đơn #$oid – đang chờ xác nhận.");
    redirect('profile');
  } catch (Throwable $e) {
    $pdo->rollBack();
    flash('error','Lỗi đặt hàng: '.$e->getMessage());
    redirect('checkout');
  }
}
