<?php
require_once 'common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_USER) {
    header("Location: login.php");
    exit();
}

$restaurant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get restaurant details
$sql = "SELECT * FROM restaurants WHERE id = $restaurant_id AND status = 'active'";
$restaurant_result = mysqli_query($conn, $sql);

if(mysqli_num_rows($restaurant_result) == 0) {
    header("Location: index.php");
    exit();
}

$restaurant = mysqli_fetch_assoc($restaurant_result);

// Handle Add to Cart
if(isset($_POST['add_to_cart'])) {
    $menu_item_id = (int)$_POST['menu_item_id'];
    $quantity = (int)$_POST['quantity'];

    // Check if item already in cart
    $sql = "SELECT * FROM cart WHERE user_id = $user_id AND menu_item_id = $menu_item_id";
    $cart_check = mysqli_query($conn, $sql);

    if(mysqli_num_rows($cart_check) > 0) {
        // Update quantity
        $sql = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND menu_item_id = $menu_item_id";
    } else {
        // Insert new
        $sql = "INSERT INTO cart (user_id, menu_item_id, quantity) VALUES ($user_id, $menu_item_id, $quantity)";
    }

    mysqli_query($conn, $sql);
    header("Location: restaurant.php?id=$restaurant_id&added=1");
    exit();
}

// Get menu items
$sql = "SELECT * FROM menu_items WHERE restaurant_id = $restaurant_id AND is_available = 1 ORDER BY category, name";
$menu_items = mysqli_query($conn, $sql);

// Group by category
$menu_by_category = [];
while($item = mysqli_fetch_assoc($menu_items)) {
    $category = $item['category'] ? $item['category'] : 'Other';
    $menu_by_category[$category][] = $item;
}

$page_title = $restaurant['name'];
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-white"><?php echo $restaurant['name']; ?></h2>
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-green-400">
                        <i class="fas fa-star"></i> <?php echo $restaurant['rating']; ?>
                    </span>
                    <span class="text-slate-400">
                        <i class="far fa-clock"></i> <?php echo $restaurant['delivery_time']; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <?php if(isset($_GET['added'])): ?>
        <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-4">
            Item added to cart successfully!
        </div>
    <?php endif; ?>

    <!-- Restaurant Info -->
    <div class="card p-4 mb-6">
        <p class="text-slate-300 mb-3"><?php echo $restaurant['description']; ?></p>
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <i class="fas fa-map-marker-alt"></i>
            <span><?php echo $restaurant['address']; ?></span>
        </div>
    </div>

    <!-- Menu -->
    <?php foreach($menu_by_category as $category => $items): ?>
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
                <i class="fas fa-utensils text-purple-400"></i>
                <?php echo $category; ?>
            </h3>

            <?php foreach($items as $item): ?>
                <div class="card p-4 mb-3">
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <?php if($item['is_veg']): ?>
                                    <span class="w-4 h-4 border-2 border-green-500 flex items-center justify-center">
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    </span>
                                <?php else: ?>
                                    <span class="w-4 h-4 border-2 border-red-500 flex items-center justify-center">
                                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                    </span>
                                <?php endif; ?>
                                <h4 class="font-semibold"><?php echo $item['name']; ?></h4>
                            </div>
                            <p class="text-sm text-slate-400 mb-2"><?php echo $item['description']; ?></p>
                            <p class="text-lg font-bold text-purple-400">₹<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <form method="POST" action="" class="flex-shrink-0">
                            <input type="hidden" name="menu_item_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" name="add_to_cart" class="btn-primary text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php if(empty($menu_by_category)): ?>
        <div class="card p-6 text-center">
            <i class="fas fa-utensils text-4xl text-slate-600 mb-3"></i>
            <p class="text-slate-400">No menu items available</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
