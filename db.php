<?php
$host = 'localhost'; 
$port = '3307'; // Explicitly state the port 
$db   = 'aura_onyx'; 
$user = 'aura_admin';     // The new VIP user
$pass = 'aura_password';  // The new VIP password

try {
    // We added the port directly into the connection string
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) { 
    die("Connection failed: " . $e->getMessage() . "<br><br><b>Hint:</b> Check if your XAMPP MySQL is running on a port other than 3307."); 
}
?>