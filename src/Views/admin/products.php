<?php include __DIR__.'/../partials/header.php'; ?>
<h3 class="mb-3">🍽️ Quản lý món ăn</h3>

<style>
  .thumb { width: 64px; height: 48px; object-fit: cover; border-radius: .5rem; }
</style>

<div class="row">
  <!-- FORM -->
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-header fw-semibold">Thêm / Sửa món</div>
      <div class="card-body">
        <form method="post" action="?r=admin/product/save" enctype="multipart/form-data">
          <input type="hidden" name="id" id="p-id">

          <div class="mb-2">
            <label class="form-label">Tên món</label>
            <input class="form-control" name="name" id="p-name" required>
          </div>

          <div class="mb-2">
            <label class="form-label">Giá</label>
            <input class="form-control" name="price" id="p-price" type="number" min="0" required>
          </div>

          <div class="mb-2">
            <label class="form-label">Ảnh (tải lên)</label>
            <input class="form-control" type="file" name="image_file" id="p-file" accept="image/*">
            <div class="form-text">Hỗ trợ JPG/PNG/WebP, tối đa ~3MB.</div>
          </div>

          <div class="mb-2">
            <label class="form-label">Hoặc ảnh (URL)</label>
            <input class="form-control" name="image_url" id="p-image" placeholder="https://...">
          </div>

          <div class="mb-2">
            <label class="form-label">Mô tả</label>
            <textarea class="form-control" name="description" id="p-desc"></textarea>
          </div>

          <div class="mb-3">
            <img id="p-preview" src="" alt="" style="max-width:100%;height:auto;display:none" class="rounded border">
          </div>

          <button class="btn btn-success">Lưu</button>
        </form>
      </div>
    </div>
  </div>

  <!-- LIST -->
  <div class="col-md-7">
    <div class="card shadow-sm">
      <div class="card-header fw-semibold">Danh sách món</div>
      <div class="card-body table-responsive">
        <table class="table align-middle">
          <thead>
            <tr><th>ID</th><th>Món</th><th>Giá</th><th>Ảnh</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= e($r['name']) ?></td>
              <td><?= money($r['price']) ?></td>
              <td>
                <img src="<?= e($r['image_url']) ?>" class="thumb"
                     onerror="this.src='https://picsum.photos/seed/p<?= $r['id'] ?>/128/96';">
              </td>
              <td>
                <button class="btn btn-sm btn-primary"
                        onclick="fillEdit(<?= $r['id'] ?>,'<?= e($r['name']) ?>',<?= $r['price'] ?>,'<?= e($r['image_url']) ?>')">Sửa</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
const fileInput = document.getElementById('p-file');
const urlInput  = document.getElementById('p-image');
const prev      = document.getElementById('p-preview');

fileInput.addEventListener('change', () => {
  if (fileInput.files && fileInput.files[0]) {
    const url = URL.createObjectURL(fileInput.files[0]);
    prev.src = url; prev.style.display='block';
  }
});
urlInput.addEventListener('input', () => {
  const v = urlInput.value.trim();
  if (v){ prev.src=v; prev.style.display='block'; } else { prev.style.display='none'; }
});

function fillEdit(id,name,price,img){
  document.getElementById('p-id').value=id;
  document.getElementById('p-name').value=name;
  document.getElementById('p-price').value=price;
  document.getElementById('p-image').value=img||'';
  document.getElementById('p-file').value='';
  document.getElementById('p-desc').value='';
  if(img){ prev.src=img; prev.style.display='block'; } else { prev.style.display='none'; }
  window.scrollTo({top:0,behavior:'smooth'});
}
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>
