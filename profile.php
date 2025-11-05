<?php
require_once 'common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_USER) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($user_result);

// Handle Profile Update
if(isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $sql = "UPDATE users SET name = '$name', phone = '$phone', address = '$address' WHERE id = $user_id";

    if(mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $name;
        $success = 'Profile updated successfully!';
        $user['name'] = $name;
        $user['phone'] = $phone;
        $user['address'] = $address;
    } else {
        $error = 'Update failed. Please try again.';
    }
}

$page_title = 'Profile';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <h2 class="text-xl font-bold text-white">My Profile</h2>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <?php if(isset($error)): ?>
        <div class="bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($success)): ?>
        <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-4">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Profile Form -->
    <form method="POST" action="">
        <div class="card p-6 mb-4">
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Full Name</label>
                <input type="text" name="name" required class="input-field w-full" value="<?php echo $user['name']; ?>">
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Email</label>
                <input type="email" class="input-field w-full bg-slate-700" value="<?php echo $user['email']; ?>" disabled>
                <p class="text-xs text-slate-400 mt-1">Email cannot be changed</p>
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Phone</label>
                <input type="tel" name="phone" required class="input-field w-full" value="<?php echo $user['phone']; ?>">
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Address</label>
                <textarea name="address" rows="3" class="input-field w-full"><?php echo $user['address']; ?></textarea>
            </div>
            <button type="submit" name="update_profile" class="w-full btn-primary text-white py-3 rounded-lg font-semibold">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </div>
    </form>

    <!-- Account Info -->
    <div class="card p-4 mb-4">
        <h3 class="font-semibold mb-3">Account Information</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-400">Account Type</span>
                <span class="font-semibold"><?php echo ucfirst($user['role']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Member Since</span>
                <span class="font-semibold"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Account Status</span>
                <span class="font-semibold text-green-400"><?php echo ucfirst($user['status']); ?></span>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <a href="logout.php" class="block bg-red-600 hover:bg-red-700 text-white text-center py-4 rounded-lg font-semibold mb-6">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<?php include 'common/bottom.php'; ?>
