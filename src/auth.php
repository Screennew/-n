<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/helpers.php';

function current_user(){ return $_SESSION['user'] ?? null; }
function is_admin(){ return (current_user()['role'] ?? '')==='admin'; }
function require_login(){ if(!current_user()){ flash('error','Vui lòng đăng nhập'); redirect('auth/login'); } }

function login($email,$password){
  $st=db()->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $st->execute([$email]); $u=$st->fetch();
  if($u && password_verify($password,$u['password_hash'])){
    session_regenerate_id(true);
    $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
    return true;
  }
  return false;
}
function register_user($name,$email,$password){
  $hash=password_hash($password,PASSWORD_ALGO);
  $st=db()->prepare('INSERT INTO users(name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
  $st->execute([$name,$email,$hash,'customer']);
  return db()->lastInsertId();
}
function logout(){
  $_SESSION=[]; if(ini_get('session.use_cookies')){
    $p=session_get_cookie_params();
    setcookie(session_name(),'',
      time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);
  } session_destroy();
}
