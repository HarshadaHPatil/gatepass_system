<?php
/* config/db.php
   One reusable PDO connection.
   Adjust $dbUser / $dbPass if your XAMPP MySQL credentials differ. */

$dbHost = 'localhost';
$dbName = 'gatepass_system';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    exit('Connection failed: ' . $e->getMessage());
}
?>