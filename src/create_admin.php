<?php
//create_admin.php -- run this only 1 time
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/src/models/Database.php';

$config = require BASE_PATH . '/config/DBConfig.php';
$db = new Database($config);
$pdo = $db->getConnection();

// CHANGE THESE BEFORE RUNNING:
$username = 'SupremeAdmin';
$password = 'AdminAccess123!';   // choose your admin password

// Hash the password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert into database
$sql = "INSERT INTO users (username, password) VALUES (:u, :p)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute(['u' => $username, 'p' => $hash]);
    echo "Admin user created successfully.<br>";
    echo "Username: $username<br>";
    echo "Password: $password<br>";
} catch (PDOException $e) {
    echo "Error creating admin user: " . $e->getMessage();
}
?>