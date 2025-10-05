<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../src/auth.php';
require_login(); if(!is_admin()){ die("403"); }

$id=(int)($_GET['id']??0);
$s=$_GET['s']??'';
if(in_array($s,['paid','cancelled'])){
  $st=db()->prepare("UPDATE orders SET status=? WHERE id=?");
  $st->execute([$s,$id]);
}
header("Location: index.php");
