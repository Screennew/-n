<?php
require_once __DIR__.'/../reviews.php';

function product_show(){
  $id = (int)($_GET['id'] ?? 0);
  $st = db()->prepare('SELECT p.*, r.name AS restaurant, c.name AS category
    FROM products p LEFT JOIN restaurants r ON r.id = p.restaurant_id
    LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
  $st->execute([$id]);
  $p = $st->fetch();

  if (!$p) {
    http_response_code(404);
    echo '<h1>Không tìm thấy món</h1>';
    return;
  }

  $reviewStats = review_product_stats($id);
  $reviews = review_product_list($id, 20);
  $avgRating = $reviewStats['average'];
  $reviewCount = $reviewStats['total'];

  $currentUser = current_user();
  $userReview = null;
  if (is_array($currentUser) && isset($currentUser['id'])) {
    $userReview = review_find($id, (int)$currentUser['id']);
  }

  include __DIR__.'/../Views/product/show.php';
}

function product_review_submit(){
  require_login();

  $productId = (int)($_POST['product_id'] ?? 0);
  $rating = (int)($_POST['rating'] ?? 0);
  $comment = trim($_POST['comment'] ?? '');

  if ($productId <= 0) {
    flash('error', 'Món ăn không hợp lệ.');
    redirect('home');
  }

  if ($rating < 1 || $rating > 5) {
    flash('error', 'Vui lòng chọn mức đánh giá từ 1-5 sao.');
    redirect('product&id=' . $productId);
  }

  $st = db()->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
  $st->execute([$productId]);
  if (!$st->fetch()) {
    flash('error', 'Món ăn không tồn tại.');
    redirect('home');
  }

  $user = current_user();
  $userId = (int)($user['id'] ?? 0);

  if ($userId <= 0) {
    flash('error', 'Phiên làm việc hết hạn, vui lòng đăng nhập lại.');
    redirect('auth/login');
  }

  review_save($productId, $userId, $rating, $comment);
  flash('ok', 'Cảm ơn bạn đã đánh giá món ăn.');

  header('Location: index.php?r=product&id=' . $productId . '#reviews');
  exit;
}
