<?php
require_once 'common/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_USER) {
    header("Location: login.php");
    exit();
}

$search_query = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$results = [];

if($search_query || $category) {
    // Search in restaurants
    $sql = "SELECT DISTINCT r.*, 'restaurant' as result_type
            FROM restaurants r
            WHERE r.status = 'active' AND (
                r.name LIKE '%$search_query%' OR
                r.description LIKE '%$search_query%'
            )";

    $restaurants_result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($restaurants_result)) {
        $results[] = $row;
    }

    // Search in menu items
    $where_clause = "m.is_available = 1";
    if($search_query) {
        $where_clause .= " AND (m.name LIKE '%$search_query%' OR m.description LIKE '%$search_query%')";
    }
    if($category) {
        $where_clause .= " AND m.category = '$category'";
    }

    $sql = "SELECT m.*, r.name as restaurant_name, r.id as restaurant_id, 'menu_item' as result_type
            FROM menu_items m
            JOIN restaurants r ON m.restaurant_id = r.id
            WHERE $where_clause AND r.status = 'active'
            ORDER BY m.name";

    $menu_items_result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($menu_items_result)) {
        $results[] = $row;
    }
}

$page_title = 'Search';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-300 hover:text-white">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-xl font-bold text-white">Search</h2>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <!-- Search Form -->
    <form method="GET" action="" class="mb-6">
        <div class="relative mb-4">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>"
                   placeholder="Search for restaurants or dishes..."
                   class="input-field w-full pl-12 pr-4">
            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
        </div>
        <button type="submit" class="btn-primary w-full text-white py-3 rounded-lg font-semibold">
            <i class="fas fa-search"></i> Search
        </button>
    </form>

    <!-- Results -->
    <?php if($search_query || $category): ?>
        <h3 class="text-lg font-semibold mb-4">
            Search Results
            <?php if($category): ?>
                <span class="text-purple-400">for "<?php echo htmlspecialchars($category); ?>"</span>
            <?php endif; ?>
        </h3>

        <?php if(!empty($results)): ?>
            <?php foreach($results as $result): ?>
                <?php if($result['result_type'] == 'restaurant'): ?>
                    <!-- Restaurant Result -->
                    <a href="restaurant.php?id=<?php echo $result['id']; ?>" class="card p-4 mb-4 block hover:bg-slate-700">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-store text-purple-400"></i>
                            <span class="text-xs text-purple-400 uppercase font-semibold">Restaurant</span>
                        </div>
                        <h4 class="font-semibold text-lg mb-1"><?php echo $result['name']; ?></h4>
                        <p class="text-sm text-slate-400 mb-2"><?php echo $result['description']; ?></p>
                        <div class="flex items-center gap-4 text-sm">
                            <span class="text-green-400">
                                <i class="fas fa-star"></i> <?php echo $result['rating']; ?>
                            </span>
                            <span class="text-slate-400">
                                <i class="far fa-clock"></i> <?php echo $result['delivery_time']; ?>
                            </span>
                        </div>
                    </a>
                <?php else: ?>
                    <!-- Menu Item Result -->
                    <a href="restaurant.php?id=<?php echo $result['restaurant_id']; ?>" class="card p-4 mb-4 block hover:bg-slate-700">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-utensils text-orange-400"></i>
                            <span class="text-xs text-orange-400 uppercase font-semibold">Dish</span>
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <?php if($result['is_veg']): ?>
                                <span class="w-4 h-4 border-2 border-green-500 flex items-center justify-center">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                </span>
                            <?php else: ?>
                                <span class="w-4 h-4 border-2 border-red-500 flex items-center justify-center">
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                </span>
                            <?php endif; ?>
                            <h4 class="font-semibold text-lg"><?php echo $result['name']; ?></h4>
                        </div>
                        <p class="text-sm text-slate-400 mb-2"><?php echo $result['restaurant_name']; ?></p>
                        <p class="text-lg font-bold text-purple-400">₹<?php echo number_format($result['price'], 2); ?></p>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card p-8 text-center">
                <i class="fas fa-search text-6xl text-slate-600 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">No results found</h3>
                <p class="text-slate-400">Try searching with different keywords</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="card p-8 text-center">
            <i class="fas fa-search text-6xl text-slate-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Start Searching</h3>
            <p class="text-slate-400">Enter a keyword to find restaurants or dishes</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
