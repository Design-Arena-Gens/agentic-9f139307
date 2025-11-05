<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_ADMIN) {
    header("Location: ../login.php");
    exit();
}

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$where_clause = "1=1";
if($status_filter != 'all') {
    $where_clause .= " AND o.order_status = '$status_filter'";
}

$sql = "SELECT o.*, u.name as customer_name, r.name as restaurant_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN restaurants r ON o.restaurant_id = r.id
        WHERE $where_clause
        ORDER BY o.created_at DESC";
$orders = mysqli_query($conn, $sql);

$page_title = 'Manage Orders';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Manage Orders</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 py-6">
    <!-- Filters -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <a href="orders.php?status=all" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $status_filter == 'all' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            All
        </a>
        <a href="orders.php?status=pending" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $status_filter == 'pending' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Pending
        </a>
        <a href="orders.php?status=confirmed" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $status_filter == 'confirmed' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Confirmed
        </a>
        <a href="orders.php?status=preparing" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $status_filter == 'preparing' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Preparing
        </a>
        <a href="orders.php?status=out_for_delivery" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $status_filter == 'out_for_delivery' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Out for Delivery
        </a>
        <a href="orders.php?status=delivered" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $status_filter == 'delivered' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Delivered
        </a>
    </div>

    <!-- Orders Table -->
    <div class="card p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Order ID</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Restaurant</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Payment</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders)): ?>
                        <tr class="border-b border-slate-700 hover:bg-slate-700/50">
                            <td class="px-4 py-3 font-semibold"><?php echo $order['order_number']; ?></td>
                            <td class="px-4 py-3"><?php echo $order['customer_name']; ?></td>
                            <td class="px-4 py-3"><?php echo $order['restaurant_name']; ?></td>
                            <td class="px-4 py-3 font-semibold text-purple-400">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded <?php echo $order['payment_status'] == 'paid' ? 'bg-green-900/50 text-green-400' : 'bg-yellow-900/50 text-yellow-400'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded bg-slate-700 capitalize">
                                    <?php echo str_replace('_', ' ', $order['order_status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
