<?php
// logout.php
session_start();
session_destroy();
echo "<script>
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('gofit_user_profile');
    localStorage.removeItem('gofit_last_order');
</script>";
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Gym Template">
    <meta name="keywords" content="Gym, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Logged Out | GoFit</title>

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
</head>

<body>
    <!-- Logout Section Begin -->
    <section class="auth-hero set-bg" data-setbg="img/breadcrumb/classes-breadcrumb.jpg" style="padding-top: 0;">
        <div class="auth-overlay"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="auth-card text-center">
                        <div class="auth-header">
                            <a href="index.html"><img src="img/logo.png" alt="GoFit" class="mb-4"
                                    style="max-height: 50px;"></a>
                            <h3>You have successfully logged out</h3>
                            <p>See you next time!</p>
                        </div>

                        <div class="mt-4">
                            <a href="login.php" class="btn-gradient w-100 mb-3">Login Here</a>
                            <a href="signup.php" class="btn-gradient w-100" style="background: transparent; border: 1px solid #f36100; color: #f36100;">Register Here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Logout Section End -->

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
