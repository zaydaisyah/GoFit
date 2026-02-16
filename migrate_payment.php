<?php
// migrate_payment.php
require_once 'db_connect.php';

try {
    // Check if column exists
    $sql = "SHOW COLUMNS FROM orders LIKE 'payment_method'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(100) DEFAULT 'Cash on Delivery' AFTER total");
        echo "<h2 style='color: green;'>Success: Column 'payment_method' added to 'orders' table.</h2>";
    } else {
        echo "<h2 style='color: orange;'>Notice: Column 'payment_method' already exists.</h2>";
    }

    echo "<p><a href='index.html'>Return to Home</a></p>";

} catch (PDOException $e) {
    die("<h2 style='color: red;'>Migration failed: " . $e->getMessage() . "</h2>");
}
?>
