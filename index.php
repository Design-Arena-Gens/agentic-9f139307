<?php
require_once 'common/config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != ROLE_USER) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all restaurants
$sql = "SELECT * FROM restaurants WHERE status = 'active' ORDER BY rating DESC";
$restaurants = mysqli_query($conn, $sql);

$page_title = 'Home';
include 'common/header.php';
?>

<!-- Top Header -->
<div class="bg-slate-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-md mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-utensils text-purple-400"></i> <?php echo SITE_NAME; ?>
                </h2>
                <p class="text-sm text-slate-400">Welcome, <?php echo $_SESSION['user_name']; ?></p>
            </div>
            <a href="logout.php" class="text-red-400 hover:text-red-300">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-md mx-auto px-4 py-4">
    <!-- Banner -->
    <div class="card p-6 mb-6 bg-gradient-to-r from-purple-600 to-pink-600">
        <h3 class="text-2xl font-bold text-white mb-2">Order Your Favorite Food</h3>
        <p class="text-purple-100">Fast delivery, fresh food, amazing taste!</p>
    </div>

    <!-- Search Bar -->
    <div class="mb-6">
        <form method="GET" action="search.php" class="relative">
            <input type="text" name="q" placeholder="Search for restaurants or dishes..."
                   class="input-field w-full pl-12 pr-4">
            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
        </form>
    </div>

    <!-- Categories -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-3">Categories</h3>
        <div class="flex gap-3 overflow-x-auto pb-2">
            <a href="search.php?category=Starters" class="flex-shrink-0 card px-6 py-3 text-center hover:bg-slate-700">
                <i class="fas fa-pepper-hot text-2xl text-orange-400 mb-2"></i>
                <p class="text-sm">Starters</p>
            </a>
            <a href="search.php?category=Main Course" class="flex-shrink-0 card px-6 py-3 text-center hover:bg-slate-700">
                <i class="fas fa-drumstick-bite text-2xl text-red-400 mb-2"></i>
                <p class="text-sm">Main Course</p>
            </a>
            <a href="search.php?category=Desserts" class="flex-shrink-0 card px-6 py-3 text-center hover:bg-slate-700">
                <i class="fas fa-ice-cream text-2xl text-pink-400 mb-2"></i>
                <p class="text-sm">Desserts</p>
            </a>
            <a href="search.php?category=Beverages" class="flex-shrink-0 card px-6 py-3 text-center hover:bg-slate-700">
                <i class="fas fa-mug-hot text-2xl text-yellow-400 mb-2"></i>
                <p class="text-sm">Beverages</p>
            </a>
        </div>
    </div>

    <!-- Restaurants List -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-3">Popular Restaurants</h3>

        <?php if(mysqli_num_rows($restaurants) > 0): ?>
            <?php while($restaurant = mysqli_fetch_assoc($restaurants)): ?>
                <a href="restaurant.php?id=<?php echo $restaurant['id']; ?>" class="card p-4 mb-4 block hover:bg-slate-700">
                    <div class="flex items-start gap-4">
                        <div class="w-20 h-20 bg-slate-700 rounded-lg flex items-center justify-center flex-shrink-0">
                            <?php if($restaurant['image']): ?>
                                <img src="<?php echo $restaurant['image']; ?>" alt="<?php echo $restaurant['name']; ?>"
                                     class="w-full h-full object-cover rounded-lg">
                            <?php else: ?>
                                <i class="fas fa-utensils text-3xl text-slate-500"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-lg mb-1"><?php echo $restaurant['name']; ?></h4>
                            <p class="text-sm text-slate-400 mb-2"><?php echo substr($restaurant['description'], 0, 60); ?>...</p>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="text-green-400">
                                    <i class="fas fa-star"></i> <?php echo $restaurant['rating']; ?>
                                </span>
                                <span class="text-slate-400">
                                    <i class="far fa-clock"></i> <?php echo $restaurant['delivery_time']; ?>
                                </span>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-slate-500"></i>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card p-6 text-center">
                <i class="fas fa-store-slash text-4xl text-slate-600 mb-3"></i>
                <p class="text-slate-400">No restaurants available</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
