<?php
// api/forgot_password_req.php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../email_helper.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

$email = trim($data['email']);

try {
    // 1. Check if user exists
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // For security, don't reveal if email exists or not
        echo json_encode(['success' => true, 'message' => 'If this email is registered, you will receive a reset link shortly.']);
        exit;
    }

    // 2. Generate Token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // 3. Update User
    $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
    $updateStmt->execute([$token, $expiry, $user['id']]);

    // 4. Send Email
    $resetLink = "http://localhost/GoFit/reset-password.php?token=" . $token;
    $subject = "Password Reset Request - GoFit Club";
    $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px;'>
            <div style='text-align: center; background: #151515; padding: 10px;'>
                <h1 style='color: #f36100; margin: 0;'>GOFIT CLUB</h1>
            </div>
            <h2>Reset Your Password</h2>
            <p>Hi " . htmlspecialchars($user['name']) . ",</p>
            <p>We received a request to reset your password. Click the button below to set a new password. This link will expire in 1 hour.</p>
            <p style='text-align: center;'>
                <a href='{$resetLink}' style='display: inline-block; padding: 12px 25px; background: #f36100; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
            </p>
            <p>If you didn't request this, you can safely ignore this email.</p>
            <p>Alternatively, copy and paste this link into your browser:</p>
            <p style='font-size: 12px; color: #666;'>{$resetLink}</p>
            <br>
            <p>Best Regards,<br>The GoFit Team</p>
        </div>
    ";

    if (sendEmail($email, $subject, $body)) {
        echo json_encode(['success' => true, 'message' => 'If this email is registered, you will receive a reset link shortly.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send reset email. Please try again later.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
