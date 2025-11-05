<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_ADMIN) {
    header("Location: ../login.php");
    exit();
}

// Get payment statistics
$sql = "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_amount FROM orders";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $sql));

$sql = "SELECT COUNT(*) as paid_orders, SUM(total_amount) as paid_amount FROM orders WHERE payment_status = 'paid'";
$paid_stats = mysqli_fetch_assoc(mysqli_query($conn, $sql));

$sql = "SELECT COUNT(*) as pending_payments, SUM(total_amount) as pending_amount FROM orders WHERE payment_status = 'pending'";
$pending_stats = mysqli_fetch_assoc(mysqli_query($conn, $sql));

// Get recent payments
$sql = "SELECT o.*, u.name as customer_name, r.name as restaurant_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN restaurants r ON o.restaurant_id = r.id
        ORDER BY o.created_at DESC
        LIMIT 50";
$payments = mysqli_query($conn, $sql);

$page_title = 'Payments';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Payments</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 py-6">
    <!-- Payment Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card p-6">
            <p class="text-slate-400 mb-2">Total Revenue</p>
            <p class="text-3xl font-bold text-purple-400">₹<?php echo number_format($stats['total_amount'], 2); ?></p>
            <p class="text-sm text-slate-500 mt-1"><?php echo $stats['total_orders']; ?> orders</p>
        </div>
        <div class="card p-6">
            <p class="text-slate-400 mb-2">Paid Orders</p>
            <p class="text-3xl font-bold text-green-400">₹<?php echo number_format($paid_stats['paid_amount'], 2); ?></p>
            <p class="text-sm text-slate-500 mt-1"><?php echo $paid_stats['paid_orders']; ?> orders</p>
        </div>
        <div class="card p-6">
            <p class="text-slate-400 mb-2">Pending Payments</p>
            <p class="text-3xl font-bold text-yellow-400">₹<?php echo number_format($pending_stats['pending_amount'], 2); ?></p>
            <p class="text-sm text-slate-500 mt-1"><?php echo $pending_stats['pending_payments']; ?> orders</p>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card p-6">
        <h3 class="text-xl font-semibold mb-4">Recent Payments</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Order ID</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Restaurant</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Payment Method</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($payment = mysqli_fetch_assoc($payments)): ?>
                        <tr class="border-b border-slate-700 hover:bg-slate-700/50">
                            <td class="px-4 py-3 font-semibold"><?php echo $payment['order_number']; ?></td>
                            <td class="px-4 py-3"><?php echo $payment['customer_name']; ?></td>
                            <td class="px-4 py-3"><?php echo $payment['restaurant_name']; ?></td>
                            <td class="px-4 py-3 font-semibold text-purple-400">₹<?php echo number_format($payment['total_amount'], 2); ?></td>
                            <td class="px-4 py-3 capitalize"><?php echo $payment['payment_method']; ?></td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded <?php echo $payment['payment_status'] == 'paid' ? 'bg-green-900/50 text-green-400' : 'bg-yellow-900/50 text-yellow-400'; ?>">
                                    <?php echo ucfirst($payment['payment_status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400"><?php echo date('d M Y, h:i A', strtotime($payment['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
