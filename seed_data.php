<?php
// seed_data.php
require_once 'db_connect.php';

// Set execution time limit to 0 (no limit) as this might take a bit
set_time_limit(0);

echo "Starting data seeding...\n";

try {
    // 1. Get existing product names for order items
    $stmt = $pdo->query("SELECT name, price FROM products");
    $allProducts = $stmt->fetchAll();
    if (empty($allProducts)) {
        die("Error: No products found. Please import setup.sql first.\n");
    }

    // 2. Clear existing dynamic data (Optional, but recommended for clean seed)
    // $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    // $pdo->exec("TRUNCATE TABLE order_items;");
    // $pdo->exec("TRUNCATE TABLE orders;");
    // $pdo->exec("DELETE FROM users WHERE role = 'customer' AND email LIKE 'user%@gofit.com';");
    // $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 3. Generate 1000 Users
    $userInsertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role, tier, points, avatar) VALUES (?, ?, ?, 'customer', ?, ?, ?)");
    $tiers = ['Bronze', 'Silver', 'Gold', 'Platinum'];
    $avatars = ['img/team/team-1.jpg', 'img/team/team-2.jpg', 'img/team/team-3.jpg'];
    
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    echo "Seeding users...\n";
    for ($i = 1; $i <= 1000; $i++) {
        $name = "Customer " . $i;
        $email = "user" . $i . "@gofit.com";
        $tier = $tiers[array_rand($tiers)];
        $points = rand(0, 10000);
        $avatar = $avatars[array_rand($avatars)];
        
        $userInsertStmt->execute([$name, $email, $password, $tier, $points, $avatar]);
    }
    echo "1000 Users seeded.\n";

    // 4. Get all customer IDs
    $customerIds = $pdo->query("SELECT id FROM users WHERE role = 'customer'")->fetchAll(PDO::FETCH_COLUMN);

    // 5. Generate 1000 Orders
    $orderInsertStmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, ?, ?)");
    $orderItemInsertStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)");
    $statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];

    echo "Seeding orders...\n";
    for ($j = 1; $j <= 1000; $j++) {
        $userId = $customerIds[array_rand($customerIds)];
        $status = $statuses[array_rand($statuses)];
        $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(0, 365) . ' days -' . rand(0, 23) . ' hours'));
        
        // Randomly pick 1-3 products for the order
        $numItems = rand(1, 3);
        $total = 0;
        $items = [];
        
        for ($k = 0; $k < $numItems; $k++) {
            $prod = $allProducts[array_rand($allProducts)];
            $qty = rand(1, 2);
            $price = $prod['price'];
            $total += $price * $qty;
            $items[] = ['name' => $prod['name'], 'qty' => $qty, 'price' => $price];
        }

        $orderInsertStmt->execute([$userId, $total, $status, $createdAt]);
        $orderId = $pdo->lastInsertId();

        foreach ($items as $item) {
            $orderItemInsertStmt->execute([$orderId, $item['name'], $item['qty'], $item['price']]);
        }
    }
    echo "1000 Orders seeded.\n";

    echo "Seeding completed successfully!\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
}
?>
