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

// Get cart items
$sql = "SELECT c.*, m.name, m.price, r.id as restaurant_id
        FROM cart c
        JOIN menu_items m ON c.menu_item_id = m.id
        JOIN restaurants r ON m.restaurant_id = r.id
        WHERE c.user_id = $user_id";
$cart_items = mysqli_query($conn, $sql);

if(mysqli_num_rows($cart_items) == 0) {
    header("Location: cart.php");
    exit();
}

$cart_data = [];
$total = 0;
$restaurant_id = 0;

while($item = mysqli_fetch_assoc($cart_items)) {
    $cart_data[] = $item;
    $total += $item['price'] * $item['quantity'];
    $restaurant_id = $item['restaurant_id'];
}

$delivery_fee = 40;
$gst = $total * 0.05;
$grand_total = $total + $delivery_fee + $gst;

// Handle Order Placement
if(isset($_POST['place_order'])) {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    if(empty($address) || empty($phone)) {
        $error = 'Please fill all required fields';
    } else {
        // Generate order number
        $order_number = 'ORD' . time() . rand(100, 999);

        // Insert order
        $sql = "INSERT INTO orders (user_id, restaurant_id, order_number, total_amount, delivery_address, delivery_phone, payment_method, order_status)
                VALUES ($user_id, $restaurant_id, '$order_number', $grand_total, '$address', '$phone', '$payment_method', 'pending')";

        if(mysqli_query($conn, $sql)) {
            $order_id = mysqli_insert_id($conn);

            // Insert order items
            foreach($cart_data as $item) {
                $menu_item_id = $item['menu_item_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];

                $sql = "INSERT INTO order_items (order_id, menu_item_id, quantity, price)
                        VALUES ($order_id, $menu_item_id, $quantity, $price)";
                mysqli_query($conn, $sql);
            }

            // Clear cart
            $sql = "DELETE FROM cart WHERE user_id = $user_id";
            mysqli_query($conn, $sql);

            // Redirect to success page
            header("Location: order_success.php?order_id=$order_id");
            exit();
        } else {
            $error = 'Order placement failed. Please try again.';
        }
    }
}

$page_title = 'Checkout';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="cart.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Checkout</h2>
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
        <!-- Delivery Address -->
        <div class="card p-4 mb-4">
            <h3 class="font-semibold mb-4 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-purple-400"></i>
                Delivery Address
            </h3>
            <textarea name="address" rows="3" required class="input-field w-full"
                      placeholder="Enter your delivery address"><?php echo $user['address']; ?></textarea>
        </div>

        <!-- Contact Information -->
        <div class="card p-4 mb-4">
            <h3 class="font-semibold mb-4 flex items-center gap-2">
                <i class="fas fa-phone text-purple-400"></i>
                Contact Number
            </h3>
            <input type="tel" name="phone" required class="input-field w-full"
                   placeholder="Enter your phone number" value="<?php echo $user['phone']; ?>">
        </div>

        <!-- Order Summary -->
        <div class="card p-4 mb-4">
            <h3 class="font-semibold mb-4">Order Summary</h3>
            <?php foreach($cart_data as $item): ?>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-slate-300"><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></span>
                    <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>
            <?php endforeach; ?>
            <hr class="border-slate-700 my-3">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-400">Item Total</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Delivery Fee</span>
                    <span>₹<?php echo number_format($delivery_fee, 2); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">GST (5%)</span>
                    <span>₹<?php echo number_format($gst, 2); ?></span>
                </div>
                <hr class="border-slate-700 my-2">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total Amount</span>
                    <span class="text-purple-400">₹<?php echo number_format($grand_total, 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Method -->
        <div class="card p-4 mb-6">
            <h3 class="font-semibold mb-4 flex items-center gap-2">
                <i class="fas fa-credit-card text-purple-400"></i>
                Payment Method
            </h3>
            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="payment_method" value="razorpay" required class="w-5 h-5">
                    <div>
                        <p class="font-semibold">Razorpay (Online Payment)</p>
                        <p class="text-sm text-slate-400">Pay using UPI, Card, Net Banking</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" name="payment_method" value="cod" required class="w-5 h-5">
                    <div>
                        <p class="font-semibold">Cash on Delivery</p>
                        <p class="text-sm text-slate-400">Pay when you receive</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Place Order Button -->
        <button type="submit" name="place_order" class="w-full btn-primary text-white py-4 rounded-lg font-semibold mb-6">
            <i class="fas fa-check-circle"></i> Place Order - ₹<?php echo number_format($grand_total, 2); ?>
        </button>
    </form>
</div>

<?php include 'common/bottom.php'; ?>
