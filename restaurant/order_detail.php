<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_RESTAURANT) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get restaurant details
$sql = "SELECT * FROM restaurants WHERE user_id = $user_id";
$restaurant_result = mysqli_query($conn, $sql);

if(mysqli_num_rows($restaurant_result) == 0) {
    header("Location: setup.php");
    exit();
}

$restaurant = mysqli_fetch_assoc($restaurant_result);
$restaurant_id = $restaurant['id'];

// Get order details
$sql = "SELECT o.*, u.name as customer_name, u.phone as customer_phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = $order_id AND o.restaurant_id = $restaurant_id";
$order_result = mysqli_query($conn, $sql);

if(mysqli_num_rows($order_result) == 0) {
    header("Location: orders.php");
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$sql = "SELECT oi.*, m.name, m.is_veg
        FROM order_items oi
        JOIN menu_items m ON oi.menu_item_id = m.id
        WHERE oi.order_id = $order_id";
$order_items = mysqli_query($conn, $sql);

$page_title = 'Order Details';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="orders.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Order Details</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <!-- Order Info -->
    <div class="card p-6 mb-4 text-center">
        <p class="text-sm text-slate-400 mb-2">Order #<?php echo $order['order_number']; ?></p>
        <h3 class="text-2xl font-bold mb-2 text-purple-400">
            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
        </h3>
        <p class="text-sm text-slate-400"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
    </div>

    <!-- Customer Info -->
    <div class="card p-4 mb-4">
        <h3 class="font-semibold mb-3 flex items-center gap-2">
            <i class="fas fa-user text-purple-400"></i>
            Customer Details
        </h3>
        <p class="font-semibold mb-2"><?php echo $order['customer_name']; ?></p>
        <p class="text-sm text-slate-400 mb-1">
            <i class="fas fa-phone"></i> <?php echo $order['customer_phone']; ?>
        </p>
        <p class="text-sm text-slate-400">
            <i class="fas fa-map-marker-alt"></i> <?php echo $order['delivery_address']; ?>
        </p>
    </div>

    <!-- Order Items -->
    <div class="card p-4 mb-4">
        <h3 class="font-semibold mb-3 flex items-center gap-2">
            <i class="fas fa-shopping-bag text-purple-400"></i>
            Order Items
        </h3>
        <?php while($item = mysqli_fetch_assoc($order_items)): ?>
            <div class="flex items-center gap-3 mb-3 last:mb-0">
                <?php if($item['is_veg']): ?>
                    <span class="w-4 h-4 border-2 border-green-500 flex items-center justify-center flex-shrink-0">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    </span>
                <?php else: ?>
                    <span class="w-4 h-4 border-2 border-red-500 flex items-center justify-center flex-shrink-0">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                    </span>
                <?php endif; ?>
                <div class="flex-1">
                    <p class="font-semibold"><?php echo $item['name']; ?></p>
                    <p class="text-sm text-slate-400">Qty: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></p>
                </div>
                <p class="font-semibold">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Payment Info -->
    <div class="card p-4 mb-6">
        <h3 class="font-semibold mb-3">Payment Details</h3>
        <div class="space-y-2 text-sm mb-3">
            <div class="flex justify-between">
                <span class="text-slate-400">Payment Method</span>
                <span class="font-semibold"><?php echo ucfirst($order['payment_method']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Payment Status</span>
                <span class="font-semibold text-<?php echo $order['payment_status'] == 'paid' ? 'green' : 'yellow'; ?>-400">
                    <?php echo ucfirst($order['payment_status']); ?>
                </span>
            </div>
        </div>
        <hr class="border-slate-700 my-3">
        <div class="flex justify-between text-lg font-bold">
            <span>Total Amount</span>
            <span class="text-purple-400">₹<?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
    </div>
</div>

<?php include '../common/bottom.php'; ?>
