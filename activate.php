<?php
require 'db.php';
$token = $_GET['token'] ?? '';

if ($token) {
    $stmt = $pdo->prepare("UPDATE users SET is_active = 1, activation_token = NULL WHERE activation_token = ? AND is_active = 0");
    $stmt->execute([$token]);
    $msg = $stmt->rowCount() ? "Account activated! You can now <a href='login.php'>login</a>." : "Invalid or expired token.";
} else {
    $msg = "No token provided.";
}
?>
<!DOCTYPE html>
<html>
<head><title>Account Activation</title>
<style>
    body { font-family:'Segoe UI',sans-serif; background:#f4f4f4; text-align:center; padding-top:100px; }
    .box { background:white; display:inline-block; padding:40px; border-radius:15px; }
    a { color:#892981; }
</style>
</head>
<body>
    <div class="box">
        <h2><?= $msg ?></h2>
        <p><a href="index.php">Go to Homepage</a></p>
    </div>
</body>
</html>