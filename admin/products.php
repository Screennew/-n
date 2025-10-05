<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';

if (!is_admin()) {
    flash('error', 'Chỉ admin');
    redirect('auth/login');
}

$pdo = db();

// Rút gọn mô tả hiển thị ở bảng.
function short_text($text, $limit = 80)
{
    $text = (string) $text;

    if ($text === '') {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit, 'UTF-8')) . '...';
    }

    if (strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(substr($text, 0, $limit)) . '...';
}

$categoryRows = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$restaurantRows = $pdo->query('SELECT id, name FROM restaurants ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$categoryMap = [];
foreach ($categoryRows as $row) {
    $categoryMap[(int) $row['id']] = $row['name'];
}

$restaurantMap = [];
foreach ($restaurantRows as $row) {
    $restaurantMap[(int) $row['id']] = $row['name'];
}

$formData = [
    'name' => '',
    'price' => '',
    'category_id' => $categoryRows ? (int) $categoryRows[0]['id'] : '',
    'restaurant_id' => $restaurantRows ? (int) $restaurantRows[0]['id'] : '',
    'image_url' => '',
    'description' => '',
];

$errors = [];
$formDisabled = empty($categoryMap) || empty($restaurantMap);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $deleteId = (int) ($_POST['delete'] ?? 0);

        if ($deleteId > 0) {
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $stmt->execute([$deleteId]);

            if ($stmt->rowCount() > 0) {
                flash('ok', 'Đã xóa món #' . $deleteId);
            } else {
                flash('error', 'Không tìm thấy món cần xóa.');
            }
        } else {
            flash('error', 'ID món không hợp lệ.');
        }

        header('Location: products.php');
        exit;
    }

    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['price'] = trim($_POST['price'] ?? '');
    $formData['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $formData['restaurant_id'] = (int) ($_POST['restaurant_id'] ?? 0);
    $formData['image_url'] = trim($_POST['image_url'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');

    if ($formData['name'] === '') {
        $errors[] = 'Vui lòng nhập tên món.';
    }

    $priceNormalized = str_replace(',', '.', $formData['price']);
    $price = filter_var($priceNormalized, FILTER_VALIDATE_FLOAT);
    if ($price === false || $price < 0) {
        $errors[] = 'Giá phải là số không âm.';
    }

    if (!isset($categoryMap[$formData['category_id']])) {
        $errors[] = 'Danh mục không hợp lệ.';
    }

    if (!isset($restaurantMap[$formData['restaurant_id']])) {
        $errors[] = 'Nhà hàng không hợp lệ.';
    }

    if ($formData['image_url'] !== '' && !filter_var($formData['image_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Đường dẫn ảnh không hợp lệ.';
    }

    if (!$errors && !$formDisabled) {
        try {
            $stmt = $pdo->prepare('INSERT INTO products (name, price, category_id, restaurant_id, description, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([
                $formData['name'],
                $price,
                $formData['category_id'],
                $formData['restaurant_id'],
                $formData['description'],
                $formData['image_url'] ?: null,
            ]);

            flash('ok', 'Đã thêm món mới.');
            header('Location: products.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Không thể thêm món lúc này. Vui lòng thử lại sau.';
        }
    }
}

$productRows = $pdo->query('SELECT p.*, c.name AS c_name, r.name AS r_name FROM products p LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN restaurants r ON r.id = p.restaurant_id ORDER BY p.id DESC')->fetchAll(PDO::FETCH_ASSOC);
$productCount = count($productRows);
$categoryCount = count($categoryMap);
$restaurantCount = count($restaurantMap);
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quản trị - Sản phẩm</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --admin-primary: #ea580c;
      --admin-accent: #6366f1;
      --admin-border: rgba(148, 163, 184, 0.25);
    }
    * { box-sizing: border-box; }
    body {
      min-height: 100vh;
      margin: 0;
      padding: 2.5rem 1rem;
      background: radial-gradient(circle at top left, rgba(244, 114, 182, 0.15), transparent 55%), radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.18), transparent 45%), #f8fafc;
      color: #1f2937;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      align-items: flex-start;
      justify-content: center;
    }
    .admin-shell {
      width: 100%;
      max-width: 1180px;
    }
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: 1.75rem;
    }
    .page-header h1 {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: .35rem;
    }
    .page-header p {
      margin: 0;
      color: #64748b;
    }
    .stats {
      display: flex;
      gap: .75rem;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
    .stat-pill {
      padding: .75rem 1.2rem;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.75);
      border: 1px solid var(--admin-border);
      min-width: 150px;
      box-shadow: 0 18px 35px -28px rgba(30, 64, 175, 0.55);
    }
    .stat-pill span {
      display: block;
    }
    .stat-pill .label {
      text-transform: uppercase;
      font-size: .7rem;
      letter-spacing: .08em;
      color: #94a3b8;
      margin-bottom: .2rem;
    }
    .stat-pill .value {
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--admin-accent);
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.92);
      border-radius: 24px;
      padding: 2rem;
      margin-bottom: 2rem;
      border: 1px solid var(--admin-border);
      box-shadow: 0 28px 65px -40px rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(10px);
    }
    .glass-card h2 {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .form-grid {
      display: grid;
      gap: 1rem;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }
    .form-grid textarea {
      grid-column: 1 / -1;
      min-height: 120px;
    }
    .btn-primary {
      background: linear-gradient(135deg, var(--admin-primary), #f97316);
      border: none;
      box-shadow: 0 16px 45px -22px rgba(249, 115, 22, 0.75);
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #fb923c, var(--admin-primary));
    }
    .btn-outline-secondary {
      color: var(--admin-accent);
      border-color: var(--admin-border);
    }
    .btn-outline-secondary:hover {
      color: #fff;
      border-color: var(--admin-accent);
      background: var(--admin-accent);
    }
    .table-card {
      background: rgba(255, 255, 255, 0.96);
      border-radius: 24px;
      border: 1px solid var(--admin-border);
      box-shadow: 0 24px 55px -38px rgba(15, 23, 42, 0.5);
      overflow: hidden;
    }
    .table-card .table {
      margin: 0;
    }
    .table thead {
      background: linear-gradient(120deg, rgba(99, 102, 241, 0.12), rgba(236, 72, 153, 0.12));
    }
    .table thead th {
      border-bottom: none;
      text-transform: uppercase;
      letter-spacing: .08em;
      font-size: .7rem;
      color: #475569;
      padding-top: .85rem;
      padding-bottom: .85rem;
    }
    .table tbody tr {
      transition: transform .16s ease, box-shadow .16s ease;
    }
    .table tbody tr:hover {
      transform: translateY(-2px);
      box-shadow: inset 0 0 0 999px rgba(99, 102, 241, 0.04);
    }
    .price-pill {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: .35rem .75rem;
      border-radius: 999px;
      background: rgba(234, 88, 12, 0.12);
      color: var(--admin-primary);
      font-weight: 600;
      font-size: .9rem;
    }
    .empty-state {
      text-align: center;
      padding: 2.75rem 1.5rem;
      color: #94a3b8;
    }
    .alert {
      border-radius: 18px;
      border: none;
      box-shadow: 0 16px 45px -38px rgba(15, 23, 42, 0.35);
    }
    @media (max-width: 768px) {
      body {
        padding: 2rem 1rem;
      }
      .page-header {
        flex-direction: column;
        align-items: flex-start;
      }
      .stats {
        justify-content: flex-start;
      }
      .glass-card {
        padding: 1.5rem;
      }
      .table-responsive {
        border-radius: 0;
      }
    }
  </style>
</head>
<body>
  <div class="admin-shell">
    <div class="page-header">
      <div>
        <a class="btn btn-sm btn-outline-secondary mb-3" href="index.php">← Trở về Dashboard</a>
        <h1>Quản lý sản phẩm</h1>
        <p>Theo dõi danh sách món ăn và tạo mới thật nhanh chóng.</p>
      </div>
      <div class="stats">
        <div class="stat-pill">
          <span class="label">Món ăn</span>
          <span class="value"><?= number_format($productCount) ?></span>
        </div>
        <div class="stat-pill">
          <span class="label">Danh mục</span>
          <span class="value"><?= number_format($categoryCount) ?></span>
        </div>
        <div class="stat-pill">
          <span class="label">Nhà hàng</span>
          <span class="value"><?= number_format($restaurantCount) ?></span>
        </div>
      </div>
    </div>

    <?php if ($ok = flash('ok')): ?>
      <div class="alert alert-success"><?= e($ok) ?></div>
    <?php endif; ?>
    <?php if ($err = flash('error')): ?>
      <div class="alert alert-danger"><?= e($err) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="glass-card">
      <h2>Thêm món mới</h2>
      <?php if ($formDisabled): ?>
        <div class="alert alert-warning mb-4">
          Cần ít nhất một danh mục và một nhà hàng để tạo món ăn mới.
        </div>
      <?php endif; ?>
      <form method="post" class="form-grid">
        <div>
          <label class="form-label">Tên món</label>
          <input type="text" name="name" class="form-control" value="<?= e($formData['name']) ?>" <?= $formDisabled ? 'disabled' : '' ?> required>
        </div>
        <div>
          <label class="form-label">Giá</label>
          <input type="number" name="price" min="0" step="0.01" class="form-control" value="<?= e($formData['price']) ?>" <?= $formDisabled ? 'disabled' : '' ?> required>
        </div>
        <div>
          <label class="form-label">Danh mục</label>
          <select class="form-select" name="category_id" <?= $formDisabled ? 'disabled' : '' ?>>
            <?php foreach ($categoryRows as $category): ?>
              <option value="<?= (int) $category['id'] ?>" <?= (int) $formData['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                <?= e($category['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Nhà hàng</label>
          <select class="form-select" name="restaurant_id" <?= $formDisabled ? 'disabled' : '' ?>>
            <?php foreach ($restaurantRows as $restaurant): ?>
              <option value="<?= (int) $restaurant['id'] ?>" <?= (int) $formData['restaurant_id'] === (int) $restaurant['id'] ? 'selected' : '' ?>>
                <?= e($restaurant['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Ảnh sản phẩm (URL)</label>
          <input type="url" name="image_url" class="form-control" placeholder="https://..." value="<?= e($formData['image_url']) ?>" <?= $formDisabled ? 'disabled' : '' ?>>
        </div>
        <textarea name="description" class="form-control" placeholder="Mô tả ngắn gọn về món ăn" <?= $formDisabled ? 'disabled' : '' ?>><?= e($formData['description']) ?></textarea>
        <div>
          <button class="btn btn-primary px-4" <?= $formDisabled ? 'disabled' : '' ?>>Thêm món</button>
        </div>
      </form>
    </div>

    <div class="table-card">
      <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
          <thead>
            <tr>
              <th style="width: 70px;">ID</th>
              <th>Tên món</th>
              <th style="width: 160px;">Giá</th>
              <th style="width: 180px;">Danh mục</th>
              <th style="width: 180px;">Nhà hàng</th>
              <th style="width: 110px;" class="text-center">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($productRows): ?>
              <?php foreach ($productRows as $product): ?>
                <tr>
                  <td class="fw-semibold text-secondary">#<?= e($product['id']) ?></td>
                  <td>
                    <div class="fw-semibold"><?= e($product['name']) ?></div>
                    <?php if (!empty($product['description'])): ?>
                      <div class="text-muted small"><?= e(short_text($product['description'])) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="price-pill"><?= money($product['price']) ?></span></td>
                  <td><?= e($product['c_name'] ?? 'Không rõ') ?></td>
                  <td><?= e($product['r_name'] ?? 'Không rõ') ?></td>
                  <td class="text-center">
                    <form method="post" class="d-inline" onsubmit="return confirm('Xóa món này?');">
                      <button class="btn btn-sm btn-outline-danger" name="delete" value="<?= e($product['id']) ?>">Xóa</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6">
                  <div class="empty-state">
                    Hiện chưa có món ăn nào trong hệ thống. Hãy thêm món đầu tiên!
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
