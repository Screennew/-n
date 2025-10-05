<?php include __DIR__.'/../partials/header.php'; ?>
<?php $availableCoupons = $availableCoupons ?? []; ?>
<style>
  .cart-layout{display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));}
  .cart-card{border:1px solid rgba(148,163,184,0.25);border-radius:22px;background:#fff;box-shadow:0 24px 55px -40px rgba(15,23,42,0.45);}
  .cart-card h4{font-weight:700;margin-bottom:1rem;}
  .cart-items{display:flex;flex-direction:column;gap:1rem;padding:1.75rem;}
  .cart-item{display:flex;gap:1rem;align-items:center;border-bottom:1px solid rgba(148,163,184,0.18);padding-bottom:1rem;}
  .cart-item:last-child{border-bottom:none;padding-bottom:0;}
  .cart-item img{width:72px;height:72px;object-fit:cover;border-radius:18px;}
  .cart-item-title{font-weight:600;margin-bottom:.25rem;}
  .cart-item-meta{color:#64748b;font-size:.85rem;}
  .qty-input{width:90px;}
  .cart-empty{border:2px dashed rgba(148,163,184,0.45);border-radius:24px;padding:2rem;text-align:center;color:#94a3b8;background:#fff;}
  .summary-card{padding:1.75rem;display:flex;flex-direction:column;gap:1rem;}
  .summary-list .line{display:flex;justify-content:space-between;margin-bottom:.6rem;}
  .summary-list .line strong{font-size:1rem;}
  .summary-total{font-size:1.35rem;font-weight:700;color:#2563eb;}
  .cart-actions{display:flex;gap:.75rem;margin-top:1.5rem;flex-wrap:wrap;}
  .coupon-form{display:flex;gap:.75rem;flex-wrap:wrap;}
  .coupon-form .btn{border-radius:999px;padding:.5rem 1.2rem;}
  .voucher-badge{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;background:rgba(37,99,235,0.12);color:#1d4ed8;font-weight:600;padding:.25rem .8rem;font-size:.8rem;}
  .coupon-row{display:flex;align-items:center;gap:1rem;}
  .coupon-row + .coupon-row{border-top:1px solid rgba(148,163,184,0.18);padding-top:.8rem;margin-top:.8rem;}
  @media(max-width:576px){
    .cart-item{flex-direction:column;align-items:flex-start;}
    .cart-item img{width:100%;height:160px;}
    .qty-input{width:100%;}
  }
</style>
<h4 class="fw-bold mb-4">Giỏ hàng</h4>
<?php if (!$items): ?>
  <div class="cart-empty">
    <p class="mb-3">Giỏ hàng của bạn đang trống. Thêm vài món ngon nhé!</p>
    <a class="btn btn-primary" href="?r=home">Khám phá món ăn</a>
  </div>
<?php else: ?>
  <div class="cart-layout">
    <form method="post" action="?r=cart/update" class="cart-card cart-items">
      <h4>Món đã chọn</h4>
      <?php foreach($items as $it): ?>
        <div class="cart-item">
          <img src="<?= e($it['image_url']) ?>" alt="<?= e($it['name']) ?>"
               onerror="this.src='https://picsum.photos/seed/i<?= e($it['id']) ?>/160/120';">
          <div class="flex-grow-1">
            <div class="cart-item-title text-truncate"><?= e($it['name']) ?></div>
            <div class="cart-item-meta">Giá: <?= money($it['price']) ?></div>
          </div>
          <div class="text-end">
            <input class="form-control form-control-sm qty-input" type="number" min="1" name="qty[<?= e($it['id']) ?>]" value="<?= e($it['qty']) ?>">
            <div class="mt-2 fw-semibold"><?= money($it['total']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
      <div class="cart-actions">
        <button class="btn btn-outline-primary">⟳ Cập nhật số lượng</button>
        <a class="btn btn-success" href="?r=checkout">Thanh toán</a>
        <a class="btn btn-light" href="?r=home">Tiếp tục mua sắm</a>
      </div>
    </form>

    <div class="cart-card summary-card">
      <h4>Tổng kết đơn</h4>
      <?php $t=$totals; ?>
      <div class="summary-list">
        <div class="line"><span>Tạm tính</span><strong><?= money($t['subtotal']) ?></strong></div>
        <div class="line"><span>Giảm giá</span><strong>-<?= money($t['discount']) ?></strong></div>
        <div class="line"><span>Phí vận chuyển</span><strong><?= money($t['shipping']) ?></strong></div>
        <div class="line"><span>Tổng cộng</span><span class="summary-total"><?= money($t['total']) ?></span></div>
      </div>
      <form method="post" action="?r=cart/update" class="coupon-form">
        <input class="form-control" name="coupon" id="couponInput" placeholder="Nhập mã giảm giá">
        <button class="btn btn-outline-secondary">Áp dụng</button>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#couponModal">Chọn voucher</button>
      </form>
      <p class="small text-secondary mb-0">Áp dụng mã trước khi thanh toán để nhận ưu đãi tốt nhất.</p>
    </div>
  </div>
<?php endif; ?>

<div class="modal fade" id="couponModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ví voucher FoodShop</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if($availableCoupons): ?>
          <?php foreach($availableCoupons as $coupon): ?>
            <div class="coupon-row">
              <div class="flex-grow-1">
                <div class="voucher-badge">Mã <?= strtoupper(e($coupon['code'])) ?></div>
                <div class="fw-semibold mt-1">
                  <?= $coupon['type']==='percent' ? 'Giảm '.number_format($coupon['value']).'%' : 'Giảm '.money($coupon['value']) ?>
                </div>
                <div class="text-secondary small">Đơn tối thiểu: <?= isset($coupon['min_value']) ? money($coupon['min_value']) : '0 đ' ?></div>
                <?php if(!empty($coupon['expires_at'])): ?>
                  <div class="text-secondary small">Hết hạn: <?= date('d/m/Y', strtotime($coupon['expires_at'])) ?></div>
                <?php endif; ?>
              </div>
              <button class="btn btn-primary" type="button" data-apply-code="<?= e($coupon['code']) ?>">Dùng mã</button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-info mb-0">Chưa có mã giảm giá khả dụng.</div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Trở lại</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('[data-apply-code]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var code = btn.getAttribute('data-apply-code');
      var input = document.getElementById('couponInput');
      if(input){ input.value = code; }
      var modalEl = document.getElementById('couponModal');
      if(window.bootstrap){
        var instance = bootstrap.Modal.getInstance(modalEl);
        instance && instance.hide();
      }
    });
  });
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>
