<?php
// api/place_order.php
header('Content-Type: application/json');
require_once '../db_connect.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['user_id']) || !isset($data['items']) || !isset($data['total'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Order Data']);
    exit;
}

    $user_id = $data['user_id'];
    $items = $data['items'];
    $total = $data['total'];
    $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'Cash on Delivery';

    try {
        $pdo->beginTransaction();

        // 1. Create Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, payment_method, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $total, $payment_method]);
        $order_id = $pdo->lastInsertId();

    // 2. Insert Items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $stmtItem->execute([$order_id, $item['name'], $item['quantity'], $item['price']]);
    }

    $pdo->commit();

    // 4. Send Confirmation Email
    try {
        require_once '../email_helper.php';
        
        // Fetch User Details
        $stmtUser = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $stmtUser->execute([$user_id]);
        $user = $stmtUser->fetch();

        if ($user) {
            $userName = $user['name'];
            $userEmail = $user['email'];
            $subject = "Order Confirmation #{$order_id} - GoFit Club";
            
            $itemsHtml = "";
            foreach ($items as $item) {
                $itemsHtml .= "<li>" . htmlspecialchars($item['name']) . " x {$item['quantity']} - RM " . number_format($item['price'] * $item['quantity'], 2) . "</li>";
            }

            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px;'>
                    <div style='text-align: center; background: #151515; padding: 10px;'>
                        <h1 style='color: #f36100; margin: 0;'>GOFIT CLUB</h1>
                    </div>
                    <h2>Thank you for your order!</h2>
                    <p>Hi {$userName},</p>
                    <p>Your order #<strong>{$order_id}</strong> has been placed successfully.</p>
                    <hr>
                    <h3>Order Summary</h3>
                    <ul>{$itemsHtml}</ul>
                    <p><strong>Total Amount:</strong> RM " . number_format($total, 2) . "</p>
                    <p><strong>Payment Method:</strong> {$payment_method}</p>
                    <hr>
                    <p>We are processing your order and will notify you once it's shipped.</p>
                    <p>You can view your order status at any time in your profile.</p>
                    <p><a href='http://localhost/GoFit/customer.php' style='display: inline-block; padding: 10px 20px; background: #f36100; color: white; text-decoration: none; border-radius: 5px;'>View My Orders</a></p>
                    <br>
                    <p>Best Regards,<br>The GoFit Team</p>
                </div>
            ";

            sendEmail($userEmail, $subject, $body);
        }
    } catch (Exception $mailEx) {
        // Log mail error but don't fail the order response as DB is already committed
        error_log("Order Email failed: " . $mailEx->getMessage());
    }

    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
