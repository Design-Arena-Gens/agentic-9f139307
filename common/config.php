<?php
session_start();

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'foodie_jabalpur');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Base URL
define('BASE_URL', '/');
define('SITE_NAME', 'Foodie Jabalpur');

// User role constants
define('ROLE_USER', 'user');
define('ROLE_RESTAURANT', 'restaurant');
define('ROLE_DELIVERY', 'delivery');
define('ROLE_ADMIN', 'admin');
?>
