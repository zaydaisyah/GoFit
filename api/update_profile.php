<?php
// api/update_profile.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$name = isset($data['name']) ? trim($data['name']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : ''; // Not in DB yet, but receiving it
$avatar_base64 = isset($data['avatar']) ? $data['avatar'] : null;

try {
    // 1. Handle Avatar Upload (if provided and is base64)
    $avatarPath = null;
    if ($avatar_base64 && strpos($avatar_base64, 'data:image') === 0) {
        
        // Extract base64 data
        list($type, $dataStr) = explode(';', $avatar_base64);
        list(, $dataStr)      = explode(',', $dataStr);
        $imageData = base64_decode($dataStr);

        if ($imageData === false) {
             throw new Exception("Failed to decode image.");
        }

        // Generate Filename
        $uploadDir = '../img/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Use png for simplicity
        $fileName = 'avatar_' . $user_id . '_' . time() . '.png';
        $filePath = $uploadDir . $fileName;

        // Save File
        if (file_put_contents($filePath, $imageData)) {
            $avatarPath = 'img/uploads/' . $fileName; // Path relative to web root
        } else {
            throw new Exception("Failed to write image file.");
        }
    }

    // 2. Update Database
    $sql = "UPDATE users SET name = ?, email = ?";
    $params = [$name, $email];

    if ($avatarPath) {
        $sql .= ", avatar = ?";
        $params[] = $avatarPath;
    }

    $sql .= " WHERE id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 3. Response
    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully',
        'new_avatar' => $avatarPath // Return new path so JS can update local state
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
