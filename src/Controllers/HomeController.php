<?php
require_once __DIR__.'/../reviews.php';

function home_index(){
  $pdo = db();
  reviews_init();

  $cats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll() ?: [];
  $selectedCategory = (int)($_GET['category'] ?? 0);
  $search = trim($_GET['q'] ?? '');

  $promos = $pdo->query('SELECT * FROM promotions WHERE active=1 ORDER BY id DESC LIMIT 9')->fetchAll() ?: [];

  $stats = [
    'restaurants' => (int)$pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn(),
    'products'    => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'orders'      => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='paid'")->fetchColumn(),
  ];

  $collectionImages = [
    'Đồ uống'   => 'https://images.unsplash.com/photo-1527169402691-feff5539e52c?auto=format&fit=crop&w=800&q=80',
    'Cơm'       => 'https://images.unsplash.com/photo-1604908177522-4023ac76fb34?auto=format&fit=crop&w=800&q=80',
    'Bún/Phở'   => 'https://images.unsplash.com/photo-1504753793650-d4a2b783c15e?auto=format&fit=crop&w=800&q=80',
    'Ăn vặt'    => 'https://images.unsplash.com/photo-1525755662778-989d0524087e?auto=format&fit=crop&w=800&q=80',
    'Pizza'     => 'https://images.unsplash.com/photo-1548365328-9f54763c1a97?auto=format&fit=crop&w=800&q=80',
    'Mì'        => 'https://images.unsplash.com/photo-1521302080334-4abe04c74d83?auto=format&fit=crop&w=800&q=80',
    'Đồ ngọt'   => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80',
  ];

  $collections = [];
  foreach ($cats as $cat) {
    if (count($collections) >= 6) break;
    $collections[] = [
      'id' => $cat['id'],
      'name' => $cat['name'],
      'image' => $collectionImages[$cat['name']] ?? 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=800&q=80',
    ];
  }

  $nearbyRestaurants = $pdo->query("SELECT r.id, r.name, r.address, r.phone,
      COALESCE(SUM(oi.qty),0) AS items,
      COALESCE(AVG(pr.rating),0) AS rating
    FROM restaurants r
    LEFT JOIN products p ON p.restaurant_id = r.id
    LEFT JOIN order_items oi ON oi.product_id = p.id
    LEFT JOIN orders o ON o.id = oi.order_id AND o.status='paid'
    LEFT JOIN product_reviews pr ON pr.product_id = p.id
    GROUP BY r.id, r.name, r.address, r.phone
    ORDER BY items DESC, rating DESC
    LIMIT 6")->fetchAll(PDO::FETCH_ASSOC) ?: [];

  $flashSales = [];
  try {
    $flashSalesStmt = $pdo->prepare("SELECT fs.*, p.name, p.image_url, p.price, r.name AS restaurant
       FROM flash_sales fs
       JOIN products p ON p.id = fs.product_id
       LEFT JOIN restaurants r ON r.id = p.restaurant_id
       WHERE fs.start_at <= NOW() AND fs.end_at >= NOW()
       ORDER BY fs.start_at ASC
       LIMIT 6");
    $flashSalesStmt->execute();
    $flashSales = $flashSalesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  } catch (Throwable $e) {
    $flashSales = [];
  }

  $topSelling = $pdo->query("SELECT p.*, r.name AS restaurant, SUM(oi.qty) AS sold
      FROM products p
      LEFT JOIN order_items oi ON oi.product_id = p.id
      LEFT JOIN orders o ON o.id = oi.order_id AND o.status='paid'
      LEFT JOIN restaurants r ON r.id = p.restaurant_id
      GROUP BY p.id, r.name
      ORDER BY sold DESC
      LIMIT 6")->fetchAll(PDO::FETCH_ASSOC) ?: [];

  $sql = 'SELECT p.*, r.name AS restaurant
          FROM products p LEFT JOIN restaurants r ON r.id = p.restaurant_id';
  $wheres = [];
  $params = [];
  if ($selectedCategory > 0) {
    $wheres[] = 'p.category_id = ?';
    $params[] = $selectedCategory;
  }
  if ($search !== '') {
    $wheres[] = '(p.name LIKE ? OR r.name LIKE ?)';
    $params[] = '%'.$search.'%';
    $params[] = '%'.$search.'%';
  }
  if ($wheres) {
    $sql .= ' WHERE ' . implode(' AND ', $wheres);
  }
  $sql .= ' ORDER BY p.id DESC LIMIT 12';

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

  foreach ($products as &$product) {
    $reviewStats = review_product_stats((int)($product['id'] ?? 0));
    $product['avg_rating'] = $reviewStats['average'];
    $product['review_count'] = $reviewStats['total'];
  }
  unset($product);

  include __DIR__.'/../Views/home/index.php';
}
