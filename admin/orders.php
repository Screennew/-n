<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/payments.php';

if (!is_admin()) {
    flash('error','Chỉ admin');
    redirect('auth/login');
}

$pdo = db();
payments_ensure_tables();

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'], $_POST['status'])){
    $id=(int)$_POST['id'];
    $status=in_array($_POST['status'],['pending','paid','cancelled'],true)?$_POST['status']:'pending';
    if($id>0){
        $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$status,$id]);
        $paymentStatus = $status === 'paid' ? 'paid' : ($status === 'cancelled' ? 'failed' : 'pending');
        $pdo->prepare('UPDATE order_payments SET status=?, updated_at=NOW() WHERE order_id=?')->execute([$paymentStatus,$id]);
        flash('ok','Đã cập nhật đơn #' . $id);
    } else {
        flash('error','Đơn không hợp lệ.');
    }
    header('Location: orders.php');
    exit;
}

$summary = $pdo->query("SELECT COUNT(*) AS total_orders,
                               SUM(CASE WHEN status='paid' THEN total ELSE 0 END) AS revenue_paid,
                               SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending_orders,
                               SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) AS paid_orders
                        FROM orders")->fetch();
$statusBreakdown = $pdo->query("SELECT status, COUNT(*) AS c FROM orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

$rows=$pdo->query('SELECT o.*, u.name AS account_name, u.email,
                          pay.method AS payment_method, pay.status AS payment_status
                   FROM orders o
                   LEFT JOIN users u ON u.id=o.user_id
                   LEFT JOIN order_payments pay ON pay.order_id=o.id
                   ORDER BY o.id DESC')->fetchAll();

$paymentMap = payment_methods_map();
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><title>Admin - Đơn hàng</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  :root { --admin-primary:#0ea5e9; --admin-border:rgba(148,163,184,0.25); }
  body{background:#f8fafc;min-height:100vh;padding:2.5rem 1rem;color:#0f172a;font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;}
  .admin-shell{max-width:1200px;margin:0 auto;}
  .page-header{display:flex;flex-wrap:wrap;gap:1rem;justify-content:space-between;align-items:flex-start;margin-bottom:1.8rem;}
  .page-header h1{font-size:2rem;font-weight:700;margin-bottom:.4rem;}
  .stats-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:2rem;}
  .stat-card{background:rgba(255,255,255,0.88);border-radius:20px;padding:1.25rem;border:1px solid var(--admin-border);box-shadow:0 24px 60px -40px rgba(14,165,233,0.55);}
  .stat-card .label{text-transform:uppercase;font-size:.68rem;letter-spacing:.08em;color:#94a3b8;}
  .stat-card .value{font-size:1.6rem;font-weight:700;color:var(--admin-primary);margin-top:.35rem;}
  .alert{border:none;border-radius:16px;box-shadow:0 20px 45px -38px rgba(15,23,42,0.35);}
  .table-card{background:#fff;border-radius:24px;border:1px solid var(--admin-border);box-shadow:0 26px 70px -44px rgba(15,23,42,0.55);overflow:hidden;}
  .table thead{background:linear-gradient(120deg,rgba(14,165,233,0.12),rgba(59,130,246,0.12));text-transform:uppercase;font-size:.68rem;letter-spacing:.08em;}
  .table thead th{border-bottom:none;color:#475569;padding-top:.9rem;padding-bottom:.9rem;}
  .badge-status{border-radius:999px;padding:.35rem .75rem;font-weight:600;}
  .payment-pill{border-radius:12px;padding:.35rem .6rem;background:rgba(14,165,233,0.12);color:#0f172a;font-size:.8rem;font-weight:600;display:inline-flex;align-items:center;gap:.35rem;}
  .table tbody tr{border-bottom:1px solid rgba(148,163,184,0.2);}
  .table tbody tr:last-child{border-bottom:none;}
  .table td, .table th{border-top:none;}
  .status-select{width:150px;}
  .action-buttons{display:flex;gap:.5rem;align-items:center;}

  @media(max-width:768px){body{padding:2rem 1rem;} .table-responsive{border-radius:0;} .action-buttons{flex-direction:column;align-items:flex-start;}}
</style>
</head><body>
<div class="admin-shell">
  <div class="page-header">
    <div>
      <a class="btn btn-outline-secondary btn-sm mb-3" href="index.php">← Về Dashboard</a>
      <h1>Quản lý đơn hàng</h1>
      <p class="text-secondary mb-0">Theo dõi trạng thái và phương thức thanh toán của từng đơn.</p>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="label">Tổng đơn</div>
      <div class="value"><?= number_format($summary['total_orders'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="label">Đơn chờ</div>
      <div class="value"><?= number_format($summary['pending_orders'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="label">Đơn đã thanh toán</div>
      <div class="value"><?= number_format($summary['paid_orders'] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="label">Doanh thu đã thu</div>
      <div class="value"><?= money($summary['revenue_paid'] ?? 0) ?></div>
    </div>
  </div>

  <?php if ($m = flash('ok')): ?>
    <div class="alert alert-success"><?= e($m) ?></div>
  <?php endif; ?>
  <?php if ($m = flash('error')): ?>
    <div class="alert alert-danger"><?= e($m) ?></div>
  <?php endif; ?>

  <div class="table-card">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Phương thức</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): ?>
            <?php foreach($rows as $r): ?>
              <?php
                $statusClass = $r['status'] === 'paid' ? 'bg-success' : ($r['status'] === 'cancelled' ? 'bg-secondary' : 'bg-warning text-dark');
                $method = $r['payment_method'] ?? 'cod';
                $methodInfo = $paymentMap[$method] ?? ['label' => strtoupper($method), 'icon' => '&#128179;'];
                $paymentStatus = $r['payment_status'] ?? 'pending';
              ?>
              <tr>
                <td>
                  <div class="fw-semibold">#<?= e($r['id']) ?></div>
                  <div class="text-secondary small"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></div>
                </td>
                <td>
                  <div class="fw-semibold"><?= e($r['customer_name'] ?? ($r['account_name'] ?? 'Khách lạ')) ?></div>
                  <div class="text-secondary small"><?= e($r['phone'] ?? '') ?></div>
                  <div class="text-secondary small"><?= e($r['address'] ?? '') ?></div>
                </td>
                <td>
                  <div class="payment-pill">
                    <span><?= $methodInfo['icon'] ?? '&#128179;' ?></span>
                    <span><?= e($methodInfo['label']) ?></span>
                  </div>
                  <?php $paymentText = ['pending'=>'Chờ thanh toán','paid'=>'Đã thanh toán','failed'=>'Thanh toán lỗi'][$paymentStatus] ?? ucfirst($paymentStatus); ?>
                  <div class="text-secondary small mt-1">Trạng thái: <?= e($paymentText) ?></div>
                </td>
                <td class="fw-semibold"><?= money($r['total']) ?></td>
                <td>
                  <span class="badge badge-status <?= $statusClass ?>"><?= e(order_status_text($r['status'])) ?></span>
                </td>
                <td>
                  <form method="post" class="action-buttons">
                    <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                    <select name="status" class="form-select form-select-sm status-select">
                      <option value="pending" <?= $r['status']=='pending'?'selected':'' ?>>Chờ xử lý</option>
                      <option value="paid" <?= $r['status']=='paid'?'selected':'' ?>>Đã thanh toán</option>
                      <option value="cancelled" <?= $r['status']=='cancelled'?'selected':'' ?>>Đã hủy</option>
                    </select>
                    <button class="btn btn-sm btn-primary">Cập nhật</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center py-5 text-secondary">Chưa có đơn hàng nào.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
