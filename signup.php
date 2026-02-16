<?php
// signup.php
require_once 'db_connect.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered";
        } else {
            // Hash Password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert User
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password]);
                
                // Send Welcome Email
                require_once 'email_helper.php';
                $subject = "Welcome to GoFit, " . $name . "!";
                $body = "
                    <h1>Welcome to GoFit Club!</h1>
                    <p>Hi " . htmlspecialchars($name) . ",</p>
                    <p>Thank you for registering at GoFit. Your account has been successfully created.</p>
                    <p>You can now log in and start your fitness journey.</p>
                    <p><a href='http://localhost/GoFit/login.php' style='padding: 10px 20px; background: #f36103; color: white; text-decoration: none; border-radius: 5px;'>Log In Now</a></p>
                    <br>
                    <p>Best Regards,<br>The GoFit Team</p>
                ";
                
                sendEmail($email, $subject, $body);

                // Success
                header("Location: login.php?signup=success");
                exit;
            } catch (PDOException $e) {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
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
    <title>Sign Up | GoFit</title>

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
        Please open this URL instead: <a href="http://localhost/GoFit/signup.php" style="color: yellow; text-decoration: underline;">http://localhost/GoFit/signup.php</a>
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

    <!-- Sign Up Section Begin -->
    <section class="auth-hero set-bg" data-setbg="img/breadcrumb/classes-breadcrumb.jpg" style="padding-top: 0;">
        <div class="auth-overlay"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="auth-card">
                        <div class="auth-header text-center">
                            <a href="index.html"><img src="img/logo.png" alt="GoFit" class="mb-4"
                                    style="max-height: 50px;"></a>
                            <h3>Join the Club</h3>
                            <p>Start your transformation today</p>
                        </div>

                         <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form action="signup.php" method="POST" class="auth-form">
                            <div class="form-group">
                                <label for="fullname"><i class="fa fa-user"></i> Full Name</label>
                                <input type="text" id="fullname" name="fullname" class="form-control-styled"
                                    placeholder="Enter your full name" required>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fa fa-envelope-o"></i> Email Address</label>
                                <input type="email" id="email" name="email" class="form-control-styled"
                                    placeholder="Enter your email" required>
                            </div>
                            <div class="form-group">
                                <label for="password"><i class="fa fa-lock"></i> Password</label>
                                <input type="password" id="password" name="password" class="form-control-styled"
                                    placeholder="Create a password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm-password"><i class="fa fa-lock"></i> Confirm Password</label>
                                <input type="password" id="confirm-password" name="confirm_password"
                                    class="form-control-styled" placeholder="Confirm your password" required>
                            </div>

                            <button type="submit" class="btn-gradient w-100">Create Account</button>

                            <div class="text-center mt-4 auth-footer">
                                <p>Already have an account? <a href="login.php">Login Here</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Sign Up Section End -->

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
