<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/reviews.php';

if (!is_admin()) {
    flash('error', 'Chỉ admin');
    redirect('auth/login');
}

$pdo = db();
$formErrors = [];
$formValues = ['name' => '', 'address' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = (int)($_POST['delete'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM restaurants WHERE id = ?');
                $stmt->execute([$id]);
                flash('ok', 'Đã xoá nhà hàng #' . $id);
            } catch (PDOException $e) {
                flash('error', 'Không thể xoá do còn món ăn hoặc đơn liên quan.');
            }
        } else {
            flash('error', 'ID nhà hàng không hợp lệ.');
        }
        header('Location: restaurants.php');
        exit;
    }

    $formValues['name'] = trim($_POST['name'] ?? '');
    $formValues['address'] = trim($_POST['address'] ?? '');
    $formValues['phone'] = trim($_POST['phone'] ?? '');

    if ($formValues['name'] === '') {
        $formErrors[] = 'Vui lòng nhập tên nhà hàng.';
    }
    if ($formValues['phone'] !== '' && !preg_match('/^[0-9+ ]{8,20}$/', $formValues['phone'])) {
        $formErrors[] = 'Số điện thoại không hợp lệ.';
    }

    if (!$formErrors) {
        $stmt = $pdo->prepare('INSERT INTO restaurants(name, address, phone, created_at) VALUES(?,?,?,NOW())');
        $stmt->execute([
            $formValues['name'],
            $formValues['address'] !== '' ? $formValues['address'] : null,
            $formValues['phone'] !== '' ? $formValues['phone'] : null,
        ]);
        flash('ok', 'Đã thêm nhà hàng mới.');
        header('Location: restaurants.php');
        exit;
    }
}

$restaurantRows = $pdo->query('SELECT * FROM restaurants ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

$productMap = [];
$productCounts = [];
if ($restaurantRows) {
    $productStmt = $pdo->query('SELECT id, restaurant_id FROM products WHERE restaurant_id IS NOT NULL');
    foreach ($productStmt as $prod) {
        $rid = (int)$prod['restaurant_id'];
        if (!isset($productMap[$rid])) {
            $productMap[$rid] = [];
        }
        $productMap[$rid][] = (int)$prod['id'];
        $productCounts[$rid] = ($productCounts[$rid] ?? 0) + 1;
    }
}

$orderStats = [];
$orderStmt = $pdo->query('SELECT p.restaurant_id AS rid,
                                 COUNT(DISTINCT o.id) AS order_count,
                                 COALESCE(SUM(oi.price * oi.qty), 0) AS revenue,
                                 COALESCE(SUM(oi.qty), 0) AS items_sold
                          FROM order_items oi
                          JOIN orders o ON o.id = oi.order_id AND o.status = "paid"
                          JOIN products p ON p.id = oi.product_id
                          WHERE p.restaurant_id IS NOT NULL
                          GROUP BY p.restaurant_id');
foreach ($orderStmt as $row) {
    $orderStats[(int)$row['rid']] = [
        'order_count' => (int)$row['order_count'],
        'revenue' => (float)$row['revenue'],
        'items_sold' => (int)$row['items_sold'],
    ];
}

$restaurantsData = [];
$totalDishes = 0;
$totalOrders = 0;
$totalRevenue = 0.0;
$totalReviewCount = 0;
$weightedRating = 0.0;
$topRestaurant = null;

foreach ($restaurantRows as $row) {
    $rid = (int)$row['id'];
    $prodIds = $productMap[$rid] ?? [];
    $productCount = $productCounts[$rid] ?? 0;
    $stats = $orderStats[$rid] ?? ['order_count' => 0, 'revenue' => 0.0, 'items_sold' => 0];
    $reviewStats = review_restaurant_stats($prodIds);

    $restaurantsData[] = [
        'id' => $rid,
        'name' => $row['name'],
        'address' => $row['address'],
        'phone' => $row['phone'],
        'created_at' => $row['created_at'],
        'product_count' => $productCount,
        'order_count' => $stats['order_count'],
        'revenue' => $stats['revenue'],
        'items_sold' => $stats['items_sold'],
        'review_average' => $reviewStats['average'],
        'review_total' => $reviewStats['total'],
    ];

    $totalDishes += $productCount;
    $totalOrders += $stats['order_count'];
    $totalRevenue += $stats['revenue'];
    $totalReviewCount += $reviewStats['total'];
    $weightedRating += $reviewStats['average'] * $reviewStats['total'];

    if ($topRestaurant === null || $stats['revenue'] > $topRestaurant['revenue']) {
        $topRestaurant = [
            'name' => $row['name'],
            'revenue' => $stats['revenue'],
        ];
    }
}

$overallRating = $totalReviewCount > 0 ? $weightedRating / $totalReviewCount : 0.0;
$totalRestaurants = count($restaurantsData);
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Admin - Nhà hàng</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --admin-primary: #6366f1;
      --admin-accent: #10b981;
      --admin-danger: #ef4444;
      --admin-border: rgba(148,163,184,0.25);
    }
    body {
      min-height: 100vh;
      margin: 0;
      padding: 2.5rem 1rem 3rem;
      background: radial-gradient(circle at top left, rgba(99,102,241,0.12), transparent 55%),
                  radial-gradient(circle at bottom right, rgba(16,185,129,0.15), transparent 45%),
                  #f8fafc;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      color: #0f172a;
    }
    .admin-shell { max-width: 1200px; margin: 0 auto; }
    .page-header { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: flex-start; margin-bottom: 1.8rem; }
    .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: .4rem; }
    .page-header p { margin: 0; color: #64748b; }
    .stats-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem; }
    .stat-card { background: rgba(255,255,255,0.82); border: 1px solid var(--admin-border); border-radius: 20px; padding: 1.25rem; box-shadow: 0 26px 60px -38px rgba(30,64,175,0.55); }
    .stat-card .label { text-transform: uppercase; font-size: .7rem; letter-spacing: .08em; color: #94a3b8; }
    .stat-card .value { font-size: 1.6rem; font-weight: 700; margin-top: .35rem; color: var(--admin-primary); }
    .stat-card .value.accent { color: var(--admin-accent); }
    .stat-card .value.danger { color: var(--admin-danger); }
    .glass-card { background: rgba(255,255,255,0.9); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; border: 1px solid var(--admin-border); box-shadow: 0 30px 70px -45px rgba(15,23,42,0.55); backdrop-filter: blur(10px); }
    .table-card { background: rgba(255,255,255,0.95); border: 1px solid var(--admin-border); border-radius: 24px; box-shadow: 0 24px 55px -42px rgba(15,23,42,0.5); overflow: hidden; }
    .table thead { background: linear-gradient(140deg, rgba(99,102,241,0.12), rgba(16,185,129,0.12)); text-transform: uppercase; font-size: .68rem; letter-spacing: .1em; }
    .table thead th { border-bottom: none; color: #475569; padding-top: .85rem; padding-bottom: .85rem; }
    .table tbody tr { border-bottom: 1px solid rgba(148,163,184,0.18); }
    .table tbody tr:last-child { border-bottom: none; }
    .table td, .table th { border-top: none; }
    .contact { font-size: .9rem; color: #64748b; }
    .badge-pill { padding: .4rem .75rem; border-radius: 999px; font-weight: 600; background: rgba(99,102,241,0.12); color: var(--admin-primary); font-size: .75rem; }
    .badge-pill.success { background: rgba(16,185,129,0.16); color: var(--admin-accent); }
    .rating-display { position: relative; display: inline-block; font-size: 1rem; line-height: 1; }
    .rating-display .stars { color: #cbd5f5; }
    .rating-display .stars.fill { color: #fbbf24; position: absolute; top: 0; left: 0; overflow: hidden; white-space: nowrap; }
    .alert { border-radius: 16px; border: none; box-shadow: 0 20px 45px -38px rgba(15,23,42,0.35); }
    @media (max-width: 768px) {
      body { padding: 2rem 1rem; }
      .glass-card { padding: 1.5rem; }
    }
  </style>
</head>
<body>
  <div class="admin-shell">
    <div class="page-header">
      <div>
        <a class="btn btn-outline-secondary btn-sm mb-3" href="index.php">← Về Dashboard</a>
        <h1>Quản lý nhà hàng</h1>
        <p>Theo dõi đối tác, doanh thu và cảm nhận khách hàng.</p>
      </div>
      <?php if ($topRestaurant): ?>
        <div class="stat-card" style="min-width:220px">
          <div class="label">Top doanh thu</div>
          <div class="value" style="font-size:1rem; color:#0f172a;"><?= e($topRestaurant['name']) ?></div>
          <div class="mt-2 fw-semibold text-success"><?= money($topRestaurant['revenue']) ?></div>
        </div>
      <?php endif; ?>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="label">Nhà hàng</div>
        <div class="value"><?= number_format($totalRestaurants) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Tổng món ăn</div>
        <div class="value"><?= number_format($totalDishes) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Đơn đã thanh toán</div>
        <div class="value accent"><?= number_format($totalOrders) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Doanh thu</div>
        <div class="value"><?= money($totalRevenue) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Điểm trung bình</div>
        <div class="value danger"><?= number_format($overallRating, 1) ?></div>
      </div>
    </div>

    <?php if ($msg = flash('ok')): ?>
      <div class="alert alert-success"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
      <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($formErrors): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($formErrors as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="glass-card">
      <h2 class="h5 fw-semibold mb-3">Thêm nhà hàng mới</h2>
      <form method="post" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Tên nhà hàng</label>
          <input class="form-control" name="name" value="<?= e($formValues['name']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Địa chỉ</label>
          <input class="form-control" name="address" value="<?= e($formValues['address']) ?>" placeholder="Số nhà, đường, quận...">
        </div>
        <div class="col-md-4">
          <label class="form-label">Số điện thoại</label>
          <input class="form-control" name="phone" value="<?= e($formValues['phone']) ?>" placeholder="VD: 0901234567">
        </div>
        <div class="col-12">
          <button class="btn btn-primary px-4">Thêm đối tác</button>
        </div>
      </form>
    </div>

    <div class="table-card">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>Nhà hàng</th>
              <th>Sản phẩm</th>
              <th>Đơn &amp; doanh thu</th>
              <th>Đánh giá</th>
              <th class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($restaurantsData): ?>
              <?php foreach ($restaurantsData as $item): ?>
                <?php $fill = max(0, min(100, ($item['review_average'] / 5) * 100)); ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?= e($item['name']) ?></div>
                    <div class="contact">
                      <?php if (!empty($item['address'])): ?>
                        <div><span class="badge-pill">Địa chỉ</span> <?= e($item['address']) ?></div>
                      <?php endif; ?>
                      <?php if (!empty($item['phone'])): ?>
                        <div><span class="badge-pill">Liên hệ</span> <?= e($item['phone']) ?></div>
                      <?php endif; ?>
                      <div><span class="badge-pill">Gia nhập</span> <?= date('d/m/Y', strtotime($item['created_at'])) ?></div>
                    </div>
                  </td>
                  <td>
                    <div class="badge-pill success"><?= number_format($item['product_count']) ?> món</div>
                    <div class="text-secondary small mt-2">Đã bán <?= number_format($item['items_sold']) ?> phần</div>
                  </td>
                  <td>
                    <div class="fw-semibold"><?= money($item['revenue']) ?></div>
                    <div class="text-secondary small"><?= number_format($item['order_count']) ?> đơn đã thanh toán</div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="rating-display">
                        <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                        <div class="stars fill" style="width: <?= $fill ?>%;">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                      </div>
                      <div>
                        <div class="fw-semibold"><?= number_format($item['review_average'], 1) ?></div>
                        <div class="text-secondary small"><?= number_format($item['review_total']) ?> đánh giá</div>
                      </div>
                    </div>
                  </td>
                  <td class="text-end">
                    <form method="post" onsubmit="return confirm('Xóa nhà hàng này?');">
                      <button class="btn btn-sm btn-outline-danger" name="delete" value="<?= e($item['id']) ?>">Xóa</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center py-5 text-secondary">Chưa có nhà hàng nào. Hãy thêm đối tác đầu tiên!</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
