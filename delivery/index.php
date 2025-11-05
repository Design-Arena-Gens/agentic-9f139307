<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_DELIVERY) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get delivery statistics
$sql = "SELECT COUNT(*) as total_deliveries FROM orders WHERE delivery_boy_id = $user_id";
$total_deliveries = mysqli_fetch_assoc(mysqli_query($conn, $sql))['total_deliveries'];

$sql = "SELECT COUNT(*) as active_deliveries FROM orders WHERE delivery_boy_id = $user_id AND order_status IN ('out_for_delivery')";
$active_deliveries = mysqli_fetch_assoc(mysqli_query($conn, $sql))['active_deliveries'];

$sql = "SELECT COUNT(*) as completed_deliveries FROM orders WHERE delivery_boy_id = $user_id AND order_status = 'delivered'";
$completed_deliveries = mysqli_fetch_assoc(mysqli_query($conn, $sql))['completed_deliveries'];

$sql = "SELECT SUM(total_amount) as total_earnings FROM orders WHERE delivery_boy_id = $user_id AND order_status = 'delivered'";
$total_earnings = mysqli_fetch_assoc(mysqli_query($conn, $sql))['total_earnings'] ?? 0;

// Get available orders (out_for_delivery but not assigned)
$sql = "SELECT COUNT(*) as available_orders FROM orders WHERE delivery_boy_id IS NULL AND order_status = 'out_for_delivery'";
$available_orders = mysqli_fetch_assoc(mysqli_query($conn, $sql))['available_orders'];

$page_title = 'Delivery Dashboard';
require_once 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-motorcycle text-purple-400"></i> Delivery Panel
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
    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-motorcycle text-2xl text-orange-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?php echo $active_deliveries; ?></p>
                    <p class="text-sm text-slate-400">Active</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-2xl text-blue-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?php echo $available_orders; ?></p>
                    <p class="text-sm text-slate-400">Available</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-2xl text-green-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold"><?php echo $completed_deliveries; ?></p>
                    <p class="text-sm text-slate-400">Completed</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-2xl text-purple-400"></i>
                </div>
                <div>
                    <p class="text-xl font-bold">₹<?php echo number_format($total_earnings * 0.1, 0); ?></p>
                    <p class="text-sm text-slate-400">Earnings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card p-4 mb-6">
        <h3 class="font-semibold mb-3">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="orders.php" class="bg-slate-700 hover:bg-slate-600 p-4 rounded-lg text-center">
                <i class="fas fa-motorcycle text-2xl text-orange-400 mb-2"></i>
                <p class="text-sm">My Deliveries</p>
            </a>
            <a href="available.php" class="bg-slate-700 hover:bg-slate-600 p-4 rounded-lg text-center">
                <i class="fas fa-box text-2xl text-blue-400 mb-2"></i>
                <p class="text-sm">Available Orders</p>
            </a>
        </div>
    </div>

    <!-- Today's Performance -->
    <div class="card p-4 mb-6">
        <h3 class="font-semibold mb-4">Today's Performance</h3>
        <?php
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as today_deliveries FROM orders WHERE delivery_boy_id = $user_id AND order_status = 'delivered' AND DATE(created_at) = '$today'";
        $today_deliveries = mysqli_fetch_assoc(mysqli_query($conn, $sql))['today_deliveries'];

        $sql = "SELECT SUM(total_amount) as today_earnings FROM orders WHERE delivery_boy_id = $user_id AND order_status = 'delivered' AND DATE(created_at) = '$today'";
        $today_earnings = mysqli_fetch_assoc(mysqli_query($conn, $sql))['today_earnings'] ?? 0;
        ?>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Deliveries Completed</span>
                <span class="font-bold text-2xl text-green-400"><?php echo $today_deliveries; ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Earnings Today</span>
                <span class="font-bold text-2xl text-purple-400">₹<?php echo number_format($today_earnings * 0.1, 2); ?></span>
            </div>
        </div>
    </div>
</div>

<?php include '../common/bottom.php'; ?>
