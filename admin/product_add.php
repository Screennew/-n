<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../src/auth.php';
require_login(); if(!is_admin()){ die("403"); }

if($_POST){
  $st=db()->prepare("INSERT INTO products(name,price,description,image_url) VALUES(?,?,?,?)");
  $st->execute([$_POST['name'],$_POST['price'],$_POST['description'],$_POST['image_url']]);
  header("Location: index.php");
  exit;
}
?>
<form method="post" class="p-3">
  <input name="name" placeholder="Tên món" class="form-control mb-2">
  <input name="price" type="number" placeholder="Giá" class="form-control mb-2">
  <input name="image_url" placeholder="Ảnh" class="form-control mb-2">
  <textarea name="description" class="form-control mb-2"></textarea>
  <button class="btn btn-success">Lưu</button>
</form>
