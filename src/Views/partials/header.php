<?php
$u = current_user();
if (!is_array($u)) $u = [];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f6f7fb}
    .product-card img{object-fit:cover;height:160px}
    .promo-card img{object-fit:cover}
    .navbar{--bs-navbar-padding-y:.35rem}
    .btn,.badge{border-radius:999px}
    .marq-wrap {background:#fff3cd;border:1px solid #ffe69c;border-radius:.5rem}
    .marq-wrap marquee {padding:.35rem .5rem;color:#b54708;font-weight:600}
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">🍜 <?= e(APP_NAME) ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <form class="d-flex ms-auto me-3" method="get" action="index.php">
        <input type="hidden" name="r" value="home">
        <?php if(isset($_GET['category']) && (int)$_GET['category']>0): ?>
          <input type="hidden" name="category" value="<?= (int)$_GET['category'] ?>">
        <?php endif; ?>
        <input class="form-control me-2" type="search" name="q" value="<?= e($_GET['q'] ?? "") ?>" placeholder="Tìm món/nhà hàng">
        <button class="btn btn-light" type="submit">Tìm</button>
      </form>

      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="?r=cart">🛒 Giỏ hàng</a></li>
        <?php if(!empty($u)): ?>
          <?php $display = ($u['name'] ?? '') !== '' ? $u['name'] : ($u['email'] ?? 'Tài khoản'); ?>
          <li class="nav-item"><a class="nav-link" href="?r=profile">📦 Đơn hàng của bạn</a></li>
          <?php if(($u['role'] ?? '') === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="../admin/index.php">👑 Quản lý</a></li>
          <?php endif; ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">👤 <?= e($display) ?></a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="?r=profile">Thông tin tài khoản</a></li>
              <?php if(($u['role'] ?? '') === 'admin'): ?>
                <li><a class="dropdown-item" href="../admin/index.php/orders">Quản lý đơn hàng</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="?r=auth/logout">Đăng xuất</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="?r=auth/login">Đăng nhập</a></li>
          <li class="nav-item"><a class="nav-link" href="?r=auth/register">Đăng ký</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-3">
  <?php if($m=flash('ok')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
  <?php if($e=flash('error')): ?><div class="alert alert-danger"><?= e($e) ?></div><?php endif; ?>
  <style>
    html,body{height:100%}
    body{
      min-height:100vh;                 /* cao tối thiểu = chiều cao trình duyệt */
      display:flex; flex-direction:column; /* để footer bám đáy */
      background:#f6f7fb;
    }
    main{flex:1 0 auto}                 /* phần nội dung đẩy footer xuống đáy */
    footer{margin-top:auto}             /* footer luôn ở cuối */
    .product-card img{object-fit:cover;height:160px}
    .promo-card img{object-fit:cover}
    .navbar{--bs-navbar-padding-y:.35rem}
    .btn,.badge{border-radius:999px}
    .marq-wrap{background:#fff3cd;border:1px solid #ffe69c;border-radius:.5rem}
    .marq-wrap marquee{padding:.35rem .5rem;color:#b54708;font-weight:600}
  </style>
