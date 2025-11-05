<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_ADMIN) {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$sql = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
$total_users = mysqli_fetch_assoc(mysqli_query($conn, $sql))['total_users'];

$sql = "SELECT COUNT(*) as total_restaurants FROM restaurants WHERE status = 'active'";
$total_restaurants = mysqli_fetch_assoc(mysqli_query($conn, $sql))['total_restaurants'];

$sql = "SELECT COUNT(*) as total_orders FROM orders";
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, $sql))['total_orders'];

$sql = "SELECT COUNT(*) as pending_orders FROM orders WHERE order_status = 'pending'";
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, $sql))['pending_orders'];

$sql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE payment_status = 'paid'";
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, $sql))['total_revenue'] ?? 0;

$sql = "SELECT COUNT(*) as delivery_boys FROM users WHERE role = 'delivery'";
$delivery_boys = mysqli_fetch_assoc(mysqli_query($conn, $sql))['delivery_boys'];

// Get recent orders
$sql = "SELECT o.*, u.name as customer_name, r.name as restaurant_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN restaurants r ON o.restaurant_id = r.id
        ORDER BY o.created_at DESC
        LIMIT 10";
$recent_orders = mysqli_query($conn, $sql);

$page_title = 'Admin Dashboard';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-shield-alt text-purple-400"></i> Admin Panel
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
<div class="max-w-6xl mx-auto px-4 py-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-2xl text-blue-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?php echo $total_users; ?></p>
                    <p class="text-xs text-slate-400">Users</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-store text-2xl text-purple-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?php echo $total_restaurants; ?></p>
                    <p class="text-xs text-slate-400">Restaurants</p>
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
                    <p class="text-xs text-slate-400">Orders</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-2xl text-yellow-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?php echo $pending_orders; ?></p>
                    <p class="text-xs text-slate-400">Pending</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-motorcycle text-2xl text-orange-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?php echo $delivery_boys; ?></p>
                    <p class="text-xs text-slate-400">Delivery</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-pink-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-2xl text-pink-400"></i>
                </div>
                <div>
                    <p class="text-xl font-bold">₹<?php echo number_format($total_revenue/1000, 0); ?>K</p>
                    <p class="text-xs text-slate-400">Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <a href="users.php" class="card p-4 hover:bg-slate-700 text-center">
            <i class="fas fa-users text-3xl text-blue-400 mb-2"></i>
            <p class="font-semibold">Manage Users</p>
        </a>
        <a href="restaurants.php" class="card p-4 hover:bg-slate-700 text-center">
            <i class="fas fa-store text-3xl text-purple-400 mb-2"></i>
            <p class="font-semibold">Restaurants</p>
        </a>
        <a href="orders.php" class="card p-4 hover:bg-slate-700 text-center">
            <i class="fas fa-receipt text-3xl text-green-400 mb-2"></i>
            <p class="font-semibold">All Orders</p>
        </a>
        <a href="payments.php" class="card p-4 hover:bg-slate-700 text-center">
            <i class="fas fa-credit-card text-3xl text-pink-400 mb-2"></i>
            <p class="font-semibold">Payments</p>
        </a>
    </div>

    <!-- Recent Orders -->
    <div class="card p-6">
        <h3 class="text-xl font-semibold mb-4">Recent Orders</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Order ID</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Restaurant</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <tr class="border-b border-slate-700 hover:bg-slate-700/50">
                            <td class="px-4 py-3"><?php echo $order['order_number']; ?></td>
                            <td class="px-4 py-3"><?php echo $order['customer_name']; ?></td>
                            <td class="px-4 py-3"><?php echo $order['restaurant_name']; ?></td>
                            <td class="px-4 py-3 font-semibold">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded bg-slate-700">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3"><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
