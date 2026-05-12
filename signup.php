<?php
session_start();
require 'db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Check duplicate email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $error = "This email is already registered.";
    } else {
        // 2. Strong Password Validation (8+ chars, Upper, Lower, Number, Special)
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
        if (!preg_match($pattern, $password)) {
            $error = "Password must be 8+ chars with uppercase, lowercase, numbers, and symbols.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $token = bin2hex(random_bytes(32));

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, activation_token) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $token])) {
                require_once 'send_mail.php';

                // Activation link – adjust if you use a different port
                $activate_link = "http://localhost/aura_onyx/activate.php?token=$token";
                $subject = "Activate your Aura & Onyx account";
                $body = "
                <h2>Welcome to Aura & Onyx!</h2>
                <p>Please confirm your email address by clicking the button below.</p>
                <a href='$activate_link' style='background:#892981;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Activate Account</a>
                ";

                sendMail($email, $subject, $body);
                echo "<script>alert('Account created! Please check your email to activate.'); window.location='login.php';</script>";
                exit;
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | Aura & Onyx</title>
    <style>
        body {
            background: #f4f4f4;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            width: 350px;
        }
        h2 {
            color: #892981;
            text-align: center;
            margin-bottom: 30px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            background: #892981;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #6b1f65;
        }
        .error {
            color: red;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 15px;
        }
        .links {
            text-align: center;
            margin-top: 15px;
        }
        .links a {
            color: #892981;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create Account</h2>
        <?php if($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Strong Password" required>
            <button type="submit">REGISTER</button>
        </form>
        <div class="links">
            <p><a href="login.php">Already have an account? Login</a></p>
        </div>
    </div>
</body>
</html>