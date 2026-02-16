<?php
// customer.php
session_start();
require_once 'db_connect.php';

// --- SECURE LOGIN CHECK ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$userData = [];

// Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete_reward') {
        $rewardId = $_POST['reward_id'];
        // Ensure the reward belongs to the user before deleting
        $stmt = $pdo->prepare("DELETE FROM user_rewards WHERE id = ? AND user_id = ?");
        $stmt->execute([$rewardId, $user_id]);
        
        // Redirect to avoid resubmission
        header("Location: customer.php");
        exit;
    }
}

try {
    // Fetch User Data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch();

    if (!$userData) {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    // Fetch Leaderboard (Top 10)
    $stmt = $pdo->query("SELECT name, points, tier, avatar FROM users ORDER BY points DESC LIMIT 10");
    $leaderboardData = $stmt->fetchAll();

    // Fetch Available Rewards
    $stmt = $pdo->query("SELECT * FROM rewards ORDER BY cost ASC");
    $rewardsData = $stmt->fetchAll();

    // Fetch User's Redeemed Rewards
    $stmt = $pdo->prepare("
        SELECT ur.id, r.title, r.description, ur.coupon_code, ur.redeemed_at 
        FROM user_rewards ur 
        JOIN rewards r ON ur.reward_id = r.id 
        WHERE ur.user_id = ? 
        ORDER BY ur.redeemed_at DESC
    ");
    $stmt->execute([$user_id]);
    $myRewards = $stmt->fetchAll();

    // Fetch User's Orders
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $userOrders = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Gym Template">
    <meta name="keywords" content="Gym, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>My Profile | GoFit</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Muli:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/flaticon.css" type="text/css">
    <link rel="stylesheet" href="css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="css/barfiller.css" type="text/css">
    <link rel="stylesheet" href="css/magnific-popup.css" type="text/css">
    <link rel="stylesheet" href="css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <script src="js/auth.js"></script>

    <link rel="stylesheet" href="css/style.css" type="text/css">

    <style>
        #server-warning {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #ff4d4d;
            color: white;
            padding: 20px;
            text-align: center;
            z-index: 99999;
            font-family: sans-serif;
            font-weight: bold;
        }

        /* --- Global & Background --- */
        body {
            background-color: #0b0b0b;
            background-image: radial-gradient(circle at 20% 40%, rgba(243, 97, 0, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(0, 123, 255, 0.05) 0%, transparent 40%);
            color: #e0e0e0;
        }

        /* --- Glassmorphism & Cards --- */
        .glass-panel {
            background: rgba(30, 30, 30, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            border-radius: 16px;
            padding: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-panel:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.4);
            border-color: rgba(243, 97, 0, 0.3);
        }

        /* --- Typography & Accents --- */
        h2,
        h3,
        h4,
        h5 {
            color: #ffffff;
            font-family: "Oswald", sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .text-neon {
            color: #f36100;
            text-shadow: 0 0 10px rgba(243, 97, 0, 0.3);
        }

        /* --- Avatar & Sidebar --- */
        .avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .avatar-glow {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #f36100;
            box-shadow: 0 0 20px rgba(243, 97, 0, 0.4);
            transition: all 0.3s ease;
        }

        .avatar-glow:hover {
            box-shadow: 0 0 30px rgba(243, 97, 0, 0.7);
        }

        .sidebar-info-label {
            color: #888;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 4px;
        }

        .sidebar-info-value {
            color: #ddd;
            font-size: 15px;
            font-weight: 500;
        }

        /* --- Stat Cards --- */
        .stat-card-icon {
            position: absolute;
            right: -10px;
            top: -10px;
            font-size: 100px;
            color: rgba(255, 255, 255, 0.03);
            transition: transform 0.5s ease;
        }

        .glass-panel:hover .stat-card-icon {
            transform: scale(1.1) rotate(10deg);
            color: rgba(243, 97, 0, 0.05);
        }

        /* --- Timeline (Recent Activity) --- */
        .timeline-container {
            position: relative;
            padding-left: 20px;
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 25px;
            border-left: 2px solid rgba(255, 255, 255, 0.1);
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: -6px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #f36100;
            box-shadow: 0 0 10px rgba(243, 97, 0, 0.5);
        }

        .timeline-date {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }

        .timeline-content {
            font-size: 15px;
            color: #eee;
        }

        .timeline-points {
            font-size: 13px;
            color: #f36100;
            font-weight: bold;
        }

        /* --- Rewards --- */
        .reward-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .reward-card:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: #f36100;
            transform: translateY(-5px);
        }
    </style>
    <!-- Placeholder Data for JS -->
    <script>
        window.serverData = {
            user: {
                id: <?php echo $userData['id']; ?>,
                name: "<?php echo htmlspecialchars($userData['name']); ?>",
                tier: "<?php echo htmlspecialchars($userData['tier']); ?>",
                points: <?php echo $userData['points']; ?>,
                streak: <?php echo $userData['streak']; ?>,
                email: "<?php echo htmlspecialchars($userData['email']); ?>",
                phone: "012-3456789", // Placeholder, or add to DB
                joined_date: "<?php echo $userData['joined_date']; ?>",
                avatar: "<?php echo htmlspecialchars($userData['avatar']); ?>"
            },
            rewards: [
                <?php foreach ($rewardsData as $reward): ?>
                    { 
                        id: <?php echo $reward['id']; ?>,
                        title: "<?php echo htmlspecialchars($reward['title']); ?>", 
                        cost: <?php echo $reward['cost']; ?>, 
                        desc: "<?php echo htmlspecialchars($reward['description']); ?>",
                        icon: "fa-gift" // Default icon, or add to DB
                    },
                <?php endforeach; ?>
            ],
            leaderboard: [
                <?php foreach ($leaderboardData as $user): ?>
                    { 
                        name: "<?php echo htmlspecialchars($user['name']); ?>", 
                        points: <?php echo $user['points']; ?>, 
                        tier: "<?php echo htmlspecialchars($user['tier']); ?>", 
                        avatar: "<?php echo htmlspecialchars($user['avatar']); ?>" 
                    },
                <?php endforeach; ?>
            ]
        };
    </script>
</head>

<body>
    <div id="server-warning">
        ⚠️ STOP! You are opening this file incorrectly.<br>
        PHP files MUST be run from a server.<br>
        Please open this URL instead: <a href="http://localhost/GoFit/customer.php" style="color: yellow; text-decoration: underline;">http://localhost/GoFit/customer.php</a>
    </div>

    <script>
        if (window.location.protocol === "file:") {
            document.getElementById("server-warning").style.display = "block";
        }
    </script>
    <!-- Offcanvas Menu Section Begin -->
    <div class="offcanvas-menu-overlay"></div>
    <div class="offcanvas-menu-wrapper">
        <div class="canvas-close">
            <i class="fa fa-close"></i>
        </div>
        <div class="canvas-search search-switch">
            <i class="fa fa-search"></i>
        </div>
        <nav class="canvas-menu mobile-menu">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="fitness_plans.html">Fitness Plan</a></li>
                <li><a href="timetable.html">Class Timetable</a></li>
                <li><a href="supplements.php">Shop With Us</a></li>
                <li><a href="contact.html">Contact Us</a></li>
                <li><a href="login.php" class="user-login">Login</a></li>
                <li><a href="logout.php" style="display: none;">Logout</a></li>
            </ul>
        </nav>
        <div id="mobile-menu-wrap"></div>
        <div class="canvas-social">
            <a href="#"><i class="fa fa-facebook"></i></a>
            <a href="#"><i class="fa fa-youtube-play"></i></a>
            <a href="#"><i class="fa fa-instagram"></i></a>
        </div>
    </div>
    <!-- Offcanvas Menu Section End -->

    <!-- Header Section Begin -->
    <header class="header-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3">
                    <div class="logo">
                        <a href="./index.html">
                            <img src="img/logo.png" alt="">
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <nav class="nav-menu">
                        <ul>
                            <li><a href="index.html">Home</a></li>
                            <li><a href="fitness_plans.html">Fitness Plan</a>
                                <ul class="dropdown">
                                    <li><a href="fitness_plans.html">Fitness Plans</a></li>
                                    <li><a href="booking.html">Book a Class</a></li>
                                </ul>
                            </li>
                            <li><a href="timetable.html">Class Timetable</a></li>
                            <li><a href="supplements.php">Shop With Us</a>
                                <ul class="dropdown">
                                    <li><a href="supplements.php">Supplements & Protein</a></li>
                                    <li><a href="merchandise.php">Merchandise</a></li>
                                </ul>
                            </li>
                            <li><a href="contact.html">Contact Us</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-lg-3">
                    <div class="top-option">
                        <div class="to-search search-switch">
                            <i class="fa fa-search"></i>
                        </div>
                        <div class="to-social">
                            <a href="cart.html" style="position: relative; margin-right: 15px;">
                                <i class="fa fa-shopping-cart"></i>
                                <span id="cart-count"
                                    style="display: none; position: absolute; top: -8px; right: -10px; background: #f36100; color: #fff; font-size: 10px; padding: 2px 5px; border-radius: 50%;">0</span>
                            </a>
                            <a href="customer.php" class="user-login"><i class="fa fa-user"></i></a>
                            <a href="logout.php" style="margin-left: 15px; color: #fff;"><i
                                    class="fa fa-sign-out"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="canvas-open">
                <i class="fa fa-bars"></i>
            </div>
        </div>
    </header>
    <!-- Header End -->

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-section set-bg" data-setbg="img/headers/header_profile_aesthetic.png">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb-text">
                        <h2>GoFit Pro Training</h2>
                        <div class="bt-option">
                            <a href="./index.html">Home</a>
                            <span>Pro Training</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Customer Section Begin -->
    <section class="contact-section spad">
        <div class="container">
            <div class="row">
                <!-- Left Sidebar: Identity Card -->
                <div class="col-lg-4 mb-4">
                    <div class="dashboard-sidebar glass-panel" style="position: sticky; top: 100px;">
                        <div class="text-center mb-4">
                            <div class="avatar-container">
                                <img src="img/team/team-1.jpg" id="profileImage" class="avatar-glow">
                                <label for="avatarUpload"
                                    style="position: absolute; bottom: 5px; right: 5px; background: #f36100; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #fff; border: 2px solid #1e1e1e;">
                                    <i class="fa fa-camera" style="font-size: 14px;"></i>
                                </label>
                                <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
                            </div>
                            <h3 style="margin-top: 10px; font-weight: 700;"><?php echo htmlspecialchars($userData['name']); ?></h3>
                            <p id="sidebar-rank-display" class="text-neon" style="font-weight: 600; letter-spacing: 2px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <?php echo strtoupper($userData['tier']); ?> Rank
                            </p>
                        </div>

                        <div class="info-list" style="margin-bottom: 30px;">
                            <div style="margin-bottom: 20px;">
                                <label class="sidebar-info-label">Email</label>
                                <div class="sidebar-info-value"><?php echo htmlspecialchars($userData['email']); ?></div>
                            </div>
                            <!-- Phone removed from DB for simplicity or add column back -->
                            <div>
                                <label class="sidebar-info-label">Member Since</label>
                                <div id="member-since" class="sidebar-info-value"><?php echo date('M Y', strtotime($userData['joined_date'])); ?></div>
                            </div>
                        </div>

                        <button class="primary-btn w-100" id="editProfileBtn"
                            style="border-radius: 8px; box-shadow: 0 4px 15px rgba(243,97,0,0.3);">Edit Details</button>
                        <a href="logout.php" class="btn w-100 mt-3"
                            style="background: transparent; border: 1px solid #333; color: #888; border-radius: 8px;">Logout</a>
                    </div>
                </div>

                <!-- Right Content: Performance Hub -->
                <div class="col-lg-8">
                    <!-- Notification Area -->
                    <div id="notification-area"></div>

                    <!-- Welcome & Recommendations -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 style="color: #fff; font-weight: 700;">Dashboard</h2>
                        <span style="color: #888; font-size: 14px;"><i class="fa fa-calendar"
                                style="color: #f36100; margin-right: 5px;"></i> Today's Focus</span>
                    </div>

                    <div id="recommendation-card"
                        style="background: linear-gradient(90deg, #1e1e1e, #151515); border-left: 5px solid #f36100; padding: 20px; border-radius: 10px; margin-bottom: 30px; display: flex; align-items: center;">
                        <div style="margin-right: 20px;">
                            <i class="fa fa-lightbulb-o" style="font-size: 30px; color: #f36100;"></i>
                        </div>
                        <div>
                            <h5 style="color: #fff; margin: 0 0 5px 0;">Daily Recommendation</h5>
                            <p style="color: #aaa; margin: 0; font-size: 14px;">Try a 30-minute HIIT session today to
                                boost your metabolism!</p>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="row mb-4">
                        <!-- Points Card -->
                        <div class="col-md-6 mb-3">
                            <div class="glass-panel" style="height: 100%; position: relative; overflow: hidden;">
                                <div class="stat-card-icon">
                                    <i class="fa fa-star"></i>
                                </div>
                                <h4 style="font-size: 14px; color: #aaa; margin-bottom: 15px;">
                                    Total Points <i class="fa fa-info-circle" style="cursor: pointer; color: #f36100;"
                                        data-toggle="modal" data-target="#pointsInfoModal"></i>
                                </h4>

                                    <div
                                        style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; margin-bottom: 15px;">
                                        <div>
                                            <span class="points-display text-neon"
                                                style="font-size: 42px; font-weight: 800;"><?php echo $userData['points']; ?></span>
                                            <span style="font-size: 14px; color: #f36100;">PTS</span>
                                            <span style="font-size: 14px; color: #f36100;">PTS</span>
                                        </div>
                                        
                                        <!-- Rank Card (Image + Badge) -->
                                        <div class="rank-card" style="display: flex; align-items: center; background: rgba(255,255,255,0.05); padding: 5px 12px; border-radius: 20px;">
                                            <img src="img/badges/rookie.png" alt="Rank" style="width: 25px; height: 25px; object-fit: contain; margin-right: 8px;">
                                            <div id="user-tier-badge"
                                                style="color: #f36100; font-weight: 700; font-size: 12px;">
                                                <?php echo strtoupper($userData['tier']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <div class="progress"
                                    style="height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px;">
                                    <div id="points-progress-bar" class="progress-bar" role="progressbar"
                                        style="width: 60%; background: #f36100; border-radius: 10px; box-shadow: 0 0 10px rgba(243,97,0,0.5);">
                                    </div>
                                </div>
                                <p id="next-rank-text"
                                    style="color: #666; font-size: 12px; margin-top: 10px; margin-bottom: 0;">
                                    Next: <span style="color: #f36100;">250 pts to Platinum</span>
                                </p>
                            </div>
                        </div>

                        <!-- Streak Card -->
                        <div class="col-md-6 mb-3">
                            <div class="glass-panel" style="height: 100%; position: relative; overflow: hidden;">
                                <div class="stat-card-icon">
                                    <i class="fa fa-fire"></i>
                                </div>
                                <h4 style="font-size: 14px; color: #aaa; margin-bottom: 15px;">Active Streak</h4>
                                <div style="margin-bottom: 10px;">
                                    <span id="streak-display" class="text-neon"
                                        style="font-size: 42px; font-weight: 800;"><?php echo $userData['streak']; ?></span>
                                    <span style="font-size: 16px; color: #f36100;">DAYS</span>
                                </div>
                                <p style="color: #888; font-size: 13px;">Keep the fire burning! <i class="fa fa-fire"
                                        style="color:#f36100;"></i></p>
                            </div>
                        </div>
                    </div>

                    <!-- Rank Banner (Visual) -->
                    <div
                        style="background: url('img/headers/header_timetable.png'); background-size: cover; background-position: center; border-radius: 15px; padding: 30px; position: relative; margin-bottom: 30px;">
                        <div
                            style="position: absolute; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.7); border-radius: 15px;">
                        </div>
                        <div
                            style="position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center;">
                                <img src="img/badges/gold.png" alt="Current Rank"
                                    style="width: 80px; margin-right: 20px;">
                                <div>
                                    <h4 style="color: #fff; font-size: 20px; font-weight: 700;">Current Rank: <span
                                            class="rank-badge-text">GOLD</span></h4>
                                    <p style="color: #ccc; margin: 0; font-size: 14px;">You are in the top 40% of
                                        members!</p>
                                </div>
                            </div>
                            <div class="text-right d-none d-md-block" style="cursor: pointer;" data-toggle="modal"
                                data-target="#leaderboardModal" title="Click to view full leaderboard">
                                <h2 id="leaderboard-rank"
                                    style="color: #f36100; font-weight: 800; font-size: 36px; margin: 0;">#3</h2>
                                <span style="color: #fff; font-size: 12px; text-transform: uppercase;">Leaderboard <i
                                        class="fa fa-external-link" style="margin-left:5px;"></i></span>
                            </div>
                        </div>
                    </div>

                    <!-- My Redeemed Rewards -->
                    <div class="rewards-section mb-4">
                         <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 style="color: #fff; font-weight: 700;">My Redeemed Rewards</h4>
                        </div>
                        <?php if (empty($myRewards)): ?>
                            <div class="glass-panel text-center" style="padding: 20px;">
                                <p style="color: #888; margin: 0;">You haven't redeemed any rewards yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($myRewards as $myReward): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="reward-card" style="border-left: 3px solid #f36100;">
                                        <div>
                                            <div class="d-flex justify-content-between">
                                                <h5 style="font-size:16px; color: #fff;"><?php echo htmlspecialchars($myReward['title']); ?></h5>
                                                <span style="color:#888; font-size: 11px;"><?php echo date('M d', strtotime($myReward['redeemed_at'])); ?></span>
                                            </div>
                                            <p style="color:#aaa; font-size:13px; margin:5px 0;"><?php echo htmlspecialchars($myReward['description']); ?></p>
                                            <div style="background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 4px; display: inline-block; margin-bottom: 10px;">
                                                <small style="color: #aaa; text-transform: uppercase;">Code:</small> 
                                                <strong style="color: #f36100; letter-spacing: 1px;"><?php echo htmlspecialchars($myReward['coupon_code']); ?></strong>
                                            </div>
                                        </div>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this reward?');">
                                            <input type="hidden" name="action" value="delete_reward">
                                            <input type="hidden" name="reward_id" value="<?php echo $myReward['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" style="border-color: #d9534f; color: #d9534f;">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Member Rewards Vault -->
                    <div class="rewards-section mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 style="color: #fff; font-weight: 700;">Member Rewards Vault</h4>
                            <a href="#" onclick="UserProfile.toggleRewardsView(); return false;"
                                id="view-all-rewards-btn" style="color: #f36100; font-size: 14px;">View
                                All Rewards <i class="fa fa-arrow-right"></i></a>
                        </div>
                        <div class="row" id="rewards-container">
                            <!-- Static Rewards (Simulated) -->
                            <div class="col-md-6 mb-3">
                                <div class="reward-card">
                                    <div class="d-flex justify-content-between">
                                        <h5 style="font-size:16px;">Free Shaker</h5>
                                        <span style="color:#f36100; font-weight:bold;">500 PTS</span>
                                    </div>
                                    <p style="color:#aaa; font-size:13px; margin:10px 0;">Get a free GoFit branded
                                        shaker bottle.</p>
                                    <button class="btn btn-sm btn-outline-light w-100">Redeem</button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="reward-card">
                                    <div class="d-flex justify-content-between">
                                        <h5 style="font-size:16px;">10% Off Merch</h5>
                                        <span style="color:#f36100; font-weight:bold;">1000 PTS</span>
                                    </div>
                                    <p style="color:#aaa; font-size:13px; margin:10px 0;">Get 10% discount on any store
                                        item.</p>
                                    <button class="btn btn-sm btn-outline-light w-100">Redeem</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order History -->
                    <div class="orders-section mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 style="color: #fff; font-weight: 700;">My Order History</h4>
                        </div>
                        <?php if (empty($userOrders)): ?>
                            <div class="glass-panel text-center" style="padding: 20px;">
                                <p style="color: #888; margin: 0;">You haven't placed any orders yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="glass-panel" style="padding: 0; overflow: hidden;">
                                <div class="table-responsive">
                                    <table class="table" style="color: #fff; margin-bottom: 0;">
                                        <thead style="background: rgba(255,255,255,0.05);">
                                            <tr>
                                                <th style="border: none; padding: 15px;">Order ID</th>
                                                <th style="border: none; padding: 15px;">Date</th>
                                                <th style="border: none; padding: 15px;">Payment</th>
                                                <th style="border: none; padding: 15px;">Total</th>
                                                <th style="border: none; padding: 15px;">Status</th>
                                                <th style="border: none; padding: 15px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userOrders as $order): ?>
                                            <tr style="border-top: 1px solid rgba(255,255,255,0.05);">
                                                <td style="border: none; padding: 15px; vertical-align: middle;">#<?php echo $order['id']; ?></td>
                                                <td style="border: none; padding: 15px; vertical-align: middle;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                <td style="border: none; padding: 15px; vertical-align: middle; color: #aaa; font-size: 13px;">
                                                    <?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?>
                                                </td>
                                                <td style="border: none; padding: 15px; vertical-align: middle; font-weight: 700; color: #f36100;">
                                                    RM <?php echo number_format($order['total'], 2); ?>
                                                </td>
                                                <td style="border: none; padding: 15px; vertical-align: middle;">
                                                    <span class="badge" style="background: <?php echo $order['status'] === 'Completed' ? '#28a745' : ($order['status'] === 'Pending' ? '#ffc107' : '#17a2b8'); ?>; color: #000;">
                                                        <?php echo $order['status']; ?>
                                                    </span>
                                                </td>
                                                <td style="border: none; padding: 15px; vertical-align: middle;">
                                                    <a href="success.html?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-light" style="font-size: 11px; border-radius: 4px;">Details</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Simulator & History with Timeline -->
                    <div class="row">
                        <div class="col-12">
                            <div class="glass-panel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 style="font-size: 20px;">Recent Activity</h4>
                                    <!-- Dev Tools -->
                                    <div>
                                        <button id="btn-sim-workout" class="btn btn-sm"
                                            style="background: #333; color: #fff; font-size: 11px; margin-right: 5px; border-radius: 20px;">+
                                            Workout</button>
                                        <button id="btn-sim-class" class="btn btn-sm"
                                            style="background: #333; color: #fff; font-size: 11px; border-radius: 20px;">+
                                            Class</button>
                                    </div>
                                </div>

                                <div class="timeline-container" id="points-timeline-body">
                                    <div class="timeline-item">
                                        <div class="timeline-date">Today, 10:00 AM</div>
                                        <div class="timeline-content">Completed "Morning Cardio" <span
                                                class="timeline-points">+50 PTS</span></div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-date">Yesterday, 6:30 PM</div>
                                        <div class="timeline-content">Attended "Yoga Flow" Class <span
                                                class="timeline-points">+100 PTS</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- Customer Section End -->

    <!-- Footer Section Begin -->
    <footer class="footer-section">
        <div class="container">
            <div class="row">
                <!-- Logo / About -->
                <div class="col-md-3 col-sm-6">
                    <div class="footer-about">
                        <a href="index.html"><img src="img/logo.png" alt="GoFit Logo" style="max-width:120px;"></a>
                        <p>Helping you stay fit, healthy, and confident with personalized training and support.</p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-md-3 col-sm-6">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="fitness_plans.html">Fitness Plans</a></li>
                        <li><a href="booking.html">Book Class</a></li>
                        <li><a href="timetable.html">Class Timetable</a></li>
                        <li><a href="faq.html">FAQ</a></li>
                    </ul>
                </div>

                <!-- Shop & Policies -->
                <div class="col-md-3 col-sm-6">
                    <h4>Shop & Policies</h4>
                    <ul>
                        <li><a href="supplements.php">Supplements & Protein</a></li>
                        <li><a href="merchandise.php">Merchandise</a></li>
                        <li><a href="terms.html">Terms & Conditions</a></li>
                        <li><a href="privacy.html">Privacy Policy</a></li>
                        <li><a href="refund.html">Return & Refund Policy</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-md-3 col-sm-6">
                    <h4>Contact</h4>
                    <p><a href="https://maps.google.com/maps?q=No.+20,+Jalan+51b/225,+Seksyen+51a,+46100+Petaling+Jaya,+Selangor"
                            target="_blank" style="color: #c4c4c4;">No. 20, Jalan 51b/225, Seksyen 51a, 46100 Petaling
                            Jaya, Selangor</a></p>
                    <p>
                        <a href="tel:01123456789" style="color: #c4c4c4;">011-23456789</a> |
                        <a href="tel:01223456789" style="color: #c4c4c4;">012-23456789</a>
                    </p>
                    <p><a href="mailto:support.GoFit@gmail.com" style="color: #c4c4c4;">support.GoFit@gmail.com</a></p>
                    <div class="footer-social">
                        <a href="https://www.facebook.com/"><i class="fa fa-facebook"></i></a>
                        <a href="https://www.youtube.com/"><i class="fa fa-youtube-play"></i></a>
                        <a href="https://www.instagram.com/accounts/login/?hl=en"><i class="fa fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p>&copy;
                        <script>document.write(new Date().getFullYear());</script> GoFit. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Section End -->

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="background: #1e1e1e; color: #fff; border: 1px solid #333;">
                <div class="modal-header" style="border-bottom: 1px solid #333;">
                    <h5 class="modal-title" id="editProfileModalLabel" style="color: #f36100; font-weight: 700;">Edit
                        Profile Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm">
                        <div class="form-group">
                            <label for="modalUserName" style="color: #aaa;">Full Name</label>
                            <input type="text" class="form-control" id="modalUserName"
                                style="background: #333; border: 1px solid #444; color: #fff;" value="Alex Fitness">
                        </div>
                        <div class="form-group">
                            <label for="modalUserEmail" style="color: #aaa;">Email Address</label>
                            <input type="email" class="form-control" id="modalUserEmail"
                                style="background: #333; border: 1px solid #444; color: #fff;" value="alex@example.com">
                        </div>
                        <div class="form-group">
                            <label for="modalUserPhone" style="color: #aaa;">Phone Number</label>
                            <input type="text" class="form-control" id="modalUserPhone"
                                style="background: #333; border: 1px solid #444; color: #fff;" value="012-3456789">
                        </div>
                        <div class="form-group">
                            <label for="modalAvatarUrl" style="color: #aaa;">Avatar URL (Optional)</label>
                            <input type="text" class="form-control" id="modalAvatarUrl" placeholder="Enter image URL"
                                style="background: #333; border: 1px solid #444; color: #fff;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #333;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn primary-btn" id="modalSaveBtn"
                        style="background: #f36100; color: #fff; border: none;">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Points Info Modal -->
    <div class="modal fade" id="pointsInfoModal" tabindex="-1" role="dialog" aria-labelledby="pointsInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="background: #1e1e1e; color: #fff; border: 1px solid #333;">
                <div class="modal-header" style="border-bottom: 1px solid #333;">
                    <h5 class="modal-title" id="pointsInfoModalLabel" style="color: #f36100; font-weight: 700;"><i
                            class="fa fa-star"></i> Why Collect Points?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p style="color:#ccc;">Gamify your fitness journey! Points are your currency for success.</p>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 15px;">
                            <h6 style="color: #fff; font-weight: bold;"><i class="fa fa-trophy"
                                    style="color: #f36100; width: 20px;"></i> Rank Up</h6>
                            <p style="font-size: 13px; color: #888; margin: 0;">Climb from Rookie to Legend. Higher
                                ranks unlock elite status and respect.</p>
                        </li>
                        <li style="margin-bottom: 15px;">
                            <h6 style="color: #fff; font-weight: bold;"><i class="fa fa-gift"
                                    style="color: #f36100; width: 20px;"></i> Redeem Rewards</h6>
                            <p style="font-size: 13px; color: #888; margin: 0;">Use points to claim discounts on gym
                                merch, supplements, and free classes.</p>
                        </li>
                        <li style="margin-bottom: 15px;">
                            <h6 style="color: #fff; font-weight: bold;"><i class="fa fa-unlock-alt"
                                    style="color: #f36100; width: 20px;"></i> Exclusive Access</h6>
                            <p style="font-size: 13px; color: #888; margin: 0;">Unlock special features, expert training
                                plans, and VIP events.</p>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #333;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Got it!</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Leaderboard Modal -->
    <div class="modal fade" id="leaderboardModal" tabindex="-1" role="dialog" aria-labelledby="leaderboardModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content" style="background: #1e1e1e; color: #fff; border: 1px solid #333;">
                <div class="modal-header" style="border-bottom: 1px solid #333;">
                    <h5 class="modal-title" id="leaderboardModalLabel" style="color: #f36100; font-weight: 700;"><i
                            class="fa fa-trophy"></i> Global Leaderboard</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <!-- Added CSS class to style via CSS if needed, but using inline for safety now -->
                        <table class="table table-dark table-striped mb-0" id="leaderboard-table"
                            style="background: transparent;">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center" style="border-top:none;">Rank</th>
                                    <th scope="col" style="border-top:none;">Member</th>
                                    <th scope="col" style="border-top:none;">Tier</th>
                                    <th scope="col" class="text-right" style="border-top:none;">Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class='text-center'><i class="fa fa-trophy" style="color:#FFD700"></i></td>
                                    <td><img src='img/team/team-2.jpg'
                                            style='width:30px;height:30px;border-radius:50%;margin-right:10px;'> Sarah
                                        Connor</td>
                                    <td>Legend</td>
                                    <td class='text-right'>2500</td>
                                </tr>
                                <tr>
                                    <td class='text-center'><i class="fa fa-trophy" style="color:#C0C0C0"></i></td>
                                    <td><img src='img/team/team-3.jpg'
                                            style='width:30px;height:30px;border-radius:50%;margin-right:10px;'> John
                                        Wick</td>
                                    <td>Elite</td>
                                    <td class='text-right'>2100</td>
                                </tr>
                                <tr style="background: rgba(243, 97, 0, 0.2); border-left: 3px solid #f36100;">
                                    <td class='text-center'><i class="fa fa-trophy" style="color:#CD7F32"></i></td>
                                    <td><img src='img/team/team-1.jpg'
                                            style='width:30px;height:30px;border-radius:50%;margin-right:10px;'> Alex
                                        Fitness</td>
                                    <td>Gold</td>
                                    <td class='text-right'>1250</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #333;">
                    <p class="mr-auto mb-0" style="color:#888; font-size:12px;">Rankings update in real-time.</p>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search model Begin -->
    <div class="search-model">
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="search-close-switch">+</div>
            <form class="search-model-form">
                <input type="text" id="search-input" placeholder="Search here.....">
            </form>
        </div>
    </div>
    <!-- Search model end -->

    <!-- Js Plugins -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/masonry.pkgd.min.js"></script>
    <script src="js/jquery.barfiller.js"></script>
    <script src="js/jquery.slicknav.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/cart.js"></script>
    <script src="js/profile.js"></script>
</body>

</html>