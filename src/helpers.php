<?php
function redirect($path = '') {
  $url = 'index.php';
  if ($path) $url .= '?r=' . ltrim($path, '/');
  header('Location: '.$url); exit;
}
function is_post() { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function money($n){ return number_format((float)$n, 0, ',', '.').' '.CURRENCY; }
function flash($key, $value=null){
  if(session_status()!==PHP_SESSION_ACTIVE) session_start();
  if($value!==null){ $_SESSION['_flash'][$key]=$value; return; }
  $v=$_SESSION['_flash'][$key]??null; unset($_SESSION['_flash'][$key]); return $v;
}
function base_url($path=''){
  $url='index.php'; if($path) $url.='?r='.ltrim($path,'/'); return $url;
}

function order_status_text($status){
  return [
    'pending' => 'Chờ xử lý',
    'paid' => 'Đã thanh toán',
    'cancelled' => 'Đã hủy',
  ][$status] ?? ucfirst($status);
}
function order_status_badge_class($status){
  return [
    'pending' => 'badge-warning text-dark',
    'paid' => 'badge-success',
    'cancelled' => 'badge-secondary',
  ][$status] ?? 'badge-secondary';
}
