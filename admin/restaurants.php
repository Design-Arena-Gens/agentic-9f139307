<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_ADMIN) {
    header("Location: ../login.php");
    exit();
}

// Handle status toggle
if(isset($_POST['toggle_status'])) {
    $restaurant_id = (int)$_POST['restaurant_id'];
    $sql = "UPDATE restaurants SET status = IF(status = 'active', 'inactive', 'active') WHERE id = $restaurant_id";
    mysqli_query($conn, $sql);
    header("Location: restaurants.php?updated=1");
    exit();
}

// Get all restaurants
$sql = "SELECT r.*, u.name as owner_name, u.email as owner_email
        FROM restaurants r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC";
$restaurants = mysqli_query($conn, $sql);

$page_title = 'Manage Restaurants';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Manage Restaurants</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 py-6">
    <?php if(isset($_GET['updated'])): ?>
        <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-4">
            Restaurant status updated successfully!
        </div>
    <?php endif; ?>

    <!-- Restaurants Table -->
    <div class="card p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Restaurant</th>
                        <th class="px-4 py-3 text-left">Owner</th>
                        <th class="px-4 py-3 text-left">Phone</th>
                        <th class="px-4 py-3 text-left">Rating</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($restaurant = mysqli_fetch_assoc($restaurants)): ?>
                        <tr class="border-b border-slate-700 hover:bg-slate-700/50">
                            <td class="px-4 py-3">
                                <p class="font-semibold"><?php echo $restaurant['name']; ?></p>
                                <p class="text-xs text-slate-400"><?php echo substr($restaurant['address'], 0, 40); ?>...</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold"><?php echo $restaurant['owner_name']; ?></p>
                                <p class="text-xs text-slate-400"><?php echo $restaurant['owner_email']; ?></p>
                            </td>
                            <td class="px-4 py-3 text-slate-400"><?php echo $restaurant['phone']; ?></td>
                            <td class="px-4 py-3">
                                <span class="text-yellow-400">
                                    <i class="fas fa-star"></i> <?php echo $restaurant['rating']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded <?php echo $restaurant['status'] == 'active' ? 'bg-green-900/50 text-green-400' : 'bg-red-900/50 text-red-400'; ?>">
                                    <?php echo ucfirst($restaurant['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400"><?php echo date('d M Y', strtotime($restaurant['created_at'])); ?></td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
                                    <button type="submit" name="toggle_status" class="text-sm px-3 py-1 rounded <?php echo $restaurant['status'] == 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'; ?>">
                                        <?php echo $restaurant['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
