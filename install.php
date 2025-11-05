<?php
// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'foodie_jabalpur');

// Connect without database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

// Select database
mysqli_select_db($conn, DB_NAME);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    role VARCHAR(20) DEFAULT 'user',
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);

// Create restaurants table
$sql = "CREATE TABLE IF NOT EXISTS restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    phone VARCHAR(15) NOT NULL,
    image VARCHAR(255),
    rating DECIMAL(2,1) DEFAULT 0.0,
    delivery_time VARCHAR(50),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
mysqli_query($conn, $sql);

// Create menu_items table
$sql = "CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    image VARCHAR(255),
    is_veg BOOLEAN DEFAULT 1,
    is_available BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
)";
mysqli_query($conn, $sql);

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    delivery_boy_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    delivery_phone VARCHAR(15) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(20) DEFAULT 'pending',
    order_status VARCHAR(20) DEFAULT 'pending',
    razorpay_order_id VARCHAR(100),
    razorpay_payment_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_boy_id) REFERENCES users(id) ON DELETE SET NULL
)";
mysqli_query($conn, $sql);

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
)";
mysqli_query($conn, $sql);

// Create cart table
$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
)";
mysqli_query($conn, $sql);

// Create admin table
$sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);

// Create payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    razorpay_payment_id VARCHAR(100),
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";
mysqli_query($conn, $sql);

// Insert default admin
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO admin (username, password, email) VALUES ('admin', '$admin_password', 'admin@foodiejabalpur.com')";
mysqli_query($conn, $sql);

// Insert sample user
$user_password = password_hash('user123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (name, email, phone, password, address, role) VALUES
('Test User', 'user@test.com', '9999999999', '$user_password', 'Jabalpur, MP', 'user')";
mysqli_query($conn, $sql);

// Insert sample restaurant owner
$restaurant_password = password_hash('restaurant123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (name, email, phone, password, role) VALUES
('Restaurant Owner', 'restaurant@test.com', '8888888888', '$restaurant_password', 'restaurant')";
mysqli_query($conn, $sql);

// Insert sample delivery boy
$delivery_password = password_hash('delivery123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (name, email, phone, password, role) VALUES
('Delivery Boy', 'delivery@test.com', '7777777777', '$delivery_password', 'delivery')";
mysqli_query($conn, $sql);

// Insert sample restaurant
$sql = "INSERT INTO restaurants (user_id, name, description, address, phone, rating, delivery_time) VALUES
(2, 'Tasty Treats', 'Best food in town', 'Napier Town, Jabalpur', '8888888888', 4.5, '30-40 mins')";
mysqli_query($conn, $sql);

// Insert sample menu items
$sql = "INSERT INTO menu_items (restaurant_id, name, description, price, category, is_veg) VALUES
(1, 'Paneer Tikka', 'Spicy grilled paneer', 180.00, 'Starters', 1),
(1, 'Chicken Biryani', 'Aromatic rice with chicken', 250.00, 'Main Course', 0),
(1, 'Dal Tadka', 'Yellow lentils with spices', 120.00, 'Main Course', 1),
(1, 'Gulab Jamun', 'Sweet dessert', 60.00, 'Desserts', 1)";
mysqli_query($conn, $sql);

mysqli_close($conn);

// Redirect to login page
header("Location: login.php?installed=1");
exit();
?>
