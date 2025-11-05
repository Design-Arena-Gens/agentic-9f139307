<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_DELIVERY) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get delivery history
$sql = "SELECT o.*, r.name as restaurant_name
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.delivery_boy_id = $user_id AND o.order_status = 'delivered'
        ORDER BY o.created_at DESC";
$history = mysqli_query($conn, $sql);

$page_title = 'Delivery History';
require_once 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">History</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <?php if(mysqli_num_rows($history) > 0): ?>
        <?php
        $total_earnings = 0;
        while($order = mysqli_fetch_assoc($history)):
            $earning = $order['total_amount'] * 0.1;
            $total_earnings += $earning;
        ?>
            <div class="card p-4 mb-4">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-xs text-slate-400 mb-1"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
                        <p class="font-semibold"><?php echo $order['restaurant_name']; ?></p>
                        <p class="text-sm text-slate-400">Order #<?php echo $order['order_number']; ?></p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="bg-green-900/50 border border-green-700 text-green-400 px-2 py-1 rounded text-xs">
                                <i class="fas fa-check-circle"></i> Delivered
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-400">Order: ₹<?php echo number_format($order['total_amount'], 2); ?></p>
                        <p class="text-xl font-bold text-green-400">+₹<?php echo number_format($earning, 2); ?></p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- Total Earnings -->
        <div class="card p-6 text-center">
            <p class="text-sm text-slate-400 mb-2">Total Earnings</p>
            <p class="text-4xl font-bold text-purple-400">₹<?php echo number_format($total_earnings, 2); ?></p>
        </div>
    <?php else: ?>
        <div class="card p-8 text-center">
            <i class="fas fa-history text-6xl text-slate-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No history yet</h3>
            <p class="text-slate-400">Your completed deliveries will appear here</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../common/bottom.php'; ?>
