<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_ADMIN) {
    header("Location: ../login.php");
    exit();
}

// Handle status toggle
if(isset($_POST['toggle_status'])) {
    $user_id = (int)$_POST['user_id'];
    $sql = "UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = $user_id";
    mysqli_query($conn, $sql);
    header("Location: users.php?updated=1");
    exit();
}

// Get filter
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';

// Build query
$where_clause = "1=1";
if($role_filter != 'all') {
    $where_clause .= " AND role = '$role_filter'";
}

$sql = "SELECT * FROM users WHERE $where_clause ORDER BY created_at DESC";
$users = mysqli_query($conn, $sql);

$page_title = 'Manage Users';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Manage Users</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 py-6">
    <?php if(isset($_GET['updated'])): ?>
        <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-4">
            User status updated successfully!
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <a href="users.php?role=all" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $role_filter == 'all' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            All
        </a>
        <a href="users.php?role=user" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $role_filter == 'user' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Customers
        </a>
        <a href="users.php?role=restaurant" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $role_filter == 'restaurant' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Restaurants
        </a>
        <a href="users.php?role=delivery" class="px-4 py-2 rounded-lg flex-shrink-0 <?php echo $role_filter == 'delivery' ? 'btn-primary text-white' : 'bg-slate-700 text-slate-300'; ?>">
            Delivery Boys
        </a>
    </div>

    <!-- Users Table -->
    <div class="card p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Phone</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Joined</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($users)): ?>
                        <tr class="border-b border-slate-700 hover:bg-slate-700/50">
                            <td class="px-4 py-3 font-semibold"><?php echo $user['name']; ?></td>
                            <td class="px-4 py-3 text-slate-400"><?php echo $user['email']; ?></td>
                            <td class="px-4 py-3 text-slate-400"><?php echo $user['phone']; ?></td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded bg-slate-700 capitalize">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded <?php echo $user['status'] == 'active' ? 'bg-green-900/50 text-green-400' : 'bg-red-900/50 text-red-400'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400"><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="toggle_status" class="text-sm px-3 py-1 rounded <?php echo $user['status'] == 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'; ?>">
                                        <?php echo $user['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
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
