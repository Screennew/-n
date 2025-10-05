<?php
require_once __DIR__.'/../helpers.php';
require_once __DIR__.'/../../config/db.php';

function promotion_index(){
  $pdo = db();
  $promos = $pdo->query('SELECT * FROM promotions WHERE active=1 ORDER BY id DESC')->fetchAll() ?: [];
  $coupons = $pdo->query('SELECT * FROM coupons WHERE active=1 ORDER BY COALESCE(expires_at, NOW())')->fetchAll() ?: [];
  include __DIR__.'/../Views/promotions/index.php';
}
