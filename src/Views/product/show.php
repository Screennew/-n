<?php include __DIR__.'/../partials/header.php'; ?>
<?php
$avgDisplay = number_format($avgRating, 1);
$fillPercent = max(0, min(100, ($avgRating / 5) * 100));
?>
<style>
  .rating-display { position: relative; display: inline-block; font-size: 1.1rem; line-height: 1; }
  .rating-display .stars { color: #d1d5db; }
  .rating-display .stars.fill { color: #f97316; position: absolute; top: 0; left: 0; overflow: hidden; white-space: nowrap; }
  .review-card { border: 1px solid rgba(148,163,184,.25); border-radius: 14px; padding: 1rem; background: #fff; box-shadow: 0 15px 35px -32px rgba(15,23,42,.6); }
  .rating-input { position: relative; display: flex; gap: .35rem; flex-direction: row-reverse; justify-content: flex-end; }
  .rating-input input { display: none; }
  .rating-input label { cursor: pointer; font-size: 1.6rem; color: #cbd5f5; transition: color .15s ease; }
  .rating-input input:checked ~ label, .rating-input label:hover, .rating-input label:hover ~ label { color: #f97316; }
  .review-list { display: grid; gap: 1rem; }
  .review-meta { font-size: .85rem; color: #64748b; }
  .review-empty { border: 1px dashed rgba(148,163,184,.6); border-radius: 14px; padding: 1.5rem; text-align: center; color: #94a3b8; background: rgba(255,255,255,.6); }
</style>
<div class="row g-4">
  <div class="col-md-6">
    <img class="img-fluid rounded shadow-sm" src="<?= e($p['image_url']) ?>" onerror="this.src='https://picsum.photos/seed/p<?= e($p['id']) ?>/800/600';">
  </div>
  <div class="col-md-6">
    <h3 class="fw-bold"><?= e($p['name']) ?></h3>
    <div class="d-flex align-items-center gap-2 mb-2">
      <div class="rating-display">
        <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="stars fill" style="width: <?= $fillPercent ?>%;">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
      </div>
      <span class="fw-semibold"><?= $avgDisplay ?></span>
      <span class="text-secondary small"><?= $reviewCount ?> đánh giá</span>
    </div>
    <div class="text-secondary mb-2"><?= e($p['restaurant']) ?> &bull; <?= e($p['category']) ?></div>
    <div class="fs-4 fw-bold mb-3 text-primary"><?= money($p['price']) ?></div>
    <form method="post" action="?r=cart/add" class="mb-3">
      <input type="hidden" name="id" value="<?= e($p['id']) ?>">
      <div class="input-group mb-3" style="max-width:220px">
        <span class="input-group-text">SL</span>
        <input class="form-control" type="number" name="qty" min="1" value="1">
      </div>
      <button class="btn btn-primary">&#128722; Thêm vào giỏ</button>
    </form>
    <div class="small text-secondary"><?= nl2br(e($p['description'])) ?></div>
  </div>
</div>

<hr class="my-5">
<div class="row g-4">
  <div class="col-lg-6">
    <h5 id="reviews" class="fw-semibold mb-3">Đánh giá món</h5>
    <?php if ($reviewCount > 0): ?>
      <div class="review-list">
        <?php foreach ($reviews as $review): ?>
          <div class="review-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <div class="fw-semibold"><?= e($review['user_name'] ?: $review['user_email']) ?></div>
                <div class="review-meta"><?= date('d/m/Y H:i', strtotime($review['updated_at'])) ?></div>
              </div>
              <div class="rating-display">
                <?php $percent = max(0, min(100, ($review['rating'] / 5) * 100)); ?>
                <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <div class="stars fill" style="width: <?= $percent ?>%;">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
              </div>
            </div>
            <?php if (!empty($review['comment'])): ?>
              <div><?= nl2br(e($review['comment'])) ?></div>
            <?php else: ?>
              <div class="text-muted fst-italic">Khách hàng không để lại nhận xét.</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="review-empty">Chưa có đánh giá nào cho món này. Hãy là người đầu tiên chia sẻ cảm nhận!</div>
    <?php endif; ?>
  </div>
  <div class="col-lg-6">
    <h5 class="fw-semibold mb-3">Viết đánh giá của bạn</h5>
    <?php if (current_user()): ?>
      <form method="post" action="?r=product/review" class="review-card">
        <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
        <div class="mb-3">
          <label class="form-label">Chọn số sao</label>
          <div class="rating-input">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" name="rating" id="rate<?= $i ?>" value="<?= $i ?>" <?= ($userReview['rating'] ?? 0) == $i ? 'checked' : '' ?>>
              <label for="rate<?= $i ?>">&#9733;</label>
            <?php endfor; ?>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Cảm nhận (tuỳ chọn)</label>
          <textarea class="form-control" name="comment" rows="4" placeholder="Món có ngon không, giao hàng nhanh chứ?"><?= e($userReview['comment'] ?? '') ?></textarea>
        </div>
        <button class="btn btn-primary w-100">Gửi đánh giá</button>
        <?php if ($userReview): ?>
          <div class="form-text mt-2">Bạn có thể gửi lại để cập nhật đánh giá đã có.</div>
        <?php endif; ?>
      </form>
    <?php else: ?>
      <div class="review-card">
        <p class="mb-1"><strong>Đăng nhập để đánh giá</strong></p>
        <p class="mb-3 text-secondary">Chia sẻ cảm nhận của bạn sau khi thưởng thức món ăn.</p>
        <a class="btn btn-outline-primary" href="?r=auth/login">Đăng nhập</a>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
