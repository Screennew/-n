<?php
function auth_login(){
  if(is_post()){
    if(login($_POST['email']??'', $_POST['password']??'')){ flash('ok','Đăng nhập thành công'); redirect('home'); }
    flash('error','Sai email hoặc mật khẩu');
  }
  include __DIR__.'/../Views/auth/login.php';
}
function auth_register(){
  if(is_post()){
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
    if($name && $email && $pass){ register_user($name,$email,$pass); flash('ok','Đăng ký thành công. Mời đăng nhập.'); redirect('auth/login'); }
    else flash('error','Vui lòng điền đủ thông tin');
  }
  include __DIR__.'/../Views/auth/register.php';
}
function auth_logout(){ logout(); redirect('home'); }
