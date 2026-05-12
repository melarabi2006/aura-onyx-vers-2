<?php
require 'db.php';
$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    // Strength validation (matches signup rules)
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $error = "Password must be 8+ characters with uppercase, lowercase, number, and symbol.";
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if ($reset) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $reset['user_id']]);
            $pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
            $success = true;
        } else {
            $error = "Invalid or expired token.";
        }
    }
} else {
    // Check if token exists and is valid
    $stmt = $pdo->prepare("SELECT id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    if (!$stmt->fetch()) {
        $error = "This reset link is invalid or has expired.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Set New Password</title>
<style>
    body { font-family:'Segoe UI',sans-serif; background:#f4f4f4; display:flex; align-items:center; justify-content:center; height:100vh; }
    .box { background:white; padding:40px; border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,0.1); width:350px; text-align:center; }
    input { width:100%; padding:12px; margin:10px 0; border:1px solid #ddd; border-radius:5px; }
    button { background:#892981; color:white; border:none; padding:12px; width:100%; border-radius:5px; cursor:pointer; }
    .error { color:red; }
    .success { color:green; }
</style>
</head>
<body>
<div class="box">
    <?php if($success): ?>
        <h2 style="color:#892981;">Password Updated!</h2>
        <p>You can now <a href="login.php">login</a>.</p>
    <?php else: ?>
        <h2 style="color:#892981;">Set New Password</h2>
        <?php if($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
        <form method="post">
            <input type="password" name="password" placeholder="New password" required>
            <button type="submit">Update Password</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>