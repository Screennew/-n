</main>

<footer class="bg-dark text-light mt-auto py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="fs-5 fw-bold">🍜 <?= e(APP_NAME) ?></div>
        <div>Đặt đồ ăn nhanh – ngon – tiện lợi</div>
        <div class="small text-secondary">© <?= date('Y') ?> FoodShop. All rights reserved.</div>
        <div class="mt-3">
          <div class="small"><span class="fw-semibold">Hotline:</span> <a class="link-light" href="tel:19001234">1900 1234</a></div>
          <div class="small"><span class="fw-semibold">Email:</span> <a class="link-light" href="mailto:support@foodshop.local">support@foodshop.local</a></div>
          <div class="small"><span class="fw-semibold">Trụ sở:</span> 123 Láng Hạ, Đống Đa, Hà Nội</div>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="fw-semibold mb-2">Về chúng tôi</div>
        <div class="small"><a class="link-light" href="?r=page/about">Giới thiệu</a></div>
        <div class="small"><a class="link-light" href="?r=page/careers">Tuyển dụng</a></div>
        <div class="small"><a class="link-light" href="?r=page/press">Bộ báo chí</a></div>
      </div>
      <div class="col-6 col-md-3">
        <div class="fw-semibold mb-2">Hỗ trợ</div>
        <div class="small"><a class="link-light" href="?r=page/terms">Điều khoản sử dụng</a></div>
        <div class="small"><a class="link-light" href="?r=page/privacy">Chính sách bảo mật</a></div>
        <div class="small"><a class="link-light" href="?r=page/shipping">Chính sách giao hàng</a></div>
      </div>
      <div class="col-md-3">
        <div class="fw-semibold mb-2">Kết nối</div>
        <div class="d-flex gap-3 fs-4">
          <a class="link-light" href="https://facebook.com" target="_blank" rel="noopener">🐦</a>
          <a class="link-light" href="https://www.instagram.com" target="_blank" rel="noopener">🌼</a>
          <a class="link-light" href="https://www.tiktok.com" target="_blank" rel="noopener">🎬</a>
        </div>
        <div class="small text-secondary mt-3">Tải ứng dụng FoodShop để nhận thêm ưu đãi.</div>
      </div>
    </div>
  </div>
</footer>

<?php if (!empty($showPromoModal ?? false)): ?>
<!-- Popup ƯU ĐÃI: chỉ bật ở trang chủ, cho phép ẩn 2 giờ -->
<div class="modal fade" id="promoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">🎉 Ưu đãi đặc biệt</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Nhập mã <b><?= e(WELCOME_COUPON_CODE) ?></b> để giảm 10% cho đơn đầu tiên.</p>
        <p>Freeship nội thành cho đơn từ 99k – Giảm 30% khung 11:00–14:00.</p>
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" id="dontShowPromo">
          <label class="form-check-label" for="dontShowPromo">Không hiện lại trong 2 giờ</label>
        </div>
      </div>
      <div class="modal-footer">
        <a class="btn btn-outline-secondary" href="#promotions">Xem ưu đãi</a>
        <button class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($showPromoModal ?? false)): ?>
<script>
(function(){
  const KEY='promoModalHiddenUntil';
  try {
    const until = Number(localStorage.getItem(KEY) || 0);
    const now   = Date.now();
    if (!until || now > until) {
      const modalEl = document.getElementById('promoModal');
      if(!modalEl) return;
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
      modalEl.addEventListener('hidden.bs.modal', function(){
        if (document.getElementById('dontShowPromo').checked) {
          localStorage.setItem(KEY, String(Date.now() + 2*60*60*1000)); // 2 giờ
        }
      });
    }
  } catch(e) {}
})();
</script>
<?php endif; ?>
</body>
</html>
