<?php
// api/redeem_reward.php
header('Content-Type: application/json');
require_once '../db_connect.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['reward_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$user_id = $data['user_id'];
$reward_id = $data['reward_id'];

try {
    $pdo->beginTransaction();

    // 1. Get User Points
    $stmt = $pdo->prepare("SELECT points FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("User not found");
    }

    // 2. Get Reward Cost
    $stmt = $pdo->prepare("SELECT cost, title FROM rewards WHERE id = ?");
    $stmt->execute([$reward_id]);
    $reward = $stmt->fetch();

    if (!$reward) {
        throw new Exception("Reward not found");
    }

    // 3. Check Balance
    if ($user['points'] < $reward['cost']) {
        throw new Exception("Insufficient points");
    }

    // 4. Deduct Points
    $new_points = $user['points'] - $reward['cost'];
    $stmt = $pdo->prepare("UPDATE users SET points = ? WHERE id = ?");
    $stmt->execute([$new_points, $user_id]);

    // 5. Generate Coupon
    $coupon = 'GO-' . strtoupper(substr(md5(uniqid()), 0, 8));

    // 6. Record Redemption
    $stmt = $pdo->prepare("INSERT INTO user_rewards (user_id, reward_id, coupon_code) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $reward_id, $coupon]);

    // 7. Log Activity
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity, points_earned) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, "Redeemed Reward: " . $reward['title'], -$reward['cost']]);

    $pdo->commit();

    echo json_encode(['success' => true, 'new_points' => $new_points, 'coupon' => $coupon]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>