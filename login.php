<?php
require_once 'common/config.php';

// If already logged in, redirect based on role
if(isset($_SESSION['user_id'])) {
    if($_SESSION['user_role'] == ROLE_ADMIN) {
        header("Location: admin/index.php");
    } elseif($_SESSION['user_role'] == ROLE_RESTAURANT) {
        header("Location: restaurant/index.php");
    } elseif($_SESSION['user_role'] == ROLE_DELIVERY) {
        header("Location: delivery/index.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error = '';
$success = '';

if(isset($_GET['installed'])) {
    $success = 'Installation successful! Please login.';
}

// Handle Login
if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)) {
        $error = 'All fields are required';
    } else {
        // Check in users table
        $sql = "SELECT * FROM users WHERE email = '$email' AND status = 'active'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                if($user['role'] == ROLE_RESTAURANT) {
                    header("Location: restaurant/index.php");
                } elseif($user['role'] == ROLE_DELIVERY) {
                    header("Location: delivery/index.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = 'Invalid credentials';
            }
        } else {
            $error = 'Invalid credentials';
        }
    }
}

// Handle Admin Login
if(isset($_POST['admin_login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if(empty($username) || empty($password)) {
        $error = 'All fields are required';
    } else {
        $sql = "SELECT * FROM admin WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
            if(password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_name'] = $admin['username'];
                $_SESSION['user_role'] = ROLE_ADMIN;
                header("Location: admin/index.php");
                exit();
            } else {
                $error = 'Invalid credentials';
            }
        } else {
            $error = 'Invalid credentials';
        }
    }
}

// Handle Signup
if(isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    if(empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'All fields are required';
    } elseif($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif(!in_array($role, [ROLE_USER, ROLE_RESTAURANT, ROLE_DELIVERY])) {
        $error = 'Invalid role selected';
    } else {
        // Check if email exists
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            $error = 'Email already registered';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, phone, password, role) VALUES ('$name', '$email', '$phone', '$hashed_password', '$role')";

            if(mysqli_query($conn, $sql)) {
                $success = 'Registration successful! Please login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$page_title = 'Login';
include 'common/header.php';
?>

<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent">
                <i class="fas fa-utensils"></i> <?php echo SITE_NAME; ?>
            </h1>
            <p class="text-slate-400 mt-2">Jabalpur ka sabse achha food delivery app</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <div class="flex mb-6 card">
            <button onclick="showTab('login')" id="loginTab" class="flex-1 py-3 text-center font-semibold border-b-2 border-purple-500 text-purple-400">
                Login
            </button>
            <button onclick="showTab('signup')" id="signupTab" class="flex-1 py-3 text-center font-semibold border-b-2 border-transparent text-slate-400">
                Signup
            </button>
            <button onclick="showTab('admin')" id="adminTab" class="flex-1 py-3 text-center font-semibold border-b-2 border-transparent text-slate-400">
                Admin
            </button>
        </div>

        <!-- Login Form -->
        <div id="loginForm" class="card p-6">
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-slate-300 mb-2">Email</label>
                    <input type="email" name="email" required class="input-field w-full" placeholder="Enter your email">
                </div>
                <div class="mb-6">
                    <label class="block text-slate-300 mb-2">Password</label>
                    <input type="password" name="password" required class="input-field w-full" placeholder="Enter your password">
                </div>
                <button type="submit" name="login" class="w-full btn-primary text-white py-3 rounded-lg font-semibold">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>

        <!-- Signup Form -->
        <div id="signupForm" class="card p-6 hidden">
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-slate-300 mb-2">Full Name</label>
                    <input type="text" name="name" required class="input-field w-full" placeholder="Enter your name">
                </div>
                <div class="mb-4">
                    <label class="block text-slate-300 mb-2">Email</label>
                    <input type="email" name="email" required class="input-field w-full" placeholder="Enter your email">
                </div>
                <div class="mb-4">
                    <label class="block text-slate-300 mb-2">Phone</label>
                    <input type="tel" name="phone" required class="input-field w-full" placeholder="Enter your phone number">
                </div>
                <div class="mb-4">
                    <label class="block text-slate-300 mb-2">Role</label>
                    <select name="role" required class="input-field w-full">
                        <option value="user">Customer</option>
                        <option value="restaurant">Restaurant Owner</option>
                        <option value="delivery">Delivery Boy</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-slate-300 mb-2">Password</label>
                    <input type="password" name="password" required class="input-field w-full" placeholder="Enter password">
                </div>
                <div class="mb-6">
                    <label class="block text-slate-300 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required class="input-field w-full" placeholder="Confirm password">
                </div>
                <button type="submit" name="signup" class="w-full btn-primary text-white py-3 rounded-lg font-semibold">
                    <i class="fas fa-user-plus"></i> Sign Up
                </button>
            </form>
        </div>

        <!-- Admin Login Form -->
        <div id="adminForm" class="card p-6 hidden">
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-slate-300 mb-2">Admin Username</label>
                    <input type="text" name="username" required class="input-field w-full" placeholder="Enter admin username">
                </div>
                <div class="mb-6">
                    <label class="block text-slate-300 mb-2">Password</label>
                    <input type="password" name="password" required class="input-field w-full" placeholder="Enter admin password">
                </div>
                <button type="submit" name="admin_login" class="w-full btn-primary text-white py-3 rounded-lg font-semibold">
                    <i class="fas fa-shield-alt"></i> Admin Login
                </button>
            </form>
        </div>

        <div class="text-center mt-6 text-slate-400 text-sm">
            <p>Demo Credentials:</p>
            <p>User: user@test.com / user123</p>
            <p>Restaurant: restaurant@test.com / restaurant123</p>
            <p>Delivery: delivery@test.com / delivery123</p>
            <p>Admin: admin / admin123</p>
        </div>
    </div>
</div>

<script>
    function showTab(tab) {
        // Hide all forms
        document.getElementById('loginForm').classList.add('hidden');
        document.getElementById('signupForm').classList.add('hidden');
        document.getElementById('adminForm').classList.add('hidden');

        // Reset all tabs
        document.getElementById('loginTab').classList.remove('border-purple-500', 'text-purple-400');
        document.getElementById('loginTab').classList.add('border-transparent', 'text-slate-400');
        document.getElementById('signupTab').classList.remove('border-purple-500', 'text-purple-400');
        document.getElementById('signupTab').classList.add('border-transparent', 'text-slate-400');
        document.getElementById('adminTab').classList.remove('border-purple-500', 'text-purple-400');
        document.getElementById('adminTab').classList.add('border-transparent', 'text-slate-400');

        // Show selected form and highlight tab
        if(tab === 'login') {
            document.getElementById('loginForm').classList.remove('hidden');
            document.getElementById('loginTab').classList.add('border-purple-500', 'text-purple-400');
            document.getElementById('loginTab').classList.remove('border-transparent', 'text-slate-400');
        } else if(tab === 'signup') {
            document.getElementById('signupForm').classList.remove('hidden');
            document.getElementById('signupTab').classList.add('border-purple-500', 'text-purple-400');
            document.getElementById('signupTab').classList.remove('border-transparent', 'text-slate-400');
        } else if(tab === 'admin') {
            document.getElementById('adminForm').classList.remove('hidden');
            document.getElementById('adminTab').classList.add('border-purple-500', 'text-purple-400');
            document.getElementById('adminTab').classList.remove('border-transparent', 'text-slate-400');
        }
    }
</script>

</body>
</html>
