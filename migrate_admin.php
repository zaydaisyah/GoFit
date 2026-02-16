<?php
// migrate_admin.php
require_once 'db_connect.php';

try {
    // 1. Add role column if it doesn't exist
    $sql = "SHOW COLUMNS FROM users LIKE 'role'";
    $stmt = $pdo->query($sql);
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('customer', 'admin') DEFAULT 'customer' AFTER password");
        echo "Column 'role' added successfully.<br>";
    } else {
        echo "Column 'role' already exists.<br>";
    }

    // 2. Check if Admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@gofit.com']);
    
    if (!$stmt->fetch()) {
        // Create Admin (Password: admin123)
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, role, tier, points, avatar) VALUES (?, ?, ?, 'admin', 'Bronze', 0, 'img/team/team-1.jpg')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['Admin User', 'admin@gofit.com', $password]);
        echo "Admin user created (Email: admin@gofit.com, Pass: admin123).<br>";
    } else {
        // Ensure role is admin
        $pdo->exec("UPDATE users SET role = 'admin' WHERE email = 'admin@gofit.com'");
        echo "Admin user already exists. Role ensured.<br>";
    }

    echo "Migration completed.";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
