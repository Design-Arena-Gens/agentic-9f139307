<?php
require_once 'common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_USER) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Remove from Cart
if(isset($_POST['remove_item'])) {
    $cart_id = (int)$_POST['cart_id'];
    $sql = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
    mysqli_query($conn, $sql);
    header("Location: cart.php");
    exit();
}

// Handle Update Quantity
if(isset($_POST['update_quantity'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    if($quantity > 0) {
        $sql = "UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id";
        mysqli_query($conn, $sql);
    }
    header("Location: cart.php");
    exit();
}

// Get cart items
$sql = "SELECT c.*, m.name, m.price, m.is_veg, r.name as restaurant_name, r.id as restaurant_id
        FROM cart c
        JOIN menu_items m ON c.menu_item_id = m.id
        JOIN restaurants r ON m.restaurant_id = r.id
        WHERE c.user_id = $user_id
        ORDER BY c.created_at DESC";
$cart_items = mysqli_query($conn, $sql);

$total = 0;
$items_count = 0;

$page_title = 'Cart';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">My Cart</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <?php if(mysqli_num_rows($cart_items) > 0): ?>
        <!-- Cart Items -->
        <?php
        $current_restaurant = '';
        while($item = mysqli_fetch_assoc($cart_items)):
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
            $items_count += $item['quantity'];

            // Show restaurant header if different
            if($current_restaurant != $item['restaurant_name']):
                $current_restaurant = $item['restaurant_name'];
        ?>
                <div class="mb-4 mt-6 first:mt-0">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-store text-purple-400"></i>
                        <?php echo $current_restaurant; ?>
                    </h3>
                </div>
        <?php endif; ?>

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
                        <p class="text-sm text-slate-400 mb-2">₹<?php echo number_format($item['price'], 2); ?> each</p>
                        <p class="text-lg font-bold text-purple-400">₹<?php echo number_format($subtotal, 2); ?></p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <!-- Quantity Controls -->
                        <div class="flex items-center gap-2 bg-slate-700 rounded-lg">
                            <form method="POST" action="">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">
                                <button type="submit" name="update_quantity" class="px-3 py-1 text-xl hover:text-purple-400">-</button>
                            </form>
                            <span class="px-2 font-semibold"><?php echo $item['quantity']; ?></span>
                            <form method="POST" action="">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                <button type="submit" name="update_quantity" class="px-3 py-1 text-xl hover:text-purple-400">+</button>
                            </form>
                        </div>
                        <!-- Remove Button -->
                        <form method="POST" action="">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="remove_item" class="text-red-400 hover:text-red-300 text-sm">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- Bill Summary -->
        <div class="card p-4 mb-6 mt-6">
            <h3 class="font-semibold mb-4">Bill Summary</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-400">Item Total (<?php echo $items_count; ?> items)</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Delivery Fee</span>
                    <span>₹40.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">GST (5%)</span>
                    <span>₹<?php echo number_format($total * 0.05, 2); ?></span>
                </div>
                <hr class="border-slate-700 my-2">
                <div class="flex justify-between text-lg font-bold">
                    <span>To Pay</span>
                    <span class="text-purple-400">₹<?php echo number_format($total + 40 + ($total * 0.05), 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Checkout Button -->
        <a href="checkout.php" class="block btn-primary text-white text-center py-4 rounded-lg font-semibold mb-6">
            <i class="fas fa-shopping-bag"></i> Proceed to Checkout
        </a>

    <?php else: ?>
        <div class="card p-8 text-center">
            <i class="fas fa-shopping-cart text-6xl text-slate-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Your cart is empty</h3>
            <p class="text-slate-400 mb-6">Add items to get started</p>
            <a href="index.php" class="btn-primary text-white px-6 py-3 rounded-lg inline-block">
                <i class="fas fa-utensils"></i> Browse Restaurants
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
