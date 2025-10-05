<?php include __DIR__.'/../partials/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <h4>Đăng ký</h4>
    <form method="post" action="">
      <div class="mb-3"><label class="form-label">Họ tên</label><input class="form-control" name="name"></div>
      <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
      <div class="mb-3"><label class="form-label">Mật khẩu</label><input type="password" class="form-control" name="password"></div>
      <button class="btn btn-success w-100">Tạo tài khoản</button>
    </form>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
