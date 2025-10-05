<?php
require_once __DIR__.'/../auth.php';
require_once __DIR__.'/../../config/db.php';
require_once __DIR__.'/../helpers.php';

function _admin_require(){
  require_login();
  $u = current_user();
  if (($u['role'] ?? '') !== 'admin') {
    flash('error','Bạn không có quyền truy cập');
    redirect('home');
  }
}

function admin_dashboard(){
  _admin_require();
  $db = db();

  $summary = $db->query("SELECT
      COUNT(*) AS total_orders,
      SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) AS paid_orders,
      SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending_orders,
      SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled_orders,
      COALESCE(SUM(CASE WHEN status='paid' THEN total ELSE 0 END),0) AS revenue
    FROM orders")->fetch();

  $totalCustomers = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
  $totalRestaurants = (int)$db->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
  $totalProducts  = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();

  $chartRows = $db->query("SELECT DATE_FORMAT(created_at,'%Y-%m') ym, SUM(total) revenue, COUNT(*) orders
                         FROM orders
                         WHERE status='paid' AND created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 5 MONTH)
                         GROUP BY ym
                         ORDER BY ym")->fetchAll(PDO::FETCH_ASSOC);
  $chartMap = [];
  foreach ($chartRows as $row) {
    $chartMap[$row['ym']] = $row;
  }
  $chartLabels = []; $chartRevenue = []; $chartOrders = [];
  for ($i = 5; $i >= 0; $i--) {
    $monthKey = date('Y-m', strtotime("-$i months"));
    $chartLabels[] = date('m/Y', strtotime("-$i months"));
    $chartRevenue[] = (float)($chartMap[$monthKey]['revenue'] ?? 0);
    $chartOrders[] = (int)($chartMap[$monthKey]['orders'] ?? 0);
  }

  $topProducts = $db->query("SELECT p.id, p.name, SUM(oi.qty) AS qty, SUM(oi.qty*oi.price) AS revenue
                             FROM orders o
                             JOIN order_items oi ON oi.order_id = o.id
                             JOIN products p ON p.id = oi.product_id
                             WHERE o.status='paid'
                             GROUP BY p.id, p.name
                             ORDER BY qty DESC
                             LIMIT 5")->fetchAll();

  $topCustomers = $db->query("SELECT COALESCE(u.name, u.email) AS name, u.email,
                                     COUNT(o.id) AS orders, SUM(o.total) AS revenue
                              FROM orders o
                              JOIN users u ON u.id=o.user_id
                              WHERE o.status='paid'
                              GROUP BY u.id, u.name, u.email
                              ORDER BY revenue DESC
                              LIMIT 5")->fetchAll();

  $chartData = [
    'labels' => $chartLabels,
    'revenue' => $chartRevenue,
    'orders' => $chartOrders,
  ];

  include __DIR__.'/../Views/admin/dashboard.php';
}

function admin_products(){
  _admin_require();
  $rows = db()->query("SELECT id,name,price,image_url FROM products ORDER BY id DESC")->fetchAll();
  include __DIR__.'/../Views/admin/products.php';
}

function admin_product_save(){
  _admin_require();
  $id    = (int)($_POST['id'] ?? 0);
  $name  = trim($_POST['name'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $img   = trim($_POST['image_url'] ?? '');   // fallback URL
  $desc  = trim($_POST['description'] ?? '');

  if(!$name || $price<=0){ flash('error','Tên/giá không hợp lệ'); redirect('admin/products'); }

  // ===== UPLOAD ẢNH (nếu chọn file) =====
  if(!empty($_FILES['image_file']['name']) && is_uploaded_file($_FILES['image_file']['tmp_name'])){
    $tmp  = $_FILES['image_file']['tmp_name'];
    $info = @getimagesize($tmp);
    if($info !== false && $_FILES['image_file']['size'] <= 3*1024*1024){ // <= 3MB
      $ext = image_type_to_extension($info[2], true); // .jpg/.png/.webp
      $ext = strtolower($ext);
      if(in_array($ext, ['.jpg','.jpeg','.png','.webp'])){
        $dir = __DIR__ . '/../../public/uploads';
        if(!is_dir($dir)) @mkdir($dir,0777,true);
        $nameOnDisk = uniqid('img_').$ext;
        $dest = $dir.'/'.$nameOnDisk;
        if(@move_uploaded_file($tmp, $dest)){
          // đường dẫn public
          $img = rtrim(BASE_URL,'/').'/uploads/'.$nameOnDisk;
        }
      }
    }
  }
  // ======================================

  $db = db();
  if($id>0){
    $st=$db->prepare("UPDATE products SET name=?, price=?, image_url=?, description=? WHERE id=?");
    $st->execute([$name,$price,$img,$desc,$id]);
    flash('ok','Đã cập nhật món #'.$id);
  }else{
    $st=$db->prepare("INSERT INTO products(name,price,image_url,description,created_at) VALUES (?,?,?,?,NOW())");
    $st->execute([$name,$price,$img,$desc]);
    flash('ok','Đã thêm món mới');
  }
  redirect('admin/products');
}

function admin_orders(){
  _admin_require();
  $rows = db()->query("SELECT o.*, u.email
                       FROM orders o JOIN users u ON u.id=o.user_id
                       ORDER BY o.id DESC LIMIT 100")->fetchAll();
  include __DIR__.'/../Views/admin/orders.php';
}

function admin_order_update(){
  _admin_require();
  $id=(int)($_GET['id']??0);
  $s = $_GET['s'] ?? '';
  if($id>0 && in_array($s,['pending','paid','cancelled'],true)){
    $st=db()->prepare("UPDATE orders SET status=? WHERE id=?");
    $st->execute([$s,$id]);
    flash('ok',"Đổi trạng thái đơn #$id: $s");
  }
  redirect('admin/orders');
}
