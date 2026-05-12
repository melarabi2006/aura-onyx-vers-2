<?php
require 'db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)");
        $stmt->execute([$user['id'], $token, $expires]);

        require_once 'send_mail.php';

        // Reset link – adjust if you use a different port
        $resetLink = "http://localhost/aura_onyx/reset_password.php?token=$token";
        $subject = "Aura & Onyx - Password Reset";
        $body = "
        <h2>Password Reset Request</h2>
        <p>Click the button below to reset your password. This link expires in 1 hour.</p>
        <a href='$resetLink' style='background:#892981;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Reset Password</a>
        <p>If you didn't request this, ignore this email.</p>
        ";

        if (sendMail($email, $subject, $body)) {
            $msg = "Reset link sent to your email.";
        } else {
            $msg = "Could not send email. Please try again later.";
        }
    } else {
        $msg = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Forgot Password</title>
<style>
    body { font-family:'Segoe UI',sans-serif; background:#f4f4f4; display:flex; align-items:center; justify-content:center; height:100vh; }
    form { background:white; padding:40px; border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,0.1); width:350px; }
    input { width:100%; padding:12px; margin:10px 0; border:1px solid #ddd; border-radius:5px; }
    button { background:#892981; color:white; border:none; padding:12px; width:100%; border-radius:5px; cursor:pointer; }
    .msg { margin-bottom:15px; color:green; }
</style>
</head>
<body>
    <form method="post">
        <h2 style="color:#892981;">Reset Password</h2>
        <?php if($msg): ?><p class="msg"><?= $msg ?></p><?php endif; ?>
        <input type="email" name="email" placeholder="Your registered email" required>
        <button type="submit">Send Reset Link</button>
        <p style="text-align:center;"><a href="login.php">Back to login</a></p>
    </form>
</body>
</html>