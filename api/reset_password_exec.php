<?php
// api/reset_password_exec.php
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['token']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Token and password are required']);
    exit;
}

$token = $data['token'];
$password = $data['password'];

try {
    // 1. Verify Token
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired token.']);
        exit;
    }

    // 2. Hash and Update
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $updateStmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
    $updateStmt->execute([$hashed, $user['id']]);

    echo json_encode(['success' => true, 'message' => 'Password reset successful! Redirecting to login...']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
