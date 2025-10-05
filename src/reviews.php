<?php
require_once __DIR__ . '/../config/db.php';

function reviews_init(): void
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uniq_product_user (product_id, user_id),
    INDEX idx_product (product_id),
    INDEX idx_user (user_id),
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

    db()->exec($sql);
    $initialized = true;
}

function review_product_stats(int $productId): array
{
    reviews_init();

    $stmt = db()->prepare('SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average FROM product_reviews WHERE product_id = ?');
    $stmt->execute([$productId]);
    $stats = $stmt->fetch();

    return [
        'total' => (int)($stats['total'] ?? 0),
        'average' => (float)($stats['average'] ?? 0),
    ];
}

function review_product_list(int $productId, int $limit = 20): array
{
    reviews_init();

    $stmt = db()->prepare('SELECT pr.*, u.name AS user_name, u.email AS user_email
        FROM product_reviews pr JOIN users u ON u.id = pr.user_id
        WHERE pr.product_id = ?
        ORDER BY pr.updated_at DESC
        LIMIT ?');
    $stmt->bindValue(1, $productId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function review_find(int $productId, int $userId): ?array
{
    reviews_init();

    $stmt = db()->prepare('SELECT * FROM product_reviews WHERE product_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$productId, $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function review_save(int $productId, int $userId, int $rating, string $comment): void
{
    reviews_init();

    $rating = max(1, min(5, $rating));
    $comment = trim($comment);
    $now = date('Y-m-d H:i:s');

    $stmt = db()->prepare('INSERT INTO product_reviews(product_id, user_id, rating, comment, created_at, updated_at)
        VALUES(?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = VALUES(updated_at)');
    $stmt->execute([$productId, $userId, $rating, $comment !== '' ? $comment : null, $now, $now]);
}

function review_delete(int $productId, int $userId): void
{
    reviews_init();

    $stmt = db()->prepare('DELETE FROM product_reviews WHERE product_id = ? AND user_id = ?');
    $stmt->execute([$productId, $userId]);
}

function review_restaurant_stats(array $productIds): array
{
    if (!$productIds) {
        return ['total' => 0, 'average' => 0.0];
    }

    reviews_init();

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = db()->prepare("SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average FROM product_reviews WHERE product_id IN ($placeholders)");
    $stmt->execute(array_map('intval', $productIds));
    $stats = $stmt->fetch();
    return [
        'total' => (int)($stats['total'] ?? 0),
        'average' => (float)($stats['average'] ?? 0),
    ];
}
