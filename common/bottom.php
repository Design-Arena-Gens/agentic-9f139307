    <!-- Fixed Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-slate-800 border-t border-slate-700 z-50">
        <div class="max-w-md mx-auto">
            <div class="flex justify-around items-center py-3">
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == ROLE_USER): ?>
                    <a href="<?php echo BASE_URL; ?>index.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-home text-xl mb-1"></i>
                        <span class="text-xs">Home</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>search.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-search text-xl mb-1"></i>
                        <span class="text-xs">Search</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>cart.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-shopping-cart text-xl mb-1"></i>
                        <span class="text-xs">Cart</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>orders.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-receipt text-xl mb-1"></i>
                        <span class="text-xs">Orders</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>profile.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-user text-xl mb-1"></i>
                        <span class="text-xs">Profile</span>
                    </a>
                <?php elseif(isset($_SESSION['user_role']) && $_SESSION['user_role'] == ROLE_RESTAURANT): ?>
                    <a href="<?php echo BASE_URL; ?>restaurant/index.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-home text-xl mb-1"></i>
                        <span class="text-xs">Dashboard</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>restaurant/menu.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-utensils text-xl mb-1"></i>
                        <span class="text-xs">Menu</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>restaurant/orders.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-clipboard-list text-xl mb-1"></i>
                        <span class="text-xs">Orders</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>restaurant/profile.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-user text-xl mb-1"></i>
                        <span class="text-xs">Profile</span>
                    </a>
                <?php elseif(isset($_SESSION['user_role']) && $_SESSION['user_role'] == ROLE_DELIVERY): ?>
                    <a href="<?php echo BASE_URL; ?>delivery/index.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-home text-xl mb-1"></i>
                        <span class="text-xs">Dashboard</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>delivery/orders.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-motorcycle text-xl mb-1"></i>
                        <span class="text-xs">Orders</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>delivery/history.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-history text-xl mb-1"></i>
                        <span class="text-xs">History</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>delivery/profile.php" class="flex flex-col items-center text-slate-300 hover:text-purple-400">
                        <i class="fas fa-user text-xl mb-1"></i>
                        <span class="text-xs">Profile</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
