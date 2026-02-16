<?php
// admin.php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Handle Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            // --- USER CRUD ---
            if ($_POST['action'] === 'add_user') {
                $name = $_POST['name'];
                $email = $_POST['email'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role = $_POST['role'];
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $password, $role]);
                $successMsg = "User added successfully.";
            } elseif ($_POST['action'] === 'edit_user') {
                $userId = $_POST['user_id'];
                $name = $_POST['name'];
                $email = $_POST['email'];
                $role = $_POST['role'];
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$name, $email, $role, $userId]);
                $successMsg = "User updated successfully.";
            } elseif ($_POST['action'] === 'update_tier') {
                $userId = $_POST['user_id'];
                $newTier = $_POST['tier'];
                $stmt = $pdo->prepare("UPDATE users SET tier = ? WHERE id = ?");
                $stmt->execute([$newTier, $userId]);
                $successMsg = "User tier updated successfully.";
            } elseif ($_POST['action'] === 'delete_user') {
                $id = $_POST['delete_id'];
                $pdo->prepare("DELETE FROM orders WHERE user_id = ?")->execute([$id]);
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $successMsg = "User deleted successfully.";

            // --- ORDER CRUD ---
            } elseif ($_POST['action'] === 'add_order') {
                $userId = $_POST['user_id'];
                $total = $_POST['total'];
                $status = $_POST['status'];
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $total, $status]);
                $successMsg = "Order created successfully.";
            } elseif ($_POST['action'] === 'edit_order') {
                $orderId = $_POST['order_id'];
                $total = $_POST['total'];
                $status = $_POST['status'];
                $stmt = $pdo->prepare("UPDATE orders SET total = ?, status = ? WHERE id = ?");
                $stmt->execute([$total, $status, $orderId]);
                $successMsg = "Order updated successfully.";
            } elseif ($_POST['action'] === 'update_status') {
                $orderId = $_POST['order_id'];
                $newStatus = $_POST['status'];
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $orderId]);
                $successMsg = "Order status updated successfully.";
            } elseif ($_POST['action'] === 'delete_order') {
                $id = $_POST['delete_id'];
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$id]);
                $successMsg = "Order deleted successfully.";

            // --- PRODUCT CRUD ---
            } elseif ($_POST['action'] === 'add_product') {
                $name = $_POST['name'];
                $price = $_POST['price'];
                $stock = $_POST['stock'];
                $category = $_POST['category'];
                $image = !empty($_POST['image']) ? $_POST['image'] : 'img/shop/default.png';
                $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, category, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $price, $stock, $category, $image]);
                $successMsg = "Product added successfully.";
            } elseif ($_POST['action'] === 'edit_product') {
                $id = $_POST['product_id'];
                $name = $_POST['name'];
                $price = $_POST['price'];
                $stock = $_POST['stock'];
                $category = $_POST['category'];
                $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, stock = ?, category = ? WHERE id = ?");
                $stmt->execute([$name, $price, $stock, $category, $id]);
                $successMsg = "Product updated successfully.";
            } elseif ($_POST['action'] === 'update_product') {
                $productId = $_POST['update_id'];
                $productData = $_POST['products'][$productId];
                $newName = $productData['name'];
                $newPrice = $productData['price'];
                $newStock = $productData['stock'];
                $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, stock = ? WHERE id = ?");
                $stmt->execute([$newName, $newPrice, $newStock, $productId]);
                $successMsg = "Product updated successfully.";
            } elseif ($_POST['action'] === 'delete_product') {
                $id = $_POST['delete_id'];
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $successMsg = "Product deleted successfully.";

            // --- REWARD CRUD ---
            } elseif ($_POST['action'] === 'add_reward') {
                $title = $_POST['title'];
                $description = $_POST['description'];
                $cost = $_POST['cost'];
                $stmt = $pdo->prepare("INSERT INTO rewards (title, description, cost) VALUES (?, ?, ?)");
                $stmt->execute([$title, $description, $cost]);
                $successMsg = "Reward added successfully.";
            } elseif ($_POST['action'] === 'edit_reward') {
                $id = $_POST['reward_id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $cost = $_POST['cost'];
                $stmt = $pdo->prepare("UPDATE rewards SET title = ?, description = ?, cost = ? WHERE id = ?");
                $stmt->execute([$title, $description, $cost, $id]);
                $successMsg = "Reward updated successfully.";
            } elseif ($_POST['action'] === 'delete_reward') {
                $id = $_POST['delete_id'];
                $stmt = $pdo->prepare("DELETE FROM rewards WHERE id = ?");
                $stmt->execute([$id]);
                $successMsg = "Reward deleted successfully.";
            }
        } catch (PDOException $e) {
            $errorMsg = "Operation failed: " . $e->getMessage();
        }
    }
}

try {
    // 1. Stats
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn() + 1042;
    $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() + 1128;
    $realRevenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
    $totalRevenue = ($realRevenue ? $realRevenue : 0) + 45280.50;

    // Fetch All Users for dropdowns
    $allUsers = $pdo->query("SELECT id, name FROM users WHERE role = 'customer' ORDER BY name")->fetchAll();

    // 2. Users (Pagination or limit for dashboard)
    if ($page === 'users' || $page === 'dashboard') {
        $recentUsers = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY joined_date DESC " . ($page === 'dashboard' ? "LIMIT 5" : ""))->fetchAll();
    }

    // 3. Orders
    if ($page === 'orders' || $page === 'dashboard') {
        $orders = $pdo->query("SELECT o.id, u.name as user_name, o.total, o.status, o.created_at FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC " . ($page === 'dashboard' ? "LIMIT 5" : ""))->fetchAll();
    }

    // 4. Leaderboard
    if ($page === 'leaderboard' || $page === 'dashboard') {
        $leaderboard = $pdo->query("SELECT id, name, points, tier FROM users WHERE role = 'customer' ORDER BY points DESC " . ($page === 'dashboard' ? "LIMIT 5" : ""))->fetchAll();
    }

    // 5. Products
    if ($page === 'products') {
        $products = $pdo->query("SELECT * FROM products ORDER BY category, name")->fetchAll();
    }

    // 6. Rewards
    if ($page === 'rewards') {
        $rewards = $pdo->query("SELECT * FROM rewards ORDER BY cost ASC")->fetchAll();
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="GoFit Admin">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | GoFit</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Muli:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">

    <style>
        body {
            background-color: #111;
            color: #ccc;
            overflow-x: hidden;
        }

        .admin-header {
            background: #1e1e1e;
            padding: 15px 30px;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .logo img {
            max-height: 40px;
        }

        .sidebar {
            width: 260px;
            background: #1e1e1e;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 70px;
            border-right: 1px solid #333;
            padding-top: 20px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: block;
            padding: 15px 30px;
            color: #888;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu li a:hover, .sidebar-menu li.active a {
            color: #fff;
            background: rgba(243, 97, 0, 0.1);
            border-left: 3px solid #f36100;
        }

        .sidebar-menu li a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: 260px;
            padding: 100px 30px 40px;
            min-height: 100vh;
        }

        .stat-card {
            background: #1e1e1e;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #f36100;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .stat-card h3 {
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        .stat-card p {
            color: #888;
            margin: 0;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .card-box {
            background: #1e1e1e;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 30px;
            border: 1px solid #333;
        }

        .card-box h4 {
            color: #f36100;
            font-family: Oswald, sans-serif;
            text-transform: uppercase;
            margin-bottom: 25px;
            font-size: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        table {
            width: 100%;
            background: transparent;
            color: #ddd;
        }

        table th {
            color: #888;
            font-weight: 400;
            text-transform: uppercase;
            font-size: 12px;
            border-bottom: 1px solid #333;
            padding: 12px 10px;
        }

        table td {
            padding: 15px 10px;
            border-bottom: 1px solid #222;
            font-size: 14px;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-completed { background: rgba(40, 167, 69, 0.2); color: #28a745; }
        .status-pending { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
        .status-cancelled { background: rgba(220, 53, 69, 0.2); color: #dc3545; }

        .btn-logout {
            color: #fff;
            background: #f36100;
            padding: 8px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }
        .btn-logout:hover {
            color: #fff;
            background: #ff7b24;
            transform: translateY(-2px);
        }

        .admin-select, .admin-input {
            background: #2a2a2a;
            color: #fff;
            border: 1px solid #444;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 13px;
            outline: none;
            transition: all 0.3s;
        }

        .admin-select:focus, .admin-input:focus {
            border-color: #f36100;
            background: #333;
        }

        .alert-admin {
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .btn-add {
            background: #28a745;
            padding: 5px 15px;
            font-size: 12px;
            font-family: Oswald;
            color: #fff;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-add:hover { background: #218838; transform: translateY(-2px); }

        /* Modal Styles */
        .modal-content {
            background: #1e1e1e;
            color: #ccc;
            border: 1px solid #333;
        }
        .modal-header { border-bottom: 1px solid #333; }
        .modal-footer { border-top: 1px solid #333; }
        .modal-title { color: #f36100; font-family: Oswald; }
        .form-control {
            background: #2a2a2a;
            border: 1px solid #444;
            color: #fff;
        }
        .form-control:focus {
            background: #333;
            color: #fff;
            border-color: #f36100;
            box-shadow: none;
        }
        label { color: #888; font-size: 13px; }

        @media (max-width: 991px) {
            .sidebar {
                width: 70px;
            }
            .sidebar-menu li a span {
                display: none;
            }
            .sidebar-menu li a i {
                margin-right: 0;
                font-size: 18px;
            }
            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>

<body>

    <div class="admin-header">
        <div class="logo">
            <a href="admin.php">
                <img src="img/logo.png" alt="GoFit Admin"> <span style="color:#fff; font-family: Oswald; margin-left: 10px;">ADMIN PORTAL</span>
            </a>
        </div>
        <div class="user-info">
            <span style="margin-right: 15px; color: #888;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <ul class="sidebar-menu">
            <li class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                <a href="admin.php?page=dashboard"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
            </li>
            <li class="<?php echo $page === 'users' ? 'active' : ''; ?>">
                <a href="admin.php?page=users"><i class="fa fa-users"></i> <span>Registered Users</span></a>
            </li>
            <li class="<?php echo $page === 'leaderboard' ? 'active' : ''; ?>">
                <a href="admin.php?page=leaderboard"><i class="fa fa-trophy"></i> <span>Leaderboards</span></a>
            </li>
            <li class="<?php echo $page === 'orders' ? 'active' : ''; ?>">
                <a href="admin.php?page=orders"><i class="fa fa-shopping-cart"></i> <span>Orders</span></a>
            </li>
            <li class="<?php echo $page === 'products' ? 'active' : ''; ?>">
                <a href="admin.php?page=products"><i class="fa fa-cube"></i> <span>Product Management</span></a>
            </li>
            <li class="<?php echo $page === 'rewards' ? 'active' : ''; ?>">
                <a href="admin.php?page=rewards"><i class="fa fa-gift"></i> <span>Rewards Management</span></a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            
            <?php if (isset($successMsg)): ?>
                <div class="alert alert-success alert-admin"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if (isset($errorMsg)): ?>
                <div class="alert alert-danger alert-admin"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <?php if ($page === 'dashboard'): ?>
                <!-- Stats Row -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h3><?php echo number_format($totalUsers); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card" style="border-left-color: #007bff;">
                            <h3><?php echo number_format($totalOrders); ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card" style="border-left-color: #28a745;">
                            <h3>RM <?php echo number_format($totalRevenue, 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card-box">
                            <h4>Recent Users</h4>
                            <div class="table-responsive">
                                <table>
                                    <thead><tr><th>Name</th><th>Joined</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo date('M d', strtotime($user['joined_date'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right mt-3">
                                <a href="admin.php?page=users" style="color:#f36100; font-size: 13px;">View All Users &raquo;</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card-box">
                            <h4>Recent Orders</h4>
                            <div class="table-responsive">
                                <table>
                                    <thead><tr><th>Order #</th><th>Total</th><th>Status</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td>RM <?php echo number_format($order['total'], 2); ?></td>
                                            <td><?php echo $order['status']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right mt-3">
                                <a href="admin.php?page=orders" style="color:#f36100; font-size: 13px;">View All Orders &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($page === 'users'): ?>
                <div class="card-box">
                    <h4>
                        Registered Users Management
                        <button class="btn-add" data-toggle="modal" data-target="#addUserModal">Add New User</button>
                    </h4>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" style="width: 24px; height: 24px; border-radius: 50%; margin-right: 5px; object-fit: cover;">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge-status status-pending"><?php echo $user['role']; ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($user['joined_date'])); ?></td>
                                    <td>
                                        <button class="badge-status status-pending" style="border:none; cursor:pointer;" 
                                                onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                                        <form method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="delete_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="badge-status status-cancelled" style="border:none; cursor:pointer;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentUsers)): ?>
                                    <tr><td colspan="6" class="text-center">No users found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($page === 'leaderboard'): ?>
                <div class="card-box">
                    <h4>Global Leaderboard & Rank Management</h4>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Name</th>
                                    <th>Tier</th>
                                    <th class="text-right">Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; foreach ($leaderboard as $lb): ?>
                                <tr>
                                    <td><?php echo $rank++; ?></td>
                                    <td><?php echo htmlspecialchars($lb['name']); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update_tier">
                                            <input type="hidden" name="user_id" value="<?php echo $lb['id']; ?>">
                                            <select name="tier" class="admin-select" onchange="this.form.submit()">
                                                <?php foreach (['Bronze', 'Silver', 'Gold', 'Platinum'] as $t): ?>
                                                    <option value="<?php echo $t; ?>" <?php echo $lb['tier'] == $t ? 'selected' : ''; ?>>
                                                        <?php echo $t; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-right"><strong><?php echo number_format($lb['points']); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($leaderboard)): ?>
                                    <tr><td colspan="4" class="text-center">No leaderboard data found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($page === 'orders'): ?>
                <div class="card-box">
                    <h4>
                        Order History & Fulfillment
                        <button class="btn-add" data-toggle="modal" data-target="#addOrderModal">Create Manual Order</button>
                    </h4>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                    <td>RM <?php echo number_format($order['total'], 2); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="admin-select" onchange="this.form.submit()" style="font-size: 11px; text-transform: uppercase;">
                                                <?php foreach (['Pending', 'Out for Delivery', 'Completed', 'Cancelled'] as $s): ?>
                                                    <option value="<?php echo $s; ?>" <?php echo $order['status'] == $s ? 'selected' : ''; ?>>
                                                        <?php echo $s; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <button class="badge-status status-pending" style="border:none; cursor:pointer;"
                                                onclick="editOrder(<?php echo htmlspecialchars(json_encode($order)); ?>)">Edit</button>
                                        <form method="POST" onsubmit="return confirm('Delete order?');" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_order">
                                            <input type="hidden" name="delete_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="badge-status status-cancelled" style="border:none; cursor:pointer;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="6" class="text-center">No orders found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($page === 'products'): ?>
                <div class="card-box">
                    <h4>
                        Product Inventory Management
                        <button class="btn-add" data-toggle="modal" data-target="#addProductModal">Add New Product</button>
                    </h4>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price (RM)</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>#<?php echo $product['id']; ?></td>
                                    <td><img src="<?php echo htmlspecialchars($product['image']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;"></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo $product['category']; ?></td>
                                    <td>RM <?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td>
                                        <button class="badge-status status-pending" style="border:none; cursor:pointer;"
                                                onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">Edit</button>
                                        <form method="POST" onsubmit="return confirm('Delete product?');" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="delete_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="badge-status status-cancelled" style="border:none; cursor:pointer;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($products)): ?>
                                    <tr><td colspan="7" class="text-center">No products found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($page === 'rewards'): ?>
                <div class="card-box">
                    <h4>
                        Reward Items Management
                        <button class="btn-add" data-toggle="modal" data-target="#addRewardModal">Add New Reward</button>
                    </h4>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Cost</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rewards as $reward): ?>
                                <tr>
                                    <td>#<?php echo $reward['id']; ?></td>
                                    <td><?php echo htmlspecialchars($reward['title']); ?></td>
                                    <td><?php echo number_format($reward['cost']); ?> PTS</td>
                                    <td><?php echo htmlspecialchars($reward['description']); ?></td>
                                    <td>
                                        <button class="badge-status status-pending" style="border:none; cursor:pointer;"
                                                onclick="editReward(<?php echo htmlspecialchars(json_encode($reward)); ?>)">Edit</button>
                                        <form method="POST" onsubmit="return confirm('Delete reward?');" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_reward">
                                            <input type="hidden" name="delete_id" value="<?php echo $reward['id']; ?>">
                                            <button type="submit" class="badge-status status-cancelled" style="border:none; cursor:pointer;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($rewards)): ?>
                                    <tr><td colspan="5" class="text-center">No rewards found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- MODALS -->
    <!-- User Modals -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add New User</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_user">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="form-group"><label>Role</label>
                        <select name="role" class="form-control"><option value="customer">Customer</option><option value="admin">Admin</option></select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn-add">Save User</button></div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit User</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="user_id" id="edit_u_id">
                    <div class="form-group"><label>Name</label><input type="text" name="name" id="edit_u_name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" id="edit_u_email" class="form-control" required></div>
                    <div class="form-group"><label>Role</label>
                        <select name="role" id="edit_u_role" class="form-control"><option value="customer">Customer</option><option value="admin">Admin</option></select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn-add">Update User</button></div>
            </form>
        </div>
    </div>

    <!-- Order Modals -->
    <div class="modal fade" id="addOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Create Manual Order</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_order">
                    <div class="form-group"><label>Customer</label>
                        <select name="user_id" class="form-control">
                            <?php foreach ($allUsers as $u): ?><option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Total amount (RM)</label><input type="number" step="0.01" name="total" class="form-control" required></div>
                    <div class="form-group"><label>Status</label>
                        <select name="status" class="form-control"><option>Pending</option><option>Completed</option></select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn-add">Create Order</button></div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="editOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Order</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_order">
                    <input type="hidden" name="order_id" id="edit_o_id">
                    <div class="form-group"><label>Total Amount (RM)</label><input type="number" step="0.01" name="total" id="edit_o_total" class="form-control" required></div>
                    <div class="form-group"><label>Status</label>
                        <select name="status" id="edit_o_status" class="form-control">
                            <option>Pending</option><option>Out for Delivery</option><option>Completed</option><option>Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn-add">Update Order</button></div>
            </form>
        </div>
    </div>

    <!-- Product Modals -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add New Product</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_product">
                    <div class="form-group"><label>Product Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Price (RM)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                    <div class="form-group"><label>Stock</label><input type="number" name="stock" class="form-control" required></div>
                    <div class="form-group"><label>Category</label>
                        <select name="category" class="form-control"><option>Merchandise</option><option>Supplements</option></select>
                    </div>
                    <div class="form-group"><label>Image Path</label><input type="text" name="image" class="form-control" placeholder="img/shop/product.png"></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn-add">Save Product</button></div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Product</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_product">
                    <input type="hidden" name="product_id" id="edit_p_id">
                    <div class="form-group"><label>Product Name</label><input type="text" name="name" id="edit_p_name" class="form-control" required></div>
                    <div class="form-group"><label>Price (RM)</label><input type="number" step="0.01" name="price" id="edit_p_price" class="form-control" required></div>
                    <div class="form-group"><label>Stock</label><input type="number" name="stock" id="edit_p_stock" class="form-control" required></div>
                    <div class="form-group"><label>Category</label>
                        <select name="category" id="edit_p_cat" class="form-control"><option>Merchandise</option><option>Supplements</option></select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn-add">Update Product</button></div>
            </form>
        </div>
    </div>

    <!-- Reward Modals -->
    <div class="modal fade" id="addRewardModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add New Reward</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_reward">
                    <div class="form-group"><label>Reward Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="form-group"><label>Cost (Points)</label><input type="number" name="cost" class="form-control" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn-add">Save Reward</button></div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="editRewardModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Reward</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_reward">
                    <input type="hidden" name="reward_id" id="edit_r_id">
                    <div class="form-group"><label>Reward Title</label><input type="text" name="title" id="edit_r_title" class="form-control" required></div>
                    <div class="form-group"><label>Cost (Points)</label><input type="number" name="cost" id="edit_r_cost" class="form-control" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description" id="edit_r_desc" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn-add">Update Reward</button></div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        function editUser(u) {
            $('#edit_u_id').val(u.id);
            $('#edit_u_name').val(u.name);
            $('#edit_u_email').val(u.email);
            $('#edit_u_role').val(u.role);
            $('#editUserModal').modal('show');
        }
        function editOrder(o) {
            $('#edit_o_id').val(o.id);
            $('#edit_o_total').val(o.total);
            $('#edit_o_status').val(o.status);
            $('#editOrderModal').modal('show');
        }
        function editProduct(p) {
            $('#edit_p_id').val(p.id);
            $('#edit_p_name').val(p.name);
            $('#edit_p_price').val(p.price);
            $('#edit_p_stock').val(p.stock);
            $('#edit_p_cat').val(p.category);
            $('#editProductModal').modal('show');
        }
        function editReward(r) {
            $('#edit_r_id').val(r.id);
            $('#edit_r_title').val(r.title);
            $('#edit_r_cost').val(r.cost);
            $('#edit_r_desc').val(r.description);
            $('#editRewardModal').modal('show');
        }
    </script>

</body>
</html>
