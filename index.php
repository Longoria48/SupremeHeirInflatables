<?php
/* CREATION LOG: 11/19/2025
    Copied from the orderv2 example
    Changed rout names and links for rentals, cart, and contact
CREATION LOG: 11/19/2025 */

require_once __DIR__ . '/src/init.php';

// Get page from URL:  index.php?page=home
$page = $_GET['page'] ?? 'home';

// Map page keys → actual PHP files
$routes = [
    'home'       => BASE_PATH . '/src/home.php',
    'rentals'      => BASE_PATH . '/src/rentals.php',
    'cart'      => BASE_PATH . '/src/cart.php',
    'contact'     => BASE_PATH . '/src/contact.php',
    // Added log in and admin to index. May change later since these are meant to be private pages.
    'login'     => BASE_PATH . '/src/login.php',
    'admin'     => BASE_PATH . '/src/admin.php',
    'logout'     => BASE_PATH . '/src/logout.php'
];

// If route exists, load it. Otherwise show 404.
if (array_key_exists($page, $routes)) {
    require_once $routes[$page];
} else {
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
}