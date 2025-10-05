<?php include __DIR__.'/../partials/header.php'; ?>
<style>
  .order-card{border:1px solid rgba(148,163,184,0.25);border-radius:20px;padding:1.25rem;background:#fff;box-shadow:0 20px 55px -40px rgba(15,23,42,0.5);}
  .order-grid{display:grid;gap:1rem;}
  .badge-status{border-radius:999px;padding:.35rem .75rem;font-weight:600;}
  .payment-pill{border-radius:999px;padding:.3rem .7rem;background:rgba(99,102,241,0.12);font-size:.8rem;font-weight:600;display:inline-flex;align-items:center;gap:.35rem;}
  .order-meta{color:#64748b;font-size:.85rem;}
</style>
<h4 class="fw-bold mb-3">Đơn hàng của tôi</h4>
<div class="order-grid">
  <?php if ($orders): ?>
    <?php foreach ($orders as $o): ?>
      <?php
        $statusClass = $o['status']==='paid' ? 'bg-success' : ($o['status']==='cancelled' ? 'bg-secondary' : 'bg-warning text-dark');
        $method = $o['payment_method'] ?? 'cod';
        $methodInfo = $paymentMap[$method] ?? ['label' => strtoupper($method), 'icon' => '&#128179;'];
      ?>
      <div class="order-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <div class="fw-semibold">Đơn #<?= e($o['id']) ?></div>
            <div class="order-meta"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></div>
          </div>
          <span class="badge badge-status <?= $statusClass ?>"><?= e(order_status_text($o['status'])) ?></span>
        </div>
        <div class="order-meta mb-2">Địa chỉ: <?= e($o['address']) ?></div>
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-semibold">Tổng thanh toán: <?= money($o['total']) ?></div>
            <div class="small text-secondary">Đã giảm <?= money($o['discount']) ?> &bull; Phí ship <?= money($o['shipping']) ?></div>
          </div>
          <div class="payment-pill">
            <span><?= $methodInfo['icon'] ?? '&#128179;' ?></span>
            <span><?= e($methodInfo['label']) ?></span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="order-card text-center text-secondary">Bạn chưa có đơn hàng nào.</div>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
