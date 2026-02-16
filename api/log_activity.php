<?php
// api/log_activity.php
header('Content-Type: application/json');
require_once '../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['action']) || !isset($data['points'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Input']);
    exit;
}

$user_id = $data['user_id'];
$action = $data['action'];
$points = intval($data['points']);

try {
    $pdo->beginTransaction();

    // 1. Insert into Activity Log
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity, points_earned) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $points]);

    // 2. Update User Points
    $stmt = $pdo->prepare("UPDATE users SET points = points + ?, streak = streak + 1 WHERE id = ?");
    $stmt->execute([$points, $user_id]);

    $pdo->commit();

    // Return new points balance
    $stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $newPoints = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'new_points' => $newPoints]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
