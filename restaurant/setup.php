<?php
require_once '../common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_RESTAURANT) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if restaurant already exists
$sql = "SELECT * FROM restaurants WHERE user_id = $user_id";
$restaurant_result = mysqli_query($conn, $sql);

if(mysqli_num_rows($restaurant_result) > 0) {
    header("Location: index.php");
    exit();
}

// Handle Restaurant Creation
if(isset($_POST['create_restaurant'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $delivery_time = mysqli_real_escape_string($conn, $_POST['delivery_time']);

    if(empty($name) || empty($address) || empty($phone)) {
        $error = 'Please fill all required fields';
    } else {
        $sql = "INSERT INTO restaurants (user_id, name, description, address, phone, delivery_time)
                VALUES ($user_id, '$name', '$description', '$address', '$phone', '$delivery_time')";

        if(mysqli_query($conn, $sql)) {
            header("Location: index.php?created=1");
            exit();
        } else {
            $error = 'Restaurant creation failed. Please try again.';
        }
    }
}

$page_title = 'Setup Restaurant';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Setup Restaurant</h2>
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

    <form method="POST" action="">
        <div class="card p-6 mb-4">
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Restaurant Name *</label>
                <input type="text" name="name" required class="input-field w-full" placeholder="Enter restaurant name">
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Description</label>
                <textarea name="description" rows="3" class="input-field w-full" placeholder="Brief description about your restaurant"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Address *</label>
                <textarea name="address" rows="3" required class="input-field w-full" placeholder="Enter restaurant address"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Phone Number *</label>
                <input type="tel" name="phone" required class="input-field w-full" placeholder="Enter phone number">
            </div>
            <div class="mb-6">
                <label class="block text-slate-300 mb-2">Delivery Time</label>
                <input type="text" name="delivery_time" class="input-field w-full" placeholder="e.g., 30-40 mins" value="30-40 mins">
            </div>
            <button type="submit" name="create_restaurant" class="w-full btn-primary text-white py-4 rounded-lg font-semibold">
                <i class="fas fa-plus-circle"></i> Create Restaurant
            </button>
        </div>
    </form>
</div>

<?php include '../common/bottom.php'; ?>
