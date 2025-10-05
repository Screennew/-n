<?php include __DIR__.'/../partials/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <h4>Đăng nhập</h4>
    <form method="post" action="">
      <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
      <div class="mb-3"><label class="form-label">Mật khẩu</label><input type="password" class="form-control" name="password"></div>
      <button class="btn btn-primary w-100">Đăng nhập</button>
    </form>
    <div class="mt-2 small">Chưa có tài khoản? <a href="?r=auth/register">Đăng ký</a></div>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
