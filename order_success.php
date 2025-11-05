<?php
require_once 'common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_USER) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$user_id = $_SESSION['user_id'];

// Get order details
$sql = "SELECT o.*, r.name as restaurant_name
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.id = $order_id AND o.user_id = $user_id";
$order_result = mysqli_query($conn, $sql);

if(mysqli_num_rows($order_result) == 0) {
    header("Location: orders.php");
    exit();
}

$order = mysqli_fetch_assoc($order_result);

$page_title = 'Order Success';
include 'common/header.php';
?>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-4xl text-white"></i>
        </div>
        <h2 class="text-2xl font-bold mb-2">Order Placed Successfully!</h2>
        <p class="text-slate-400">Your order has been confirmed</p>
    </div>

    <!-- Order Details -->
    <div class="card p-6 mb-4">
        <div class="text-center mb-6">
            <p class="text-sm text-slate-400 mb-2">Order Number</p>
            <p class="text-2xl font-bold text-purple-400"><?php echo $order['order_number']; ?></p>
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-400">Restaurant</span>
                <span class="font-semibold"><?php echo $order['restaurant_name']; ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Total Amount</span>
                <span class="font-semibold text-purple-400">₹<?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Payment Method</span>
                <span class="font-semibold"><?php echo ucfirst($order['payment_method']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Status</span>
                <span class="font-semibold text-yellow-400">
                    <i class="fas fa-clock"></i> <?php echo ucfirst($order['order_status']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="space-y-3">
        <a href="orders.php" class="block btn-primary text-white text-center py-4 rounded-lg font-semibold">
            <i class="fas fa-receipt"></i> View My Orders
        </a>
        <a href="index.php" class="block bg-slate-700 text-white text-center py-4 rounded-lg font-semibold hover:bg-slate-600">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
