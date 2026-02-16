<?php
// api/check_session.php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'isLoggedIn' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['role'],
            'tier' => $_SESSION['user_tier']
        ]
    ]);
} else {
    echo json_encode(['isLoggedIn' => false]);
}
