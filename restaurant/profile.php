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

// Handle Update
if(isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $delivery_time = mysqli_real_escape_string($conn, $_POST['delivery_time']);

    $sql = "UPDATE restaurants SET name = '$name', description = '$description', address = '$address',
            phone = '$phone', delivery_time = '$delivery_time' WHERE user_id = $user_id";

    if(mysqli_query($conn, $sql)) {
        $success = 'Profile updated successfully!';
        $restaurant['name'] = $name;
        $restaurant['description'] = $description;
        $restaurant['address'] = $address;
        $restaurant['phone'] = $phone;
        $restaurant['delivery_time'] = $delivery_time;
    } else {
        $error = 'Update failed. Please try again.';
    }
}

$page_title = 'Restaurant Profile';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Profile</h2>
        </div>
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

    <form method="POST" action="">
        <div class="card p-6 mb-6">
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Restaurant Name</label>
                <input type="text" name="name" required class="input-field w-full" value="<?php echo $restaurant['name']; ?>">
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Description</label>
                <textarea name="description" rows="3" class="input-field w-full"><?php echo $restaurant['description']; ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Address</label>
                <textarea name="address" rows="3" required class="input-field w-full"><?php echo $restaurant['address']; ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Phone Number</label>
                <input type="tel" name="phone" required class="input-field w-full" value="<?php echo $restaurant['phone']; ?>">
            </div>
            <div class="mb-6">
                <label class="block text-slate-300 mb-2">Delivery Time</label>
                <input type="text" name="delivery_time" class="input-field w-full" value="<?php echo $restaurant['delivery_time']; ?>">
            </div>
            <button type="submit" name="update_profile" class="w-full btn-primary text-white py-3 rounded-lg font-semibold">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </div>
    </form>

    <a href="../logout.php" class="block bg-red-600 hover:bg-red-700 text-white text-center py-4 rounded-lg font-semibold mb-6">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<?php include '../common/bottom.php'; ?>
