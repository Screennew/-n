<?php
function cart_index(){
  $items = cart_items_detailed();
  $totals = cart_totals();
  $availableCoupons = db()->query('SELECT * FROM coupons WHERE active=1 ORDER BY COALESCE(expires_at, NOW())')->fetchAll() ?: [];
  include __DIR__.'/../Views/cart/index.php';
}

function cart_add_action(){
  $id  = (int)($_POST['id'] ?? 0);
  $qty = max(1,(int)($_POST['qty'] ?? 1));
  if($id>0){ cart_add($id,$qty); flash('ok','Đã thêm vào giỏ'); }
  $back = $_SERVER['HTTP_REFERER'] ?? 'index.php?r=cart';
  header('Location: '.$back); exit;
}

function cart_update_action(){
  if(isset($_POST['coupon'])){
    if(apply_coupon(trim($_POST['coupon']))) flash('ok','Áp dụng mã giảm giá thành công');
    else flash('error','Mã không hợp lệ hoặc đã hết hạn');
  } else {
    foreach(($_POST['qty']??[]) as $pid=>$q) cart_set((int)$pid,(int)$q);
    flash('ok','Cập nhật giỏ hàng');
  }
  header('Location: index.php?r=cart'); exit;
}
