<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_RESTAURANT) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get restaurant details
$sql = "SELECT * FROM restaurants WHERE user_id = $user_id";
$restaurant_result = mysqli_query($conn, $sql);

if(mysqli_num_rows($restaurant_result) == 0) {
    // Restaurant not created yet
    $restaurant = null;
} else {
    $restaurant = mysqli_fetch_assoc($restaurant_result);
    $restaurant_id = $restaurant['id'];

    // Get order statistics
    $sql = "SELECT COUNT(*) as total_orders FROM orders WHERE restaurant_id = $restaurant_id";
    $total_orders = mysqli_fetch_assoc(mysqli_query($conn, $sql))['total_orders'];

    $sql = "SELECT COUNT(*) as pending_orders FROM orders WHERE restaurant_id = $restaurant_id AND order_status = 'pending'";
    $pending_orders = mysqli_fetch_assoc(mysqli_query($conn, $sql))['pending_orders'];

    $sql = "SELECT COUNT(*) as active_orders FROM orders WHERE restaurant_id = $restaurant_id AND order_status IN ('confirmed', 'preparing', 'out_for_delivery')";
    $active_orders = mysqli_fetch_assoc(mysqli_query($conn, $sql))['active_orders'];

    $sql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE restaurant_id = $restaurant_id AND payment_status = 'paid'";
    $total_revenue = mysqli_fetch_assoc(mysqli_query($conn, $sql))['total_revenue'] ?? 0;

    // Get recent orders
    $sql = "SELECT o.*, u.name as customer_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.restaurant_id = $restaurant_id
            ORDER BY o.created_at DESC
            LIMIT 5";
    $recent_orders = mysqli_query($conn, $sql);
}

$page_title = 'Restaurant Dashboard';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-store text-purple-400"></i> Restaurant Panel
                </h2>
                <p class="text-sm text-slate-400">Welcome, <?php echo $_SESSION['user_name']; ?></p>
            </div>
            <a href="../logout.php" class="text-red-400 hover:text-red-300">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <?php if(!$restaurant): ?>
        <!-- Setup Restaurant -->
        <div class="card p-8 text-center">
            <i class="fas fa-store text-6xl text-purple-400 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Setup Your Restaurant</h3>
            <p class="text-slate-400 mb-6">Please create your restaurant profile to start receiving orders</p>
            <a href="setup.php" class="btn-primary text-white px-6 py-3 rounded-lg inline-block">
                <i class="fas fa-plus"></i> Create Restaurant
            </a>
        </div>
    <?php else: ?>
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-2xl text-yellow-400"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold"><?php echo $pending_orders; ?></p>
                        <p class="text-sm text-slate-400">Pending</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-fire text-2xl text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold"><?php echo $active_orders; ?></p>
                        <p class="text-sm text-slate-400">Active</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-receipt text-2xl text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold"><?php echo $total_orders; ?></p>
                        <p class="text-sm text-slate-400">Total Orders</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-rupee-sign text-2xl text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-xl font-bold">₹<?php echo number_format($total_revenue, 0); ?></p>
                        <p class="text-sm text-slate-400">Revenue</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card p-4 mb-6">
            <h3 class="font-semibold mb-3">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="orders.php" class="bg-slate-700 hover:bg-slate-600 p-4 rounded-lg text-center">
                    <i class="fas fa-clipboard-list text-2xl text-purple-400 mb-2"></i>
                    <p class="text-sm">View Orders</p>
                </a>
                <a href="menu.php" class="bg-slate-700 hover:bg-slate-600 p-4 rounded-lg text-center">
                    <i class="fas fa-utensils text-2xl text-orange-400 mb-2"></i>
                    <p class="text-sm">Manage Menu</p>
                </a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card p-4 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold">Recent Orders</h3>
                <a href="orders.php" class="text-purple-400 text-sm">View All <i class="fas fa-chevron-right"></i></a>
            </div>

            <?php if(mysqli_num_rows($recent_orders) > 0): ?>
                <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="block bg-slate-700 hover:bg-slate-600 p-3 rounded-lg mb-2 last:mb-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold"><?php echo $order['customer_name']; ?></p>
                                <p class="text-sm text-slate-400">Order #<?php echo $order['order_number']; ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-purple-400">₹<?php echo number_format($order['total_amount'], 2); ?></p>
                                <p class="text-xs text-slate-400"><?php echo ucfirst($order['order_status']); ?></p>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-slate-400 py-4">No orders yet</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../common/bottom.php'; ?>
