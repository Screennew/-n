
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
if(!is_admin()) { flash('error','Chỉ admin'); redirect('auth/login'); }
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){ $pdo->prepare('DELETE FROM coupons WHERE id=?')->execute([(int)$_POST['delete']]); }
    else {
        // naive insert for demo; adjust columns per table
    }
}
$rows=$pdo->query('SELECT * FROM coupons ORDER BY id DESC')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin - Mã giảm giá</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="p-3">
<a href="index.php" class="btn btn-link">← Dashboard</a>
<h3>Mã giảm giá</h3>
<div class="table-responsive"><table class="table"><thead><tr>
  <?php foreach(array_keys($rows[0]??[]) as $k) echo "<th>".e($k)."</th>"; ?>
  <th></th></tr></thead><tbody>
  <?php foreach($rows as $r): ?>
    <tr>
    <?php foreach($r as $v): ?><td><?= e($v) ?></td><?php endforeach; ?>
    <td><form method="post" onsubmit="return confirm('Xóa?')">
        <button class="btn btn-sm btn-danger" name="delete" value="<?= e($r['id']) ?>">Xóa</button></form></td>
    </tr>
  <?php endforeach; ?>
</tbody></table></div>
</body></html>
