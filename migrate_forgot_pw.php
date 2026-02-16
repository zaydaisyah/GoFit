<?php
// migrate_forgot_pw.php
require_once 'db_connect.php';

try {
    // Check if columns exist
    $sql = "SHOW COLUMNS FROM users LIKE 'reset_token'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users 
                    ADD COLUMN reset_token VARCHAR(255) NULL AFTER joined_date,
                    ADD COLUMN reset_expiry DATETIME NULL AFTER reset_token");
        echo "<h2 style='color: green;'>Success: Columns 'reset_token' and 'reset_expiry' added to 'users' table.</h2>";
    } else {
        echo "<h2 style='color: orange;'>Notice: Columns already exist.</h2>";
    }

    echo "<p><a href='index.html'>Return to Home</a></p>";

} catch (PDOException $e) {
    die("<h2 style='color: red;'>Migration failed: " . $e->getMessage() . "</h2>");
}
?>
