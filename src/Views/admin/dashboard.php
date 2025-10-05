<?php include __DIR__.'/../partials/header.php'; ?>
<?php
$paidOrders = (int)($summary['paid_orders'] ?? 0);
$totalOrders = (int)($summary['total_orders'] ?? 0);
$pendingOrders = (int)($summary['pending_orders'] ?? 0);
$cancelledOrders = (int)($summary['cancelled_orders'] ?? 0);
$totalRevenue = (float)($summary['revenue'] ?? 0);
$totalCustomers = (int)($totalCustomers ?? 0);
$totalRestaurants = (int)($totalRestaurants ?? 0);
$totalProducts = (int)($totalProducts ?? 0);
$chartLabelsJson = json_encode($chartData['labels'] ?? [], JSON_UNESCAPED_UNICODE);
$chartRevenueJson = json_encode($chartData['revenue'] ?? [], JSON_UNESCAPED_UNICODE);
$chartOrdersJson = json_encode($chartData['orders'] ?? [], JSON_UNESCAPED_UNICODE);
$topProductLabelsJson = json_encode(array_map(function($p){ return $p['name']; }, $topProducts ?? []), JSON_UNESCAPED_UNICODE);
$topProductQtyJson = json_encode(array_map(function($p){ return (int)($p['qty'] ?? 0); }, $topProducts ?? []), JSON_UNESCAPED_UNICODE);
$topProductRevenueJson = json_encode(array_map(function($p){ return (float)($p['revenue'] ?? 0); }, $topProducts ?? []), JSON_UNESCAPED_UNICODE);
?>
<style>
  .dash-shell{max-width:1200px;margin:0 auto;}
  .dash-header{display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;margin-bottom:2rem;}
  .dash-header h1{font-size:2rem;font-weight:700;margin:0;}
  .dash-header p{margin:0;color:#64748b;}
  .dash-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));}
  .dash-card{background:#fff;border-radius:22px;padding:1.5rem;box-shadow:0 24px 60px -45px rgba(15,23,42,0.4);border:1px solid rgba(148,163,184,0.25);}
  .dash-card .label{text-transform:uppercase;font-size:.65rem;letter-spacing:.08em;color:#94a3b8;}
  .dash-card .value{font-size:1.6rem;font-weight:700;color:#1d4ed8;margin-top:.4rem;}
  .dash-card small{color:#94a3b8;}
  .dash-section{background:#fff;border-radius:24px;padding:1.75rem;border:1px solid rgba(148,163,184,0.25);box-shadow:0 26px 60px -45px rgba(15,23,42,0.45);margin-top:2rem;}
  .dash-section h2{font-size:1.2rem;font-weight:600;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
  .top-list li{display:flex;justify-content:space-between;align-items:center;padding:.65rem 0;border-bottom:1px solid rgba(148,163,184,0.18);}
  .top-list li:last-child{border-bottom:none;}
  .top-list .meta{color:#64748b;font-size:.85rem;}
  .badge-soft{border-radius:999px;padding:.3rem .7rem;font-size:.75rem;font-weight:600;background:rgba(37,99,235,0.12);color:#1d4ed8;}
  .dash-actions{margin-top:1.5rem;display:flex;gap:.75rem;flex-wrap:wrap;}
  @media(max-width:768px){
    .dash-header{flex-direction:column;align-items:flex-start;}
  }
</style>
<div class="dash-shell">
  <div class="dash-header">
    <div>
      <h1>👑 Bảng điều khiển</h1>
      <p>Ảnh tổng quan doanh thu và hoạt động của hệ thống.</p>
    </div>
    <div class="dash-actions">
      <a class="btn btn-primary" href="?r=admin/orders">Quản lý đơn hàng</a>
      <a class="btn btn-outline-secondary" href="?r=admin/products">Quản lý món ăn</a>
      <a class="btn btn-outline-secondary" href="../admin/restaurants.php">Quản lý nhà hàng</a>
    </div>
  </div>

  <div class="dash-grid">
    <div class="dash-card">
      <div class="label">Đơn đã thanh toán</div>
      <div class="value"><?= number_format($paidOrders) ?></div>
      <small>Tổng số đơn trạng thái "Đã thanh toán"</small>
    </div>
    <div class="dash-card">
      <div class="label">Doanh thu</div>
      <div class="value"><?= money($totalRevenue) ?></div>
      <small>Tính tới hiện tại</small>
    </div>
    <div class="dash-card">
      <div class="label">Đơn chờ xử lý</div>
      <div class="value"><?= number_format($pendingOrders) ?></div>
      <small>Cần xác nhận</small>
    </div>
    <div class="dash-card">
      <div class="label">Đơn đã hủy</div>
      <div class="value"><?= number_format($cancelledOrders) ?></div>
      <small>Bị khách hoặc admin hủy</small>
    </div>
    <div class="dash-card">
      <div class="label">Khách hàng</div>
      <div class="value"><?= number_format($totalCustomers) ?></div>
      <small>Tài khoản khách đang hoạt động</small>
    </div>
    <div class="dash-card">
      <div class="label">Nhà hàng đối tác</div>
      <div class="value"><?= number_format($totalRestaurants) ?></div>
      <small>Số lượng nhà hàng đang mở bán</small>
    </div>
    <div class="dash-card">
      <div class="label">Món ăn trên hệ thống</div>
      <div class="value"><?= number_format($totalProducts) ?></div>
      <small>Tổng số món đang phục vụ</small>
    </div>
  </div>

  <div class="dash-section">
    <h2>📈 Doanh số 6 tháng gần đây</h2>
    <canvas id="revenueChart" height="220"></canvas>
  </div>

  <div class="dash-grid" style="margin-top:2rem;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
    <div class="dash-section">
      <h2>🍲 Top món bán chạy</h2>
      <?php if(!empty($topProducts)): ?>
        <ul class="top-list list-unstyled mb-0">
          <?php foreach($topProducts as $index => $item): ?>
            <li>
              <div>
                <span class="badge-soft me-2">#<?= $index+1 ?></span>
                <span class="fw-semibold"><?= e($item['name']) ?></span>
                <div class="meta"><?= number_format($item['qty']) ?> phần · <?= money($item['revenue']) ?></div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="text-secondary">Chưa có dữ liệu đơn hàng.</div>
      <?php endif; ?>
      <canvas id="topProductChart" height="220" class="mt-3"></canvas>
    </div>
    <div class="dash-section">
      <h2>🧑‍🤝‍🧑 Khách hàng thân thiết</h2>
      <?php if(!empty($topCustomers)): ?>
        <ul class="top-list list-unstyled mb-0">
          <?php foreach($topCustomers as $index => $cus): ?>
            <li>
              <div>
                <span class="badge-soft me-2">#<?= $index+1 ?></span>
                <span class="fw-semibold"><?= e($cus['name']) ?></span>
                <div class="meta"><?= number_format($cus['orders']) ?> đơn · <?= money($cus['revenue']) ?></div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="text-secondary">Chưa có khách hàng nổi bật.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
  (function(){
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
      const labels = <?= $chartLabelsJson ?>;
      const revenueData = <?= $chartRevenueJson ?>;
      const orderData = <?= $chartOrdersJson ?>;
      const ctx = revenueCtx.getContext('2d');
      const gradient = ctx.createLinearGradient(0,0,0,220);
      gradient.addColorStop(0,'rgba(37,99,235,0.35)');
      gradient.addColorStop(1,'rgba(37,99,235,0)');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Doanh thu (đ)',
              data: revenueData,
              borderColor: '#1d4ed8',
              backgroundColor: gradient,
              borderWidth: 3,
              tension: 0.35,
              fill: true,
              yAxisID: 'y'
            },
            {
              label: 'Số đơn',
              data: orderData,
              borderColor: '#f97316',
              backgroundColor: '#f97316',
              tension: 0.35,
              borderDash: [6,6],
              fill: false,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          interaction: { intersect:false, mode:'index' },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { callback: value => new Intl.NumberFormat('vi-VN').format(value) + ' đ' }
            },
            y1: {
              beginAtZero: true,
              position: 'right',
              grid: { drawOnChartArea:false },
              ticks: { callback: value => new Intl.NumberFormat('vi-VN').format(value) }
            }
          }
        }
      });
    }

    const topCtx = document.getElementById('topProductChart');
    if (topCtx) {
      const labels = <?= $topProductLabelsJson ?>;
      const qty = <?= $topProductQtyJson ?>;
      const revenue = <?= $topProductRevenueJson ?>;
      if (labels.length > 0) {
        new Chart(topCtx, {
          type: 'bar',
          data: {
            labels,
            datasets: [
              {
                label: 'Số phần',
                data: qty,
                backgroundColor: '#22c55e',
                borderRadius: 8,
                yAxisID: 'y'
              },
              {
                label: 'Doanh thu (đ)',
                data: revenue,
                type: 'line',
                borderColor: '#f97316',
                backgroundColor: 'rgba(249,115,22,0.15)',
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                yAxisID: 'y1'
              }
            ]
          },
          options: {
            responsive: true,
            interaction: { intersect:false, mode:'index' },
            scales: {
              y: {
                beginAtZero: true,
                ticks: { callback: value => new Intl.NumberFormat('vi-VN').format(value) }
              },
              y1: {
                beginAtZero: true,
                position: 'right',
                grid: { drawOnChartArea:false },
                ticks: { callback: value => new Intl.NumberFormat('vi-VN').format(value) + ' đ' }
              }
            }
          }
        });
      } else {
        const listEl = topCtx.closest('.dash-section').querySelector('.top-list');
        if (listEl) { listEl.classList.add('mb-0'); }
        topCtx.remove();
      }
    }
  })();
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>
