<?php include __DIR__.'/../partials/header.php'; ?>
<style>
  .home-hero{position:relative;padding:3rem 2.5rem;border-radius:36px;background:linear-gradient(135deg,#fde68a,#bfdbfe);overflow:hidden;margin-bottom:3rem;}
  .home-hero::after{content:'';position:absolute;top:-30%;right:-15%;width:420px;height:420px;background:radial-gradient(circle,rgba(255,255,255,0.75),rgba(255,255,255,0));border-radius:50%;}
  .home-hero>*{position:relative;z-index:2;}
  .home-hero h1{font-weight:800;font-size:2.5rem;max-width:560px;}
  .home-hero p{color:#1f2937;font-size:1.05rem;max-width:520px;}
  .moon-overlay{position:absolute;top:-40px;left:-60px;width:220px;height:220px;background:radial-gradient(circle,rgba(253,224,71,0.75),rgba(253,224,71,0));border-radius:50%;box-shadow:0 0 45px rgba(253,224,71,0.45);z-index:1;}
  .lantern{position:absolute;width:80px;height:120px;background:#fb7185;border-radius:40px 40px 20px 20px;top:30px;right:120px;box-shadow:0 14px 28px rgba(247,118,136,0.4);animation:floatLantern 3s ease-in-out infinite;display:flex;align-items:center;justify-content:center;}
  .lantern::before{content:'';position:absolute;top:-22px;left:50%;transform:translateX(-50%);width:6px;height:22px;background:#f97316;}
  .lantern::after{content:'';position:absolute;bottom:-12px;left:50%;transform:translateX(-50%);width:34px;height:12px;background:#f97316;border-radius:0 0 12px 12px;}
  .lantern span{font-size:1.4rem;}
  .hero-actions{display:flex;gap:.85rem;flex-wrap:wrap;margin-top:1.6rem;}
  .hero-actions .btn{border-radius:999px;padding:.65rem 1.4rem;font-weight:600;box-shadow:0 18px 40px -28px rgba(29,78,216,0.65);}
  .hero-stats{display:flex;gap:1rem;flex-wrap:wrap;margin-top:2.2rem;}
  .stat-pill{background:linear-gradient(135deg,rgba(255,255,255,0.92),rgba(255,255,255,0.72));border-radius:999px;padding:.75rem 1.35rem;display:flex;flex-direction:column;min-width:160px;border:1px solid rgba(255,255,255,0.65);backdrop-filter:blur(10px);box-shadow:0 18px 35px -28px rgba(15,23,42,0.4);}
  .stat-pill span:first-child{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#64748b;}
  .stat-pill span:last-child{font-size:1.45rem;font-weight:700;color:#1d4ed8;}
  .collection-section,.nearby-section{margin-bottom:3rem;}
  .section-title{font-weight:700;font-size:1.8rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;}
  .collection-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));}
  .collection-card{position:relative;border-radius:20px;overflow:hidden;height:140px;box-shadow:0 18px 35px -28px rgba(15,23,42,0.4);}
  .collection-card img{width:100%;height:100%;object-fit:cover;transition:transform .3s ease;}
  .collection-card:hover img{transform:scale(1.05);}
  .collection-card span{position:absolute;bottom:12px;left:16px;font-weight:600;color:#fff;text-shadow:0 2px 6px rgba(0,0,0,0.4);}
  #promotions{margin-bottom:3rem;}
  .promo-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;}
  .promo-grid{display:grid;gap:1.2rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));}
  .flash-section{margin-bottom:3rem;}
  .flash-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));}
  .flash-card{background:#fff;border-radius:20px;border:1px solid rgba(248,113,113,0.3);box-shadow:0 24px 55px -40px rgba(239,68,68,0.55);overflow:hidden;position:relative;}
  .flash-card img{height:150px;width:100%;object-fit:cover;}
  .flash-card-body{padding:1rem;display:flex;flex-direction:column;gap:.4rem;}
  .flash-countdown{font-weight:600;color:#ef4444;}
  .flash-price{font-size:1.2rem;font-weight:700;color:#ef4444;}
  .flash-original{text-decoration:line-through;color:#94a3b8;font-size:.85rem;}
  .promo-card{background:#fff;border-radius:22px;padding:1.2rem;border:1px solid rgba(148,163,184,0.2);box-shadow:0 24px 60px -40px rgba(15,23,42,0.45);}
  .promo-card h3{font-size:1.05rem;font-weight:700;margin-bottom:.35rem;}
  .promo-card p{color:#475569;min-height:44px;}
  .nearby-grid{display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));}
  .nearby-card{background:#fff;border-radius:22px;padding:1.5rem;border:1px solid rgba(148,163,184,0.2);box-shadow:0 24px 55px -40px rgba(15,23,42,0.4);}
  .nearby-card h3{font-weight:700;font-size:1.1rem;margin-bottom:.25rem;}
  .nearby-card .meta{color:#475569;font-size:.9rem;}
  .nearby-card .rating{color:#f59e0b;font-weight:600;}
  .product-section{margin-bottom:3.5rem;}
  .product-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;}
  .product-header h2{font-size:1.6rem;font-weight:700;margin:0;}
  .product-grid{display:grid;gap:1.2rem;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));}
  .product-card{background:#fff;border-radius:22px;border:1px solid rgba(148,163,184,0.18);box-shadow:0 24px 55px -42px rgba(15,23,42,0.4);overflow:hidden;display:flex;flex-direction:column;}
  .product-card img{height:170px;object-fit:cover;}
  .product-card .card-body{padding:1.1rem;display:flex;flex-direction:column;gap:.5rem;flex:1;}
  .price-rating{display:flex;justify-content:space-between;align-items:center;}
  .rating-badge{display:flex;align-items:center;gap:.25rem;font-size:.82rem;color:#fb923c;}
  .rating-badge .count{color:#64748b;font-size:.76rem;}
  .info-section{background:#fff;border-radius:24px;padding:2rem;margin-bottom:3.5rem;border:1px solid rgba(148,163,184,0.2);box-shadow:0 18px 40px -35px rgba(15,23,42,0.4);}
  .info-grid{display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));}
  .info-item h3{font-weight:700;font-size:1.1rem;margin-bottom:.5rem;}
  .info-item p{color:#475569;}
  .faq-section{margin-bottom:4rem;}
  .faq-item{background:#fff;border:1px solid rgba(148,163,184,0.18);border-radius:18px;padding:1.25rem;margin-bottom:1rem;box-shadow:0 20px 40px -38px rgba(15,23,42,0.4);}
  .faq-item h3{font-size:1rem;font-weight:700;margin-bottom:.5rem;}
  @keyframes floatLantern{0%{transform:translateY(0);}50%{transform:translateY(8px);}100%{transform:translateY(0);}}
  @media(max-width:768px){
    .home-hero{padding:2.4rem 1.75rem;}
    .home-hero h1{font-size:2.1rem;}
    .hero-actions{flex-direction:column;align-items:flex-start;}
    .product-header{flex-direction:column;align-items:flex-start;}
  }
</style>

<section class="home-hero">
  <div class="moon-overlay"></div>
  <div class="lantern"><span>🧧</span></div>
  <h1>Đặt đồ ăn mùa Trung Thu – ưu đãi siêu to cùng FoodShop</h1>
  <p>Khám phá hàng trăm nhà hàng đối tác, thanh toán tiện lợi và nhận ưu đãi độc quyền như trên ShopeeFood hay GrabFood.</p>
  <div class="hero-actions">
    <a class="btn btn-dark" href="?r=promotions/list">🎉 Ưu đãi nổi bật</a>
    <a class="btn btn-outline-dark" href="?r=cart">🛒 Ví voucher & giỏ hàng</a>
  </div>
  <div class="hero-stats">
    <div class="stat-pill"><span>Nhà hàng</span><span><?= number_format($stats['restaurants'] ?? 0) ?></span></div>
    <div class="stat-pill"><span>Món ăn</span><span><?= number_format($stats['products'] ?? 0) ?></span></div>
    <div class="stat-pill"><span>Đơn đã giao</span><span><?= number_format($stats['orders'] ?? 0) ?></span></div>
  </div>
</section>

<?php if(!empty($collections)): ?>
<section class="collection-section">
  <div class="section-title">🍱 Bộ sưu tập món ăn</div>
  <div class="collection-grid">
    <?php foreach($collections as $collection): ?>
      <a class="collection-card" href="?r=home&amp;category=<?= e($collection['id']) ?>">
        <img src="<?= e($collection['image']) ?>" alt="<?= e($collection['name']) ?>">
        <span><?= e($collection['name']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if(!empty($flashSales)): ?>
<section class="flash-section">
  <div class="section-title">⚡ Flash Sale đang diễn ra</div>
  <div class="flash-grid">
    <?php foreach($flashSales as $sale): ?>
      <div class="flash-card">
        <img src="<?= e($sale['image_url']) ?>" alt="<?= e($sale['name']) ?>" onerror="this.src='https://picsum.photos/seed/flash<?= e($sale['product_id']) ?>/600/400';">
        <div class="flash-card-body">
          <div class="fw-semibold text-truncate"><?= e($sale['name']) ?></div>
          <div class="small text-secondary text-truncate"><?= e($sale['restaurant'] ?? 'Nhà hàng') ?></div>
          <div class="flash-price"><?= money($sale['sale_price']) ?></div>
          <div class="flash-original"><?= money($sale['price']) ?></div>
          <div class="flash-countdown" data-countdown="<?= e($sale['end_at']) ?>"></div>
          <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-danger flex-fill" href="?r=product&id=<?= e($sale['product_id']) ?>">Xem chi tiết</a>
            <form method="post" action="?r=cart/add" class="m-0">
              <input type="hidden" name="id" value="<?= e($sale['product_id']) ?>">
              <input type="hidden" name="qty" value="1">
              <button class="btn btn-sm btn-danger">Chọn mua</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<section id="promotions">
  <div class="promo-header">
    <h2 class="section-title" style="margin-bottom:0;">🎁 Ưu đãi dành cho bạn</h2>
    <a class="btn btn-outline-primary btn-sm" href="?r=promotions/list">Xem tất cả</a>
  </div>
  <div class="promo-grid">
    <?php if(!empty($promos)): ?>
      <?php foreach($promos as $promo): ?>
        <div class="promo-card">
          <div class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 mb-2">Voucher</div>
          <h3><?= e($promo['title']) ?></h3>
          <p><?= e($promo['description'] ?? 'Ưu đãi dành riêng cho bạn.') ?></p>
          <?php if(!empty($promo['expires_at'])): ?>
            <div class="small text-secondary">Hết hạn: <?= date('d/m/Y', strtotime($promo['expires_at'])) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="alert alert-info">Hiện chưa có ưu đãi nào.</div>
    <?php endif; ?>
  </div>
</section>

<?php if(!empty($topSelling)): ?>
<section class="nearby-section">
  <div class="section-title">🏆 Món bán chạy nhất</div>
  <div class="product-grid">
    <?php foreach($topSelling as $top): ?>
      <div class="product-card">
        <img src="<?= e($top['image_url']) ?>" alt="<?= e($top['name']) ?>" onerror="this.src='https://picsum.photos/seed/top<?= e($top['id']) ?>/600/400';">
        <div class="card-body">
          <div class="fw-semibold text-truncate"><?= e($top['name']) ?></div>
          <div class="small text-secondary text-truncate"><?= e($top['restaurant']) ?></div>
          <div class="price-rating">
            <div class="fw-bold text-primary"><?= money($top['price']) ?></div>
            <div class="text-secondary small">Đã bán <?= number_format($top['sold'] ?? 0) ?></div>
          </div>
          <div class="d-flex gap-2 mt-auto">
            <a class="btn btn-outline-primary btn-sm flex-fill" href="?r=product&id=<?= e($top['id']) ?>">Xem chi tiết</a>
            <form method="post" action="?r=cart/add" class="m-0">
              <input type="hidden" name="id" value="<?= e($top['id']) ?>">
              <input type="hidden" name="qty" value="1">
              <button class="btn btn-primary btn-sm">🛒 Thêm</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<?php if(!empty($nearbyRestaurants)): ?>
<section class="nearby-section">
  <div class="section-title">🍽️ Quán ngon quanh đây</div>
  <div class="nearby-grid">
    <?php foreach($nearbyRestaurants as $res): ?>
      <div class="nearby-card">
        <h3><?= e($res['name']) ?></h3>
        <div class="meta mb-2"><?= e($res['address'] ?? 'Đang cập nhật') ?></div>
        <div class="meta mb-2">☎ <?= e($res['phone'] ?? '---') ?></div>
        <div class="meta">Đã bán: <?= number_format($res['items']) ?> phần</div>
        <div class="rating mt-2">⭐ <?= number_format($res['rating'],1) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="product-section">
  <div class="product-header">
    <h2>🔥 Món mới cập nhật</h2>
    <div class="text-secondary">Chọn món yêu thích và thêm vào giỏ chỉ với một cú chạm.</div>
  </div>
  <div class="product-grid">
    <?php if(!empty($products)): ?>
      <?php foreach($products as $p): ?>
        <div class="product-card">
          <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" onerror="this.src='https://picsum.photos/seed/home<?= e($p['id']) ?>/600/400';">
          <div class="card-body">
            <div class="fw-semibold text-truncate"><?= e($p['name']) ?></div>
            <div class="small text-secondary text-truncate"><?= e($p['restaurant']) ?></div>
            <div class="price-rating">
              <div class="fw-bold text-primary"><?= money($p['price']) ?></div>
              <?php
                $avg = (float)($p['avg_rating'] ?? 0);
                $count = (int)($p['review_count'] ?? 0);
                $fill = max(0, min(100, $avg/5*100));
              ?>
              <div class="rating-badge">
                <span class="position-relative" style="line-height:1;">
                  <span style="color:#cbd5f5;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                  <span style="color:#fb923c;position:absolute;left:0;top:0;width:<?= $fill ?>%;overflow:hidden;white-space:nowrap;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                </span>
                <?php if ($count > 0): ?>
                  <span class="fw-semibold"><?= number_format($avg,1) ?></span>
                  <span class="count"><?= $count ?> đánh giá</span>
                <?php else: ?>
                  <span class="count">Mới</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="d-flex gap-2 mt-auto">
              <a class="btn btn-outline-primary btn-sm flex-fill" href="?r=product&id=<?= e($p['id']) ?>">Xem chi tiết</a>
              <form method="post" action="?r=cart/add" class="m-0">
                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                <input type="hidden" name="qty" value="1">
                <button class="btn btn-primary btn-sm">🛒 Thêm</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12"><div class="alert alert-info">Chưa có món nào phù hợp bộ lọc.</div></div>
    <?php endif; ?>
  </div>
</section>

<section class="info-section">
  <h2 class="fw-bold mb-3">Tại sao chọn FoodShop?</h2>
  <div class="info-grid">
    <div class="info-item">
      <h3>Giao nhanh</h3>
      <p>Đặt món chỉ trong vài bước, hệ thống gợi ý nhà hàng gần nhất để giao nóng hổi như các app lớn.</p>
    </div>
    <div class="info-item">
      <h3>Ưu đãi mỗi ngày</h3>
      <p>Voucher độc quyền, flash sale giờ vàng và combo đoàn viên lấy cảm hứng từ ShopeeFood.</p>
    </div>
    <div class="info-item">
      <h3>Thanh toán linh hoạt</h3>
      <p>Hỗ trợ tiền mặt, ví MoMo, VNPay, ZaloPay và tích điểm FoodShop để đổi quà.</p>
    </div>
  </div>
</section>

<section class="faq-section">
  <h2 class="fw-bold mb-3">Những câu hỏi thường gặp</h2>
  <div class="faq-item">
    <h3>FoodShop hoạt động thế nào?</h3>
    <p>Bạn chọn món – FoodShop kết nối tới nhà hàng và tài xế, cập nhật trạng thái theo thời gian thực.</p>
  </div>
  <div class="faq-item">
    <h3>Tôi có thể theo dõi ưu đãi ở đâu?</h3>
    <p>Truy cập mục Ưu đãi hoặc bật thông báo email để không bỏ lỡ khuyến mãi mới nhất.</p>
  </div>
</section>

<script>
  document.querySelectorAll('[data-countdown]').forEach(function(el){
    function update(){
      var end = new Date(el.dataset.countdown.replace(' ', 'T'));
      var now = new Date();
      var diff = end - now;
      if(diff <= 0){ el.textContent = 'Đã kết thúc'; return; }
      var h = Math.floor(diff/1000/60/60);
      var m = Math.floor(diff/1000/60)%60;
      var s = Math.floor(diff/1000)%60;
      el.textContent = 'Còn ' + h.toString().padStart(2,'0') + ':' + m.toString().padStart(2,'0') + ':' + s.toString().padStart(2,'0');
    }
    update();
    setInterval(update,1000);
  });
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>
