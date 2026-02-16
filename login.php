<?php
// login.php
session_start();
require_once 'db_connect.php';

// Redirect if already logged in check removed to allow access
// if (isset($_SESSION['user_id'])) {
//     header("Location: index.html");
//     exit;
// }

$error = "";
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Updated query to fetch role
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Success
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_tier'] = $user['tier'];
        $_SESSION['role'] = isset($user['role']) ? $user['role'] : 'customer';
        
        $redirectUrl = ($_SESSION['role'] === 'admin') ? 'admin.php' : 'index.html';
        
        // Use Javascript to set localStorage before redirecting
        echo "
        <script>
            localStorage.setItem('isLoggedIn', 'true');
            localStorage.setItem('gofit_user_profile', JSON.stringify({
                id: " . json_encode($user['id']) . ",
                name: " . json_encode($user['name']) . ",
                email: " . json_encode($user['email']) . ",
                role: " . json_encode($_SESSION['role']) . ",
                tier: " . json_encode($user['tier']) . "
            }));
            window.location.href = '$redirectUrl';
        </script>";
        exit;
    } else {
        $error = "Invalid email or password.";
    }
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
    <title>Login | GoFit</title>

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
    </style>
</head>

<body>
    <div id="server-warning">
        ⚠️ STOP! You are opening this file incorrectly.<br>
        PHP files MUST be run from a server.<br>
        Please open this URL instead: <a href="http://localhost/GoFit/login.php" style="color: yellow; text-decoration: underline;">http://localhost/GoFit/login.php</a>
    </div>
    
    <script>
        if (window.location.protocol === "file:") {
            document.getElementById("server-warning").style.display = "block";
            // Optional: visual clue that PHP didn't run
            document.body.style.opacity = "0.5";
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

    <!-- Login Section Begin -->
    <section class="auth-hero set-bg" data-setbg="img/breadcrumb/classes-breadcrumb.jpg" style="padding-top: 0;">
        <div class="auth-overlay"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="auth-card">
                        <div class="auth-header text-center">
                            <a href="index.html"><img src="img/logo.png" alt="GoFit" class="mb-4"
                                    style="max-height: 50px;"></a>
                            <h3>Welcome Back</h3>
                            <p>Login to continue your fitness journey</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
                            <div class="alert alert-success" role="alert">
                                Registration successful! Please login.
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST" class="auth-form">
                            <div class="form-group">
                                <label for="email"><i class="fa fa-envelope-o"></i> Email Address</label>
                                <input type="email" id="email" name="email" class="form-control-styled"
                                    placeholder="Enter your email" required>
                            </div>
                            <div class="form-group">
                                <label for="password"><i class="fa fa-lock"></i> Password</label>
                                <input type="password" id="password" name="password" class="form-control-styled"
                                    placeholder="Enter your password" required>
                            </div>
                            <div class="form-check d-flex justify-content-between align-items-center mb-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="remember-me">
                                    <label class="custom-control-label" for="remember-me">Remember Me</label>
                                </div>
                                <a href="forgot-password.html" class="forgot-link">Forgot Password?</a>
                            </div>
                            <button type="submit" class="btn-gradient w-100">Login Now</button>

                            <div class="social-login text-center mt-4">
                                <span class="or-divider">OR</span>
                                <div class="social-btns mt-3">
                                    <a href="#" class="social-btn facebook"><i class="fa fa-facebook"></i></a>
                                    <a href="#" class="social-btn google"><i class="fa fa-google"></i></a>
                                </div>
                            </div>

                            <div class="text-center mt-4 auth-footer">
                                <p>Don't have an account? <a href="signup.php">Sign Up Free</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Login Section End -->

    <!-- Js Plugins -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/masonry.pkgd.min.js"></script>
    <script src="js/jquery.barfiller.js"></script>
    <script src="js/jquery.slicknav.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>
