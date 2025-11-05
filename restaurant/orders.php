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
    header("Location: setup.php");
    exit();
}

$restaurant = mysqli_fetch_assoc($restaurant_result);
$restaurant_id = $restaurant['id'];

// Handle Order Status Update
if(isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);

    $sql = "UPDATE orders SET order_status = '$new_status' WHERE id = $order_id AND restaurant_id = $restaurant_id";
    mysqli_query($conn, $sql);
    header("Location: orders.php?updated=1");
    exit();
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query based on filter
$where_clause = "o.restaurant_id = $restaurant_id";
if($filter != 'all') {
    $where_clause .= " AND o.order_status = '$filter'";
}

// Get orders
$sql = "SELECT o.*, u.name as customer_name, u.phone as customer_phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE $where_clause
        ORDER BY o.created_at DESC";
$orders = mysqli_query($conn, $sql);

$page_title = 'Orders';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Orders</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <?php if(isset($_GET['updated'])): ?>
        <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-4">
            Order status updated successfully!
        </div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <a href="orders.php?filter=all" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $filter == 'all' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            All
        </a>
        <a href="orders.php?filter=pending" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $filter == 'pending' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Pending
        </a>
        <a href="orders.php?filter=confirmed" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $filter == 'confirmed' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Confirmed
        </a>
        <a href="orders.php?filter=preparing" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $filter == 'preparing' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Preparing
        </a>
        <a href="orders.php?filter=delivered" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $filter == 'delivered' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Delivered
        </a>
    </div>

    <!-- Orders List -->
    <?php if(mysqli_num_rows($orders) > 0): ?>
        <?php while($order = mysqli_fetch_assoc($orders)): ?>
            <div class="card p-4 mb-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="font-semibold text-lg"><?php echo $order['customer_name']; ?></p>
                        <p class="text-sm text-slate-400">Order #<?php echo $order['order_number']; ?></p>
                        <p class="text-xs text-slate-500"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-purple-400">₹<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                </div>

                <div class="bg-slate-700 p-3 rounded-lg mb-3">
                    <p class="text-sm mb-1"><i class="fas fa-map-marker-alt text-purple-400"></i> <?php echo $order['delivery_address']; ?></p>
                    <p class="text-sm"><i class="fas fa-phone text-purple-400"></i> <?php echo $order['customer_phone']; ?></p>
                </div>

                <div class="flex gap-2">
                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white text-center py-2 rounded-lg text-sm">
                        <i class="fas fa-eye"></i> View Details
                    </a>

                    <?php if($order['order_status'] == 'pending'): ?>
                        <form method="POST" action="" class="flex-1">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="new_status" value="confirmed">
                            <button type="submit" name="update_status" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm">
                                <i class="fas fa-check"></i> Accept
                            </button>
                        </form>
                        <form method="POST" action="" class="flex-1">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="new_status" value="cancelled">
                            <button type="submit" name="update_status" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </form>
                    <?php elseif($order['order_status'] == 'confirmed'): ?>
                        <form method="POST" action="" class="flex-1">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="new_status" value="preparing">
                            <button type="submit" name="update_status" class="w-full btn-primary text-white py-2 rounded-lg text-sm">
                                <i class="fas fa-fire"></i> Start Preparing
                            </button>
                        </form>
                    <?php elseif($order['order_status'] == 'preparing'): ?>
                        <form method="POST" action="" class="flex-1">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="new_status" value="out_for_delivery">
                            <button type="submit" name="update_status" class="w-full btn-primary text-white py-2 rounded-lg text-sm">
                                <i class="fas fa-motorcycle"></i> Ready for Delivery
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card p-8 text-center">
            <i class="fas fa-receipt text-6xl text-slate-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No orders found</h3>
            <p class="text-slate-400">Orders will appear here when customers place them</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../common/bottom.php'; ?>
