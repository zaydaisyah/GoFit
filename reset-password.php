<?php
// reset-password.php
require_once 'db_connect.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$error = '';
$validToken = false;

if ($token) {
    // Validate Token
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->execute([$token]);
    if ($stmt->fetch()) {
        $validToken = true;
    } else {
        $error = "Invalid or expired reset link.";
    }
} else {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | GoFit</title>
    <link href="https://fonts.googleapis.com/css?family=Muli:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>

<body>
    <section class="auth-hero set-bg" data-setbg="img/breadcrumb/classes-breadcrumb.jpg" style="padding-top: 0;">
        <div class="auth-overlay"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="auth-card">
                        <div class="auth-header text-center">
                            <a href="index.html"><img src="img/logo.png" alt="GoFit" class="mb-4" style="max-height: 50px;"></a>
                            <h3>New Password</h3>
                            <p>Set a secure password for your account</p>
                        </div>

                        <div id="alert-container">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if ($validToken): ?>
                        <form id="resetForm" class="auth-form">
                            <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
                            <div class="form-group">
                                <label for="password"><i class="fa fa-lock"></i> New Password</label>
                                <input type="password" id="password" class="form-control-styled" placeholder="Enter new password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password"><i class="fa fa-lock"></i> Confirm Password</label>
                                <input type="password" id="confirm_password" class="form-control-styled" placeholder="Confirm new password" required>
                            </div>

                            <button type="submit" id="submitBtn" class="btn-gradient w-100">Reset Password</button>
                        </form>
                        <?php endif; ?>

                        <div class="text-center mt-4 auth-footer">
                            <a href="login.php">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        <?php if ($validToken): ?>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const token = document.getElementById('token').value;
            const btn = document.getElementById('submitBtn');
            const alertContainer = document.getElementById('alert-container');

            if (password !== confirm) {
                alertContainer.innerHTML = '<div class="alert alert-danger">Passwords do not match</div>';
                return;
            }

            btn.disabled = true;
            btn.innerText = "Processing...";

            fetch('api/reset_password_exec.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: token, password: password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alertContainer.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    document.getElementById('resetForm').style.display = 'none';
                    setTimeout(() => window.location.href = 'login.php', 3000);
                } else {
                    alertContainer.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    btn.disabled = false;
                    btn.innerText = "Reset Password";
                }
            })
            .catch(err => {
                alertContainer.innerHTML = `<div class="alert alert-danger">Network Error</div>`;
                btn.disabled = false;
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
