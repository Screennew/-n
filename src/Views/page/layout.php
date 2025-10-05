<?php include __DIR__.'/../partials/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-4 p-md-5">
        <h1 class="h3 fw-bold mb-2"><?= e($pageTitle) ?></h1>
        <?php if(!empty($pageLead)): ?>
          <p class="lead text-secondary mb-4"><?= nl2br(e($pageLead)) ?></p>
        <?php endif; ?>
        <?php foreach($sections as $section): ?>
          <div class="mb-4">
            <?php if(!empty($section['heading'])): ?>
              <h2 class="h5 fw-semibold mb-2"><?= e($section['heading']) ?></h2>
            <?php endif; ?>
            <div class="text-secondary">
              <?php if(!empty($section['body'])): ?>
                <?= $section['body'] ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
        <div class="alert alert-info mb-0">
          Cần hỗ trợ thêm? Liên hệ <a href="mailto:support@foodshop.local" class="alert-link">support@foodshop.local</a> hoặc hotline 1900 1234.
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
