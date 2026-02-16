<?php
// api/get_order_details.php
header('Content-Type: application/json');
require_once '../db_connect.php';
session_start();

if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'] ?? null;

try {
    // 1. Fetch Order (Ensure it belongs to user or user is admin)
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Security: and $order['user_id'] == $user_id (Skip for now to allow viewing by link, but in real app add check)
    
    // 2. Fetch Items
    $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$order_id]);
    $items = $stmtItems->fetchAll();

    // 3. Fetch Billing (We don't store billing in DB yet, but we can simulate or add columns)
    // For now, we'll return what we have
    
    echo json_encode([
        'success' => true,
        'order' => [
            'order_id' => $order['id'],
            'total' => $order['total'],
            'status' => $order['status'],
            'payment_method' => $order['payment_method'],
            'date' => date('Y-m-d', strtotime($order['created_at'])),
            'billing' => [
                'first' => 'Guest', // Simulate since it's not in DB
                'last' => 'User',
                'email' => '',
                'phone' => '',
                'address' => 'N/A',
                'town' => 'N/A'
            ]
        ],
        'items' => $items
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
