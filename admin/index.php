<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../src/helpers.php';
require_once __DIR__.'/../src/auth.php';
require_once __DIR__.'/../src/reviews.php';
require_once __DIR__.'/../src/payments.php';

require_login();
if(!is_admin()){ die('403 Forbidden'); }

$pdo = db();
reviews_init();
payments_ensure_tables();

$orderAgg = $pdo->query("SELECT \n  SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) AS paid_orders,\n  SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending_orders,\n  SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled_orders,\n  COALESCE(SUM(CASE WHEN status='paid' THEN total ELSE 0 END),0) AS revenue\nFROM orders")->fetch();

$customerCount   = (int)($pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn());
$restaurantCount = (int)($pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn());
$reviewCount     = (int)($pdo->query("SELECT COUNT(*) FROM product_reviews")->fetchColumn());
$avgRating       = (float)($pdo->query("SELECT COALESCE(AVG(rating),0) FROM product_reviews")->fetchColumn());

$topProducts = $pdo->query("SELECT p.id, p.name, COALESCE(SUM(oi.qty),0) AS qty, COALESCE(SUM(oi.qty*oi.price),0) AS revenue\nFROM products p\nLEFT JOIN order_items oi ON oi.product_id = p.id\nLEFT JOIN orders o ON o.id = oi.order_id AND o.status='paid'\nGROUP BY p.id, p.name\nORDER BY qty DESC, revenue DESC\nLIMIT 5")->fetchAll();

$recentOrders = $pdo->query("SELECT o.id, o.total, o.status, o.created_at, o.address,\n       COALESCE(u.name, u.email) AS customer_name, u.email\nFROM orders o\nLEFT JOIN users u ON u.id = o.user_id\nORDER BY o.id DESC\nLIMIT 6")->fetchAll();

$restaurantBoard = $pdo->query("SELECT r.id, r.name,\n       COUNT(DISTINCT o.id) AS orders,\n       COALESCE(SUM(oi.qty),0) AS items,\n       COALESCE(SUM(oi.qty*oi.price),0) AS revenue\nFROM restaurants r\nLEFT JOIN products p ON p.restaurant_id = r.id\nLEFT JOIN order_items oi ON oi.product_id = p.id\nLEFT JOIN orders o ON o.id = oi.order_id AND o.status='paid'\nGROUP BY r.id, r.name\nORDER BY revenue DESC\nLIMIT 5")->fetchAll();

$recentReviews = $pdo->query("SELECT pr.rating, pr.comment, pr.updated_at,\n       p.name AS product_name, COALESCE(u.name, u.email) AS customer\nFROM product_reviews pr\nJOIN products p ON p.id = pr.product_id\nJOIN users u ON u.id = pr.user_id\nORDER BY pr.updated_at DESC\nLIMIT 5")->fetchAll();

$chartRows = $pdo->query("SELECT DATE_FORMAT(created_at,'%Y-%m') ym, SUM(total) revenue, COUNT(*) orders\nFROM orders\nWHERE status='paid' AND created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 5 MONTH)\nGROUP BY ym\nORDER BY ym")->fetchAll(PDO::FETCH_ASSOC);
$chartMap = [];
foreach ($chartRows as $row) {
  $chartMap[$row['ym']] = $row;
}
$labels = []; $revenues = []; $ordersSeries = [];
for ($i = 5; $i >= 0; $i--) {
  $month = date('Y-m', strtotime("-$i months"));
  $labels[] = date('m/Y', strtotime("-$i months"));
  $revenues[] = (float)($chartMap[$month]['revenue'] ?? 0);
  $ordersSeries[] = (int)($chartMap[$month]['orders'] ?? 0);
}
$chartLabelsJson = json_encode($labels, JSON_UNESCAPED_UNICODE);
$chartRevenueJson = json_encode($revenues, JSON_UNESCAPED_UNICODE);
$chartOrdersJson = json_encode($ordersSeries, JSON_UNESCAPED_UNICODE);
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Admin - Bảng điều khiển</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --dash-primary: #2563eb;
      --dash-accent: #10b981;
      --dash-danger: #ef4444;
      --dash-border: rgba(148,163,184,0.25);
    }
    body {
      margin: 0;
      min-height: 100vh;
      padding: 2.5rem 1rem 3rem;
      background: radial-gradient(circle at top left, rgba(59,130,246,0.1), transparent 55%),
                  radial-gradient(circle at bottom right, rgba(16,185,129,0.12), transparent 45%),
                  #f8fafc;
      color: #0f172a;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .admin-shell { max-width: 1200px; margin: 0 auto; }
    .page-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 2rem; }
    .page-header h1 { margin: 0; font-size: 2rem; font-weight: 700; }
    .page-header p { margin: 0; color: #64748b; }
    .stats-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem; }
    .stat-card { background: rgba(255,255,255,0.88); border-radius: 20px; padding: 1.25rem; border: 1px solid var(--dash-border); box-shadow: 0 26px 60px -40px rgba(37,99,235,0.35); }
    .stat-card .label { text-transform: uppercase; font-size: .68rem; letter-spacing: .08em; color: #94a3b8; }
    .stat-card .value { font-size: 1.55rem; font-weight: 700; margin-top: .35rem; }
    .stat-card.primary .value { color: var(--dash-primary); }
    .stat-card.accent .value { color: var(--dash-accent); }
    .stat-card.danger .value { color: var(--dash-danger); }
    .glass { background: rgba(255,255,255,0.92); border-radius: 24px; border: 1px solid var(--dash-border); box-shadow: 0 26px 60px -45px rgba(15,23,42,0.5); backdrop-filter: blur(10px); }
    .glass h2 { font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
    .table { margin: 0; }
    .table thead { background: linear-gradient(120deg, rgba(37,99,235,0.08), rgba(59,130,246,0.12)); text-transform: uppercase; font-size: .7rem; letter-spacing: .08em; }
    .table thead th { border-bottom: none; color: #475569; }
    .badge-status { border-radius: 999px; padding: .35rem .75rem; font-weight: 600; }
    .rating-icon { color: #fbbf24; }
    .list-divider { border-top: 1px solid rgba(148,163,184,0.2); margin: 1.25rem 0; }
    @media (max-width: 768px) {
      body { padding: 2rem 1rem; }
      .glass { border-radius: 18px; }
    }
  </style>
</head>
<body>
  <div class="admin-shell">

    <div class="page-header">
      <div>

        <h1>Bảng điều khiển</h1>
        <p>Toàn cảnh hoạt động của FoodShop hôm nay.</p>
      </div>

      <div class="d-flex gap-2 align-items-center">
        <a class="btn btn-outline-secondary btn-sm" href="../public/index.php">← Về trang bán hàng</a>
      </div>
    </div>


    <div class="stats-grid">
      <div class="stat-card primary">
        <div class="label">Đơn đã thanh toán</div>
        <div class="value"><?= number_format($orderAgg['paid_orders'] ?? 0) ?></div>
      </div>
      <div class="stat-card primary">
        <div class="label">Doanh thu</div>
        <div class="value"><?= money($orderAgg['revenue'] ?? 0) ?></div>
      </div>
      <div class="stat-card accent">
        <div class="label">Đơn chờ xử lý</div>
        <div class="value"><?= number_format($orderAgg['pending_orders'] ?? 0) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Khách hàng</div>
        <div class="value"><?= number_format($customerCount) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Nhà hàng</div>
        <div class="value"><?= number_format($restaurantCount) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Đánh giá (⭐ <?= number_format($avgRating,1) ?>)</div>
        <div class="value"><?= number_format($reviewCount) ?></div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-7">
        <div class="glass p-4 h-100">
          <h2>🧾 Đơn gần đây</h2>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>Đơn</th>
                  <th>Khách</th>
                  <th>Tổng</th>
                  <th>Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($recentOrders): ?>
                  <?php foreach ($recentOrders as $order): ?>
                    <?php
                      $statusClass = $order['status'] === 'paid' ? 'bg-success' : ($order['status']==='cancelled' ? 'bg-secondary' : 'bg-warning text-dark');
                    ?>
                    <tr>
                      <td>
                        <div class="fw-semibold">#<?= e($order['id']) ?></div>
                        <div class="text-secondary small"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                      </td>
                      <td>
                        <div class="fw-semibold"><?= e($order['customer_name'] ?: 'Khách lạ') ?></div>
                        <div class="text-secondary small"><?= e($order['email'] ?? '') ?></div>
                      </td>
                      <td class="fw-semibold"><?= money($order['total']) ?></td>
                      <td><span class="badge badge-status <?= $statusClass ?>"><?= e($order['status']) ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="4" class="text-center text-secondary py-4">Chưa có đơn hàng.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="text-end mt-3"><a class="btn btn-sm btn-outline-primary" href="orders.php">Xem tất cả đơn</a></div>
        </div>
      </div>
      <div class="col-xl-5">
        <div class="glass p-4 mb-4">
          <h2>🍲 Món bán chạy</h2>
          <?php if ($topProducts): ?>
            <ul class="list-unstyled mb-0">
              <?php foreach ($topProducts as $prod): ?>
                <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                  <div>
                    <div class="fw-semibold"><?= e($prod['name']) ?></div>
                    <div class="text-secondary small"><?= number_format($prod['qty']) ?> phần · <?= money($prod['revenue']) ?></div>
                  </div>
                  <a class="btn btn-sm btn-outline-secondary" href="products.php#product-<?= e($prod['id']) ?>">Chi tiết</a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="text-secondary">Chưa có dữ liệu bán hàng.</div>
          <?php endif; ?>
        </div>
        <div class="glass p-4">
          <h2>🏪 Đối tác nổi bật</h2>
          <?php if ($restaurantBoard): ?>
            <ul class="list-unstyled mb-0">
              <?php foreach ($restaurantBoard as $res): ?>
                <li class="py-2 border-bottom">
                  <div class="fw-semibold"><?= e($res['name']) ?></div>
                  <div class="text-secondary small"><?= number_format($res['orders']) ?> đơn · <?= money($res['revenue']) ?> · <?= number_format($res['items']) ?> phần</div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="text-secondary">Chưa có số liệu.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="glass p-4 mt-4">
      <h2>📈 Doanh số 6 tháng gần đây</h2>
      <canvas id="revenueChart" height="220"></canvas>
    </div>

    <div class="glass p-4 mt-4">
      <h2>⭐ Đánh giá mới</h2>
      <?php if ($recentReviews): ?>
        <div class="row g-3">
          <?php foreach ($recentReviews as $review): ?>
            <div class="col-md-4">
              <div class="border rounded-4 p-3 h-100" style="border-color: var(--dash-border);">
                <div class="d-flex justify-content-between mb-2">
                  <div class="fw-semibold"><?= e($review['product_name']) ?></div>
                  <div class="rating-icon"><?= str_repeat('★', max(0, (int)$review['rating'])) ?><?= str_repeat('☆', max(0, 5 - (int)$review['rating'])) ?></div>
                </div>
                <div class="text-secondary small mb-2"><?= e($review['customer']) ?> · <?= date('d/m/Y', strtotime($review['updated_at'])) ?></div>
                <div class="small"><?= $review['comment'] ? nl2br(e($review['comment'])) : '<span class="text-muted">Không có nhận xét</span>' ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-secondary">Chưa có đánh giá nào.</div>
      <?php endif; ?>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const chartLabels = <?= $chartLabelsJson ?? json_encode([]) ?>;
    const chartRevenue = <?= $chartRevenueJson ?? json_encode([]) ?>;
    const chartOrders = <?= $chartOrdersJson ?? json_encode([]) ?>;
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
      const gradient = ctx.getContext('2d').createLinearGradient(0,0,0,220);
      gradient.addColorStop(0,'rgba(37,99,235,0.35)');
      gradient.addColorStop(1,'rgba(37,99,235,0)');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: chartLabels,
          datasets: [
            {
              label: 'Doanh thu (đ)',
              data: chartRevenue,
              borderColor: '#2563eb',
              backgroundColor: gradient,
              tension: 0.3,
              fill: true,
              yAxisID: 'y',
              borderWidth: 3
            },
            {
              label: 'Số đơn',
              data: chartOrders,
              borderColor: '#f97316',
              backgroundColor: '#f97316',
              tension: 0.3,
              fill: false,
              yAxisID: 'y1',
              borderDash: [6,6]
            }
          ]
        },
        options: {
          scales: {
            y: { beginAtZero: true, ticks: { callback: value => new Intl.NumberFormat('vi-VN').format(value) + ' đ' } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: value => new Intl.NumberFormat('vi-VN').format(value) } }
          },
          interaction: { intersect: false, mode: 'index' },
          plugins: { legend: { display: true }, tooltip: { callbacks: { label: ctx => { if (ctx.dataset.label === 'Doanh thu (đ)') { return ctx.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(ctx.parsed.y) + ' đ'; } return ctx.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(ctx.parsed.y); } } } }
        }
      });
    }
  </script>
</body>
</html>
