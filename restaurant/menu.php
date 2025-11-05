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

// Handle Add Item
if(isset($_POST['add_item'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $is_veg = isset($_POST['is_veg']) ? 1 : 0;

    $sql = "INSERT INTO menu_items (restaurant_id, name, description, price, category, is_veg)
            VALUES ($restaurant_id, '$name', '$description', $price, '$category', $is_veg)";

    if(mysqli_query($conn, $sql)) {
        $success = 'Menu item added successfully!';
    } else {
        $error = 'Failed to add menu item.';
    }
}

// Handle Delete Item
if(isset($_POST['delete_item'])) {
    $item_id = (int)$_POST['item_id'];
    $sql = "DELETE FROM menu_items WHERE id = $item_id AND restaurant_id = $restaurant_id";
    mysqli_query($conn, $sql);
    header("Location: menu.php?deleted=1");
    exit();
}

// Handle Toggle Availability
if(isset($_POST['toggle_availability'])) {
    $item_id = (int)$_POST['item_id'];
    $sql = "UPDATE menu_items SET is_available = NOT is_available WHERE id = $item_id AND restaurant_id = $restaurant_id";
    mysqli_query($conn, $sql);
    header("Location: menu.php");
    exit();
}

// Get menu items
$sql = "SELECT * FROM menu_items WHERE restaurant_id = $restaurant_id ORDER BY category, name";
$menu_items = mysqli_query($conn, $sql);

// Group by category
$menu_by_category = [];
mysqli_data_seek($menu_items, 0);
while($item = mysqli_fetch_assoc($menu_items)) {
    $category = $item['category'] ? $item['category'] : 'Other';
    $menu_by_category[$category][] = $item;
}

$page_title = 'Menu Management';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-slate-300 hover:text-white">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h2 class="text-xl font-bold text-white">Menu</h2>
            </div>
            <button onclick="toggleAddForm()" class="btn-primary px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-plus"></i> Add Item
            </button>
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

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-4">
            Menu item deleted successfully!
        </div>
    <?php endif; ?>

    <!-- Add Item Form -->
    <div id="addForm" class="card p-6 mb-6 hidden">
        <h3 class="font-semibold mb-4">Add New Menu Item</h3>
        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Item Name *</label>
                <input type="text" name="name" required class="input-field w-full" placeholder="e.g., Paneer Tikka">
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Description</label>
                <textarea name="description" rows="2" class="input-field w-full" placeholder="Brief description"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Price (₹) *</label>
                <input type="number" name="price" required step="0.01" class="input-field w-full" placeholder="0.00">
            </div>
            <div class="mb-4">
                <label class="block text-slate-300 mb-2">Category</label>
                <input type="text" name="category" class="input-field w-full" placeholder="e.g., Starters, Main Course">
            </div>
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_veg" value="1" class="w-5 h-5">
                    <span>Vegetarian Item</span>
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" name="add_item" class="flex-1 btn-primary text-white py-3 rounded-lg font-semibold">
                    <i class="fas fa-plus"></i> Add Item
                </button>
                <button type="button" onclick="toggleAddForm()" class="flex-1 bg-slate-700 text-white py-3 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Menu Items -->
    <?php if(!empty($menu_by_category)): ?>
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
                                    <?php if(!$item['is_available']): ?>
                                        <span class="text-xs bg-red-900 text-red-300 px-2 py-1 rounded">Unavailable</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-slate-400 mb-2"><?php echo $item['description']; ?></p>
                                <p class="text-lg font-bold text-purple-400">₹<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="flex flex-col gap-2">
                                <form method="POST" action="">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="toggle_availability" class="text-sm px-3 py-1 rounded <?php echo $item['is_available'] ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700'; ?>">
                                        <?php echo $item['is_available'] ? 'Hide' : 'Show'; ?>
                                    </button>
                                </form>
                                <form method="POST" action="" onsubmit="return confirm('Delete this item?');">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="delete_item" class="text-sm px-3 py-1 rounded bg-red-600 hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card p-8 text-center">
            <i class="fas fa-utensils text-6xl text-slate-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No menu items yet</h3>
            <p class="text-slate-400">Add your first menu item to get started</p>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleAddForm() {
        const form = document.getElementById('addForm');
        form.classList.toggle('hidden');
    }
</script>

<?php include '../common/bottom.php'; ?>
