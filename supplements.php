<?php
// supplements.php
session_start();
require_once 'db_connect.php';

try {
    // Fetch Supplements products
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'Supplements' ORDER BY name");
    $stmt->execute();
    $products = $stmt->fetchAll();
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
    <title>Supplements | GoFit</title>

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
    <style>
        .out-of-stock {
            opacity: 0.6;
            pointer-events: none;
        }
        .stock-label {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }
        .stock-low {
            color: #ff4d4d;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

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
                            <li class="active"><a href="supplements.php">Shop With Us</a>
                                <ul class="dropdown">
                                    <li class="active"><a href="supplements.php">Supplements & Protein</a></li>
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
                                <span id="cart-count" style="display: none; position: absolute; top: -8px; right: -10px; background: #f36100; color: #fff; font-size: 10px; padding: 2px 5px; border-radius: 50%;">0</span>
                            </a>
                            <a href="customer.php" class="user-login"><i class="fa fa-user"></i></a>
                            <a href="logout.php" style="margin-left: 15px; color: #fff;"><i class="fa fa-sign-out"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="canvas-open">
                <i class="fa fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-section set-bg" data-setbg="img/breadcrumb/supplements-bg.png">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb-text">
                        <h2>Supplements</h2>
                        <div class="bt-option">
                            <a href="./index.html">Home</a>
                            <a href="#">Shop</a>
                            <span>Supplements</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Supplements Section Begin -->
    <section class="merchandise-section spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <span>GoFit</span>
                        <h2>SUPPLEMENTS</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="product-item <?php echo $product['stock'] <= 0 ? 'out-of-stock' : ''; ?>">
                            <div class="pi-pic">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="merch-img">
                            </div>
                            <div class="pi-text">
                                <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                <div class="stock-label">
                                    <?php if ($product['stock'] <= 0): ?>
                                        <span class="stock-low">OUT OF STOCK</span>
                                    <?php elseif ($product['stock'] <= 10): ?>
                                        <span class="stock-low">Only <?php echo $product['stock']; ?> left!</span>
                                    <?php else: ?>
                                        <span>In Stock: <?php echo $product['stock']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
                                <?php if ($product['stock'] > 0): ?>
                                    <a href="#" class="primary-btn add-to-cart-btn" 
                                       data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                       data-price="<?php echo $product['price']; ?>"
                                       data-image="<?php echo htmlspecialchars($product['image']); ?>">Add to Cart</a>
                                <?php else: ?>
                                    <a href="#" class="primary-btn disabled">Sold Out</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer Section Begin -->
    <footer class="footer-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="footer-about">
                        <a href="index.html"><img src="img/logo.png" alt=""></a>
                        <p>Helping you stay fit, healthy, and confident with personalized training and support.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="fitness_plans.html">Fitness Plans</a></li>
                        <li><a href="booking.html">Book Class</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h4>Shop</h4>
                    <ul>
                        <li><a href="supplements.php">Supplements</a></li>
                        <li><a href="merchandise.php">Merchandise</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h4>Contact</h4>
                    <p>No. 20, Jalan 51b/225, Petaling Jaya, Selangor</p>
                    <p>011-23456789</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Js Plugins -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/cart.js"></script>

    <script>
        $(document).ready(function () {
            $('.add-to-cart-btn').on('click', function (e) {
                e.preventDefault();
                const name = $(this).data('name');
                const price = parseFloat($(this).data('price'));
                const image = $(this).data('image');
                
                Cart.addItem({
                    id: name.replace(/\s+/g, '-').toLowerCase(),
                    name: name,
                    price: price,
                    image: image
                });
                alert(name + " added to cart!");
            });
        });
    </script>
</body>
</html>
