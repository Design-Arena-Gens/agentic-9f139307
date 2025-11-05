<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_DELIVERY) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Accept Order
if(isset($_POST['accept_order'])) {
    $order_id = (int)$_POST['order_id'];

    // Check if order is still available
    $sql = "SELECT * FROM orders WHERE id = $order_id AND delivery_boy_id IS NULL AND order_status = 'out_for_delivery'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0) {
        // Assign delivery boy
        $sql = "UPDATE orders SET delivery_boy_id = $user_id WHERE id = $order_id";
        mysqli_query($conn, $sql);
        header("Location: orders.php?accepted=1");
        exit();
    } else {
        $error = 'This order is no longer available';
    }
}

// Get available orders
$sql = "SELECT o.*, r.name as restaurant_name, r.address as restaurant_address, r.phone as restaurant_phone
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.delivery_boy_id IS NULL AND o.order_status = 'out_for_delivery'
        ORDER BY o.created_at ASC";
$available_orders = mysqli_query($conn, $sql);

$page_title = 'Available Orders';
require_once 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Available Orders</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <?php if(isset($error)): ?>
        <div class="bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if(mysqli_num_rows($available_orders) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($available_orders)): ?>
            <div class="card p-4 mb-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-xs text-slate-400 mb-1"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
                        <p class="font-semibold text-lg">Order #<?php echo $order['order_number']; ?></p>
                        <p class="text-sm text-slate-400"><?php echo $order['restaurant_name']; ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-purple-400">₹<?php echo number_format($order['total_amount'], 2); ?></p>
                        <p class="text-xs text-green-400">Earn: ₹<?php echo number_format($order['total_amount'] * 0.1, 2); ?></p>
                    </div>
                </div>

                <!-- Pickup Location -->
                <div class="bg-slate-700 p-3 rounded-lg mb-3">
                    <p class="text-xs text-purple-400 mb-1"><i class="fas fa-store"></i> PICKUP FROM</p>
                    <p class="text-sm font-semibold mb-1"><?php echo $order['restaurant_name']; ?></p>
                    <p class="text-sm text-slate-400 mb-1">
                        <i class="fas fa-map-marker-alt"></i> <?php echo $order['restaurant_address']; ?>
                    </p>
                    <p class="text-sm text-slate-400">
                        <i class="fas fa-phone"></i> <?php echo $order['restaurant_phone']; ?>
                    </p>
                </div>

                <!-- Delivery Location -->
                <div class="bg-slate-700 p-3 rounded-lg mb-3">
                    <p class="text-xs text-orange-400 mb-1"><i class="fas fa-home"></i> DELIVER TO</p>
                    <p class="text-sm text-slate-400 mb-1">
                        <i class="fas fa-map-marker-alt"></i> <?php echo $order['delivery_address']; ?>
                    </p>
                    <p class="text-sm text-slate-400">
                        <i class="fas fa-phone"></i> <?php echo $order['delivery_phone']; ?>
                    </p>
                </div>

                <!-- Accept Button -->
                <form method="POST" action="">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" name="accept_order" class="w-full btn-primary text-white py-3 rounded-lg font-semibold">
                        <i class="fas fa-check-circle"></i> Accept Order
                    </button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card p-8 text-center">
            <i class="fas fa-box text-6xl text-slate-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No orders available</h3>
            <p class="text-slate-400">Check back later for new delivery orders</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../common/bottom.php'; ?>
