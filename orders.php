<?php
require_once 'common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_USER) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user orders
$sql = "SELECT o.*, r.name as restaurant_name
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.user_id = $user_id
        ORDER BY o.created_at DESC";
$orders = mysqli_query($conn, $sql);

$page_title = 'My Orders';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <h2 class="text-xl font-bold text-white">My Orders</h2>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <?php if(mysqli_num_rows($orders) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($orders)):
            $status_colors = [
                'pending' => 'text-yellow-400',
                'confirmed' => 'text-blue-400',
                'preparing' => 'text-purple-400',
                'out_for_delivery' => 'text-orange-400',
                'delivered' => 'text-green-400',
                'cancelled' => 'text-red-400'
            ];
            $status_color = isset($status_colors[$order['order_status']]) ? $status_colors[$order['order_status']] : 'text-slate-400';
        ?>
            <div class="card p-4 mb-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-xs text-slate-400 mb-1"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
                        <p class="font-semibold text-lg"><?php echo $order['restaurant_name']; ?></p>
                        <p class="text-sm text-slate-400">Order #<?php echo $order['order_number']; ?></p>
                    </div>
                    <span class="<?php echo $status_color; ?> font-semibold text-sm">
                        <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                    </span>
                </div>

                <hr class="border-slate-700 my-3">

                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-slate-400">Total Amount</p>
                        <p class="text-xl font-bold text-purple-400">₹<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-primary px-4 py-2 rounded-lg text-sm">
                        View Details <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card p-8 text-center">
            <i class="fas fa-receipt text-6xl text-slate-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No orders yet</h3>
            <p class="text-slate-400 mb-6">Start ordering delicious food now!</p>
            <a href="index.php" class="btn-primary text-white px-6 py-3 rounded-lg inline-block">
                <i class="fas fa-utensils"></i> Browse Restaurants
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
