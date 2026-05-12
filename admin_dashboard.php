<?php
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'chief')) {
    header("Location: index.php"); exit();
}
require 'db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_product') {
        $name = trim($_POST['name']);
        $desc = trim($_POST['desc']);
        $price = $_POST['price'];
        $category = $_POST['category'];
        $img = trim($_POST['img']);
        $stock = intval($_POST['stock']);

        // Basic validation
        if (empty($name) || !is_numeric($price) || $price <= 0) {
            $msg = "Please provide a valid name and price.";
        } elseif (!in_array($category, ['jewelry','perfume','watches'])) {
            $msg = "Invalid category.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name,description,price,category,image_url,stock) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$name, $desc, $price, $category, $img, $stock]);
            $msg = "Product added.";
        }
    } elseif ($_POST['action'] === 'remove_product') {
        $product_id = intval($_POST['product_id']);
        if ($product_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $msg = $stmt->rowCount() ? "Product removed." : "Product not found.";
        } else {
            $msg = "Invalid product ID.";
        }
    } elseif ($_POST['action'] === 'remove_user') {
        $user_id = intval($_POST['user_id']);
        if ($user_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'chief'");
            $stmt->execute([$user_id]);
            $msg = $stmt->rowCount() ? "User removed." : "Cannot remove chief or user not found.";
        } else {
            $msg = "Invalid user ID.";
        }
    } elseif ($_POST['action'] === 'promote_admin' && $_SESSION['role'] === 'chief') {
        $user_id = intval($_POST['user_id']);
        if ($user_id > 0) {
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ? AND role = 'user'");
            $stmt->execute([$user_id]);
            $msg = $stmt->rowCount() ? "Promoted to admin." : "User not found or already admin.";
        } else {
            $msg = "Invalid user ID.";
        }
    } elseif ($_POST['action'] === 'revoke_admin' && $_SESSION['role'] === 'chief') {
        $user_id = intval($_POST['user_id']);
        if ($user_id > 0) {
            $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ? AND role = 'admin'");
            $stmt->execute([$user_id]);
            $msg = $stmt->rowCount() ? "Admin privileges revoked." : "User not admin.";
        } else {
            $msg = "Invalid user ID.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color:#222; padding:30px; }
        .card { background:white; padding:20px; margin:20px 0; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        input,select,textarea { width:100%; padding:8px; margin:10px 0; box-sizing:border-box; }
        button { background:#892981; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; }
        .gold { border:2px solid gold; }
    </style>
</head>
<body>
<h1>Admin Dashboard</h1>
<?php if($msg): ?><p style="color:green"><?= htmlspecialchars($msg) ?></p><?php endif; ?>

<div class="card">
    <h2>Add Product</h2>
    <form method="post">
        <input type="hidden" name="action" value="add_product">
        <input name="name" placeholder="Product Name" required>
        <textarea name="desc" placeholder="Description"></textarea>
        <input name="price" type="number" step="0.01" placeholder="Price" required>
        <select name="category">
            <option value="jewelry">Jewelry</option>
            <option value="perfume">Perfume</option>
            <option value="watches">Watches</option>
        </select>
        <input name="img" placeholder="Image filename (e.g., ring.jpg)">
        <input name="stock" type="number" value="0">
        <button type="submit">Add</button>
    </form>
</div>

<div class="card">
    <h2>Remove Product by ID</h2>
    <form method="post">
        <input type="hidden" name="action" value="remove_product">
        <input type="number" name="product_id" placeholder="Product ID" required>
        <button type="submit">Remove</button>
    </form>
</div>

<div class="card">
    <h2>Remove User Account (by ID)</h2>
    <form method="post">
        <input type="hidden" name="action" value="remove_user">
        <input type="number" name="user_id" placeholder="User ID" required>
        <button type="submit">Remove User</button>
    </form>
</div>

<?php if($_SESSION['role'] === 'chief'): ?>
<div class="card gold">
    <h2>Chief Controls</h2>
    <h3>Promote User to Admin</h3>
    <form method="post">
        <input type="hidden" name="action" value="promote_admin">
        <input type="number" name="user_id" placeholder="User ID">
        <button>Promote</button>
    </form>
    <h3>Revoke Admin Privileges</h3>
    <form method="post">
        <input type="hidden" name="action" value="revoke_admin">
        <input type="number" name="user_id" placeholder="Admin ID">
        <button>Revoke</button>
    </form>
</div>
<?php endif; ?>
</body>
</html>