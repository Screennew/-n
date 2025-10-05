<?php include __DIR__.'/../partials/header.php'; ?>
<h3 class="mb-3">🧾 Quản lý đơn hàng</h3>

<div class="card shadow-sm">
  <div class="card-body table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>ID</th><th>Khách</th><th>Tổng</th><th>TT</th><th>Ngày</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach($rows as $o): ?>
          <tr>
            <td>#<?= $o['id'] ?></td>
            <td><?= e($o['email']) ?><div class="small text-secondary"><?= e($o['address']) ?></div></td>
            <td class="fw-semibold"><?= money($o['total']) ?></td>
            <td>
              <?php
                $badge = ['pending'=>'warning','paid'=>'success','cancelled'=>'danger'][$o['status']] ?? 'secondary';
              ?>
              <span class="badge bg-<?= $badge ?>"><?= $o['status'] ?></span>
            </td>
            <td><?= e($o['created_at']) ?></td>
            <td class="d-flex gap-2">
              <a class="btn btn-sm btn-success"  href="?r=admin/order/update&id=<?= $o['id'] ?>&s=paid">Xác nhận</a>
              <a class="btn btn-sm btn-warning"  href="?r=admin/order/update&id=<?= $o['id'] ?>&s=pending">Chờ</a>
              <a class="btn btn-sm btn-danger"   href="?r=admin/order/update&id=<?= $o['id'] ?>&s=cancelled">Hủy</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
