<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../src/helpers.php';
require_once __DIR__.'/../src/auth.php';
require_once __DIR__.'/../src/cart.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$route = $_GET['r'] ?? 'home';
$routes = [
  'admin'              => ['src/Controllers/AdminController.php','admin_dashboard'],
  'admin/products'     => ['src/Controllers/AdminController.php','admin_products'],
  'admin/product/save' => ['src/Controllers/AdminController.php','admin_product_save'],
  'admin/orders'       => ['src/Controllers/AdminController.php','admin_orders'],
  'admin/order/update' => ['src/Controllers/AdminController.php','admin_order_update'],

  'home'            => ['src/Controllers/HomeController.php','home_index'],
  'product'         => ['src/Controllers/ProductController.php','product_show'],
  'product/review'   => ['src/Controllers/ProductController.php','product_review_submit'],
  'cart'            => ['src/Controllers/CartController.php','cart_index'],
  'cart/add'        => ['src/Controllers/CartController.php','cart_add_action'],
  'cart/update'     => ['src/Controllers/CartController.php','cart_update_action'],
  'promotions/list'  => ['src/Controllers/PromotionController.php','promotion_index'],
  'checkout'        => ['src/Controllers/CheckoutController.php','checkout_index'],
  'checkout/place'  => ['src/Controllers/CheckoutController.php','checkout_place'],
  'auth/login'      => ['src/Controllers/AuthController.php','auth_login'],
  'auth/register'   => ['src/Controllers/AuthController.php','auth_register'],
  'auth/logout'     => ['src/Controllers/AuthController.php','auth_logout'],
  'profile'         => ['src/Controllers/ProfileController.php','profile_index'],
  'page/about'     => ['src/Controllers/PageController.php','page_about'],
  'page/careers'   => ['src/Controllers/PageController.php','page_careers'],
  'page/terms'     => ['src/Controllers/PageController.php','page_terms'],
  'page/privacy'   => ['src/Controllers/PageController.php','page_privacy'],
  'page/press'     => ['src/Controllers/PageController.php','page_press'],
  'page/shipping'  => ['src/Controllers/PageController.php','page_shipping'],
];

if (isset($routes[$route])) {
  require_once __DIR__.'/../'.$routes[$route][0];
  $fn = $routes[$route][1]; $fn();
} else {
  http_response_code(404); echo '<h1>404 Not Found</h1>';
}
