<?php include __DIR__.'/../partials/header.php'; ?>
<style>
  .promo-hero{background:url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1600&q=80') center/cover;border-radius:28px;color:#fff;padding:3.5rem 2.5rem;margin-bottom:2.5rem;position:relative;overflow:hidden;}
  .promo-hero::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(15,23,42,0.65),rgba(59,130,246,0.35));}
  .promo-hero>*{position:relative;z-index:2;}
  .promo-hero h1{font-weight:800;font-size:2.3rem;}
  .promo-hero p{max-width:520px;font-size:1.05rem;}
  .promo-grid{display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));margin-bottom:3rem;}
  .promo-item{background:#fff;border-radius:22px;padding:1.5rem;border:1px solid rgba(148,163,184,0.2);box-shadow:0 26px 60px -40px rgba(15,23,42,0.45);position:relative;overflow:hidden;}
  .promo-item::before{content:'';position:absolute;top:-40px;right:-40px;width:120px;height:120px;border-radius:50%;background:rgba(37,99,235,0.12);} 
  .promo-item h3{font-weight:700;font-size:1.15rem;}
  .badge-type{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.35rem .75rem;background:rgba(37,99,235,0.12);color:#1d4ed8;font-weight:600;margin-bottom:.6rem;}
  .coupon-table{background:#fff;border-radius:22px;border:1px solid rgba(148,163,184,0.25);box-shadow:0 26px 55px -42px rgba(15,23,42,0.4);overflow:hidden;}
  .coupon-table table{margin:0;}
  .coupon-table thead{background:linear-gradient(135deg,rgba(59,130,246,0.12),rgba(59,130,246,0.2));text-transform:uppercase;font-size:.7rem;letter-spacing:.08em;color:#1f2937;}
  .coupon-table tbody tr:hover{background:rgba(59,130,246,0.05);}
</style>
<section class="promo-hero text-white">
  <h1>Ưu đãi hot hôm nay</h1>
  <p>Chọn voucher phù hợp để tiết kiệm hơn cho mỗi đơn hàng trung thu, Combo sốc và nhiều chương trình khác.</p>
</section>

<?php if($promos): ?>
<h2 class="fw-bold mb-3">🎁 Chương trình đang chạy (<?= count($promos) ?>)</h2>
<div class="promo-grid">
  <?php foreach($promos as $promo): ?>
    <div class="promo-item">
      <div class="badge-type">🎉 Voucher</div>
      <h3><?= e($promo['title']) ?></h3>
      <p class="text-secondary"><?= e($promo['description'] ?? 'Ưu đãi dành riêng cho bạn.') ?></p>
      <?php if(!empty($promo['expires_at'])): ?>
        <div class="small text-secondary">Hết hạn: <?= date('d/m/Y', strtotime($promo['expires_at'])) ?></div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<h2 class="fw-bold mb-3">🧧 Mã giảm giá</h2>
<div class="coupon-table mb-4" id="coupons">
  <table class="table align-middle">
    <thead>
      <tr><th>Mã</th><th>Loại</th><th>Giá trị</th><th>Hạn dùng</th><th></th></tr>
    </thead>
    <tbody>
      <?php if($coupons): ?>
        <?php foreach($coupons as $coupon): ?>
          <tr>
            <td class="fw-semibold"><?= e($coupon['code']) ?></td>
            <td><?= $coupon['type']==='percent' ? 'Giảm %' : 'Giảm tiền' ?></td>
            <td><?= $coupon['type']==='percent' ? number_format($coupon['value']).'%' : money($coupon['value']) ?></td>
            <td><?= $coupon['expires_at'] ? date('d/m/Y', strtotime($coupon['expires_at'])) : 'Không giới hạn' ?></td>
            <td><button class="btn btn-outline-primary btn-sm" data-code="<?= e($coupon['code']) ?>">Sao chép</button></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center text-secondary py-4">Chưa có mã giảm giá nào.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<script>
  document.querySelectorAll('[data-code]').forEach(function(btn){
    btn.addEventListener('click', function(){
      navigator.clipboard.writeText(btn.dataset.code).then(function(){
        btn.textContent = 'Đã sao chép';
        setTimeout(function(){ btn.textContent = 'Sao chép'; },1500);
      });
    });
  });
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>
