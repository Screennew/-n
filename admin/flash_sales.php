<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
if(!is_admin()) { flash('error','Chỉ admin'); redirect('auth/login'); }
$pdo = db();
$products = $pdo->query('SELECT id,name,price FROM products ORDER BY name')->fetchAll();
$formErrors = [];
$formData = ['product_id'=>'','sale_price'=>'','start_at'=>'','end_at'=>'','note'=>''];
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){
        $id = (int)$_POST['delete'];
        $pdo->prepare('DELETE FROM flash_sales WHERE id=?')->execute([$id]);
        flash('ok','Đã xóa flash sale #'.$id);
        header('Location: flash_sales.php');
        exit;
    }
    $formData['product_id'] = (int)($_POST['product_id'] ?? 0);
    $formData['sale_price'] = trim($_POST['sale_price'] ?? '');
    $formData['start_at'] = trim($_POST['start_at'] ?? '');
    $formData['end_at'] = trim($_POST['end_at'] ?? '');
    $formData['note'] = trim($_POST['note'] ?? '');
    if($formData['product_id']<=0) $formErrors[]='Vui lòng chọn món.';
    if(($formData['sale_price']==='') || !is_numeric($formData['sale_price'])) $formErrors[]='Giá flash sale không hợp lệ.';
    if($formData['start_at']==='' || $formData['end_at']==='') $formErrors[]='Vui lòng chọn thời gian.';
    if(!$formErrors){
        $pdo->prepare('INSERT INTO flash_sales(product_id,sale_price,start_at,end_at,note) VALUES(?,?,?,?,?)')
            ->execute([$formData['product_id'],$formData['sale_price'],$formData['start_at'],$formData['end_at'],$formData['note'] ?: null]);
        flash('ok','Đã tạo flash sale mới');
        header('Location: flash_sales.php');
        exit;
    }
}
$rows = $pdo->query('SELECT fs.*, p.name, p.price FROM flash_sales fs JOIN products p ON p.id=fs.product_id ORDER BY fs.start_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Admin - Flash sale</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f8fafc;padding:2.5rem 1rem;color:#0f172a;font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;}
    .admin-shell{max-width:1100px;margin:0 auto;}
    .glass{background:#fff;border-radius:22px;border:1px solid rgba(148,163,184,0.25);box-shadow:0 26px 60px -45px rgba(15,23,42,0.5);padding:1.8rem;margin-bottom:2rem;}
    .table thead{background:linear-gradient(120deg,rgba(59,130,246,0.12),rgba(37,99,235,0.12));text-transform:uppercase;font-size:.7rem;letter-spacing:.08em;}
  </style>
</head>
<body>
<div class="admin-shell">
  <a class="btn btn-outline-secondary mb-3" href="index.php">← Về Dashboard</a>
  <h2 class="fw-bold mb-3">Quản lý Flash Sale</h2>
  <?php if($msg=flash('ok')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
  <?php if($msg=flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
  <?php if($formErrors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($formErrors as $err) echo '<li>'.e($err).'</li>'; ?></ul></div>
  <?php endif; ?>
  <div class="glass">
    <h4 class="mb-3">Tạo flash sale mới</h4>
    <form method="post" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Món ăn</label>
        <select class="form-select" name="product_id" required>
          <option value="">-- Chọn món --</option>
          <?php foreach($products as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= $formData['product_id']==$p['id']?'selected':'' ?>><?= e($p['name']).' ('.money($p['price']).')' ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Giá flash sale</label>
        <input type="number" step="0.01" min="0" class="form-control" name="sale_price" value="<?= e($formData['sale_price']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Bắt đầu</label>
        <input type="datetime-local" class="form-control" name="start_at" value="<?= e($formData['start_at']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Kết thúc</label>
        <input type="datetime-local" class="form-control" name="end_at" value="<?= e($formData['end_at']) ?>" required>
      </div>
      <div class="col-md-9">
        <label class="form-label">Ghi chú (tuỳ chọn)</label>
        <input class="form-control" name="note" value="<?= e($formData['note']) ?>">
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Tạo flash sale</button>
      </div>
    </form>
  </div>

  <div class="glass">
    <h4 class="mb-3">Danh sách flash sale</h4>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>ID</th><th>Món</th><th>Giá sale</th><th>Giá gốc</th><th>Thời gian</th><th>Ghi chú</th><th></th></tr></thead>
        <tbody>
          <?php if($rows): ?>
            <?php foreach($rows as $row): ?>
              <tr>
                <td><?= e($row['id']) ?></td>
                <td><?= e($row['name']) ?></td>
                <td class="text-danger fw-semibold"><?= money($row['sale_price']) ?></td>
                <td><span class="text-decoration-line-through text-secondary"><?= money($row['price']) ?></span></td>
                <td>
                  <div class="small">Bắt đầu: <?= e($row['start_at']) ?></div>
                  <div class="small">Kết thúc: <?= e($row['end_at']) ?></div>
                </td>
                <td><?= e($row['note']) ?></td>
                <td>
                  <form method="post" onsubmit="return confirm('Xoá flash sale này?');">
                    <button class="btn btn-sm btn-outline-danger" name="delete" value="<?= e($row['id']) ?>">Xoá</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center text-secondary">Chưa có flash sale nào.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
