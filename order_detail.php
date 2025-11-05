<?php
require_once 'common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_USER) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get order details
$sql = "SELECT o.*, r.name as restaurant_name, r.address as restaurant_address, r.phone as restaurant_phone
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.id = $order_id AND o.user_id = $user_id";
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

// Get delivery boy info if assigned
$delivery_boy = null;
if($order['delivery_boy_id']) {
    $sql = "SELECT * FROM users WHERE id = {$order['delivery_boy_id']}";
    $delivery_result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($delivery_result) > 0) {
        $delivery_boy = mysqli_fetch_assoc($delivery_result);
    }
}

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
    <!-- Order Status -->
    <div class="card p-6 mb-4 text-center">
        <p class="text-sm text-slate-400 mb-2">Order #<?php echo $order['order_number']; ?></p>
        <h3 class="text-2xl font-bold mb-4 text-purple-400">
            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
        </h3>
        <p class="text-sm text-slate-400"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
    </div>

    <!-- Restaurant Info -->
    <div class="card p-4 mb-4">
        <h3 class="font-semibold mb-3 flex items-center gap-2">
            <i class="fas fa-store text-purple-400"></i>
            Restaurant Details
        </h3>
        <p class="font-semibold mb-1"><?php echo $order['restaurant_name']; ?></p>
        <p class="text-sm text-slate-400 mb-1">
            <i class="fas fa-map-marker-alt"></i> <?php echo $order['restaurant_address']; ?>
        </p>
        <p class="text-sm text-slate-400">
            <i class="fas fa-phone"></i> <?php echo $order['restaurant_phone']; ?>
        </p>
    </div>

    <!-- Delivery Boy Info -->
    <?php if($delivery_boy): ?>
        <div class="card p-4 mb-4">
            <h3 class="font-semibold mb-3 flex items-center gap-2">
                <i class="fas fa-motorcycle text-purple-400"></i>
                Delivery Partner
            </h3>
            <p class="font-semibold mb-1"><?php echo $delivery_boy['name']; ?></p>
            <p class="text-sm text-slate-400">
                <i class="fas fa-phone"></i> <?php echo $delivery_boy['phone']; ?>
            </p>
        </div>
    <?php endif; ?>

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

    <!-- Delivery Address -->
    <div class="card p-4 mb-4">
        <h3 class="font-semibold mb-3 flex items-center gap-2">
            <i class="fas fa-map-marker-alt text-purple-400"></i>
            Delivery Address
        </h3>
        <p class="text-slate-300 mb-2"><?php echo $order['delivery_address']; ?></p>
        <p class="text-sm text-slate-400">
            <i class="fas fa-phone"></i> <?php echo $order['delivery_phone']; ?>
        </p>
    </div>

    <!-- Payment Details -->
    <div class="card p-4 mb-6">
        <h3 class="font-semibold mb-3 flex items-center gap-2">
            <i class="fas fa-credit-card text-purple-400"></i>
            Payment Details
        </h3>
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

<?php include 'common/bottom.php'; ?>
