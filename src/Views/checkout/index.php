<?php include __DIR__.'/../partials/header.php'; ?>
<style>
  .checkout-shell { display: grid; gap: 2rem; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }
  .panel { background: rgba(255,255,255,0.95); border-radius: 22px; padding: 1.75rem; border: 1px solid rgba(148,163,184,0.25); box-shadow: 0 28px 70px -45px rgba(15,23,42,0.55); }
  .panel h5 { font-weight: 600; margin-bottom: 1rem; }
  .input-floating { display: grid; gap: 1rem; }
  .payment-grid { display: grid; gap: 1rem; }
  .payment-option { border: 1px solid transparent; border-radius: 18px; padding: 1rem; display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; background: rgba(248,250,252,0.85); }
  .payment-option input { display: none; }
  .payment-option .badge { border-radius: 999px; font-size: .7rem; font-weight: 600; padding: .25rem .6rem; }
  .payment-option .icon { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 16px; background: rgba(99,102,241,0.12); font-size: 1.4rem; }
  .payment-option .info { flex: 1; }
  .payment-option .info .title { font-weight: 600; }
  .payment-option.selected { border-color: #6366f1; box-shadow: 0 20px 55px -35px rgba(99,102,241,0.55); transform: translateY(-3px); background: #fff; }
  .summary-card { background: linear-gradient(135deg, #f8fafc, #eef2ff); border-radius: 24px; padding: 1.75rem; border: 1px solid rgba(99,102,241,0.16); box-shadow: 0 28px 60px -40px rgba(30,64,175,0.55); }
  .summary-card .line { display: flex; justify-content: space-between; margin-bottom: .55rem; }
  .summary-card .line.total { font-size: 1.2rem; font-weight: 700; margin-top: 1rem; }
  .summary-items { display: grid; gap: .5rem; margin-bottom: .75rem; }
  .summary-item { display: flex; justify-content: space-between; font-size: .9rem; color: #475569; }
  .summary-item strong { color: #0f172a; }
  @media (max-width: 768px) { .panel, .summary-card { padding: 1.5rem; } }
</style>
<h4 class="fw-bold mb-4">Thanh toán</h4>
<form method="post" action="?r=checkout/place">
  <div class="checkout-shell">
    <div class="panel">
      <h5>Thông tin giao hàng</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Họ tên người nhận</label>
          <input class="form-control" name="name" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Số điện thoại</label>
          <input class="form-control" name="phone" required pattern="[0-9+ ]{8,15}" placeholder="VD: 0901234567">
        </div>
        <div class="col-12">
          <label class="form-label">Địa chỉ giao hàng</label>
          <input class="form-control" name="address" required placeholder="Số nhà, đường, phường/xã, quận/huyện">
        </div>
        <div class="col-12">
          <label class="form-label">Ghi chú cho tài xế (tuỳ chọn)</label>
          <input class="form-control" name="note" placeholder="Ví dụ: gọi trước khi đến">
        </div>
      </div>
      <hr class="my-4">
      <h5>Phương thức thanh toán</h5>
      <div class="payment-grid">
        <?php foreach ($paymentMethods as $method): ?>
          <?php $isActive = $selectedMethod === $method['value']; ?>
          <label class="payment-option <?= $isActive ? 'selected' : '' ?>">
            <input type="radio" name="payment_method" value="<?= e($method['value']) ?>" <?= $isActive ? 'checked' : '' ?>>
            <div class="icon"><?= $method['icon'] ?></div>
            <div class="info">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="title"><?= e($method['label']) ?></span>
                <span class="badge bg-light text-primary"><?= e($method['badge']) ?></span>
              </div>
              <div class="text-secondary small"><?= e($method['description']) ?></div>
            </div>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="summary-card">
      <h5 class="fw-semibold mb-3">Tóm tắt đơn</h5>
      <div class="summary-items">
        <?php foreach($items as $it): ?>
          <div class="summary-item">
            <span><?= e($it['name']) ?> x <?= e($it['qty']) ?></span>
            <strong><?= money($it['total']) ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="line"><span>Tạm tính</span><strong><?= money($totals['subtotal']) ?></strong></div>
      <div class="line"><span>Giảm giá</span><strong>-<?= money($totals['discount']) ?></strong></div>
      <div class="line"><span>Phí vận chuyển</span><strong><?= money($totals['shipping']) ?></strong></div>
      <div class="line total"><span>Tổng cộng</span><span><?= money($totals['total']) ?></span></div>
      <button class="btn btn-success btn-lg mt-4 w-100">Đặt hàng ngay</button>
      <div class="form-text mt-2">Đơn sẽ ở trạng thái <strong>chờ xác nhận</strong>. Khi cửa hàng xác nhận, trạng thái đổi sang <strong>paid</strong>.</div>
    </div>
  </div>
</form>
<script>
  document.querySelectorAll('.payment-option').forEach(function(option){
    option.addEventListener('click', function(){
      document.querySelectorAll('.payment-option').forEach(function(el){ el.classList.remove('selected'); });
      option.classList.add('selected');
      option.querySelector("input[type='radio']").checked = true;
    });
  });
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>
