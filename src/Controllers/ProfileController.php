<?php
require_once __DIR__.'/../payments.php';

function profile_index(){
  require_login();
  payments_ensure_tables();
  $st=db()->prepare('SELECT o.*, pay.method AS payment_method FROM orders o LEFT JOIN order_payments pay ON pay.order_id=o.id WHERE o.user_id=? ORDER BY o.id DESC');
  $st->execute([current_user()['id']]); $orders=$st->fetchAll();
  $paymentMap = payment_methods_map();
  include __DIR__.'/../Views/profile/index.php';
}
