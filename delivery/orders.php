<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_DELIVERY) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Status Update
if(isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);

    $sql = "UPDATE orders SET order_status = '$new_status' WHERE id = $order_id AND delivery_boy_id = $user_id";
    mysqli_query($conn, $sql);
    header("Location: orders.php?updated=1");
    exit();
}

// Get delivery orders
$sql = "SELECT o.*, r.name as restaurant_name, r.address as restaurant_address, r.phone as restaurant_phone
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.delivery_boy_id = $user_id AND o.order_status IN ('out_for_delivery', 'delivered')
        ORDER BY o.created_at DESC";
$orders = mysqli_query($conn, $sql);

$page_title = 'My Deliveries';
require_once 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">My Deliveries</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <?php if(isset($_GET['accepted'])): ?>
        <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-4">
            Order accepted successfully!
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['updated'])): ?>
        <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-4">
            Order status updated successfully!
        </div>
    <?php endif; ?>

    <?php if(mysqli_num_rows($orders) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($orders)): ?>
            <div class="card p-4 mb-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-xs text-slate-400 mb-1"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
                        <p class="font-semibold text-lg">Order #<?php echo $order['order_number']; ?></p>
                        <p class="text-sm text-slate-400"><?php echo $order['restaurant_name']; ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-purple-400">₹<?php echo number_format($order['total_amount'], 2); ?></p>
                        <p class="text-xs text-<?php echo $order['order_status'] == 'delivered' ? 'green' : 'orange'; ?>-400">
                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                        </p>
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

                <!-- Action Button -->
                <?php if($order['order_status'] == 'out_for_delivery'): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <input type="hidden" name="new_status" value="delivered">
                        <button type="submit" name="update_status" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold">
                            <i class="fas fa-check-circle"></i> Mark as Delivered
                        </button>
                    </form>
                <?php else: ?>
                    <div class="bg-green-900/20 border border-green-700 text-green-400 px-4 py-3 rounded-lg text-center">
                        <i class="fas fa-check-circle"></i> Delivered
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card p-8 text-center">
            <i class="fas fa-motorcycle text-6xl text-slate-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No deliveries yet</h3>
            <p class="text-slate-400 mb-6">Accept orders to start delivering</p>
            <a href="available.php" class="btn-primary text-white px-6 py-3 rounded-lg inline-block">
                <i class="fas fa-box"></i> View Available Orders
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../common/bottom.php'; ?>
