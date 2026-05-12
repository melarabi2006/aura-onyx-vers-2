<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$saved = null;
if ($user_id) {
    $stmt = $pdo->prepare("SELECT saved_address, saved_city, saved_postal FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $saved = $stmt->fetch();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postal = $_POST['postal'];
    $payment = $_POST['payment'];
    $total = $_POST['total_price'];
    $save_info = isset($_POST['save_info']) ? 1 : 0;

    // Get cart items from hidden field (JSON)
    $items = json_decode($_POST['cart_items'], true) ?? [];

    // 1. Insert Order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, shipping_address, payment_method) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $total, "$address, $city, $postal", $payment]);
    $order_id = $pdo->lastInsertId();

    // 2. Insert order items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmtItem->execute([$order_id, $item['id'], $item['qty'], $item['price']]);
    }

    // 3. Save address info for next time
    if ($save_info && $user_id) {
        $stmt = $pdo->prepare("UPDATE users SET saved_address=?, saved_city=?, saved_postal=? WHERE id=?");
        $stmt->execute([$address, $city, $postal, $user_id]);
    }

    // 4. Fetch order items for receipt display
    $orderItems = $pdo->prepare("
        SELECT p.name, oi.quantity, oi.price_at_time 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $orderItems->execute([$order_id]);
    $itemsForReceipt = $orderItems->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Order Confirmed | Aura & Onyx</title>
        <style>
            body {
                font-family: 'Segoe UI', sans-serif;
                background: #f4f4f4;
                margin: 0;
                padding: 40px;
            }
            .receipt {
                max-width: 600px;
                margin: auto;
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            }
            .receipt h1 {
                color: #892981;
                margin-top: 0;
            }
            .receipt h3 {
                color: #333;
                margin-bottom: 10px;
            }
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .items-table th, .items-table td {
                border-bottom: 1px solid #eee;
                padding: 12px 8px;
                text-align: left;
            }
            .items-table th {
                background: #892981;
                color: white;
            }
            .total {
                font-size: 1.5rem;
                font-weight: bold;
                text-align: right;
                margin-top: 20px;
            }
            .btn-print {
                background: #892981;
                color: white;
                border: none;
                padding: 12px 25px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                transition: background 0.3s;
            }
            .btn-print:hover {
                background: #6b1f65;
            }
            .btn-continue {
                color: #892981;
                text-decoration: none;
                margin-left: 20px;
                font-weight: bold;
            }
            @media print {
                body { background: white; }
                .no-print { display: none; }
                .receipt { box-shadow: none; padding: 20px; }
            }
        </style>
    </head>
    <body>
        <div class="receipt">
            <h1>Order #<?= $order_id ?> – Confirmed</h1>
            <p><strong>Date:</strong> <?= date('F j, Y') ?></p>
            <h3>Shipping to</h3>
            <p><?= htmlspecialchars("$address, $city, $postal") ?></p>
            <h3>Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($itemsForReceipt as $item): 
                        $subtotal = $item['quantity'] * $item['price_at_time'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['price_at_time'], 2) ?></td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="total">Total: $<?= number_format($total, 2) ?></p>
            <button class="btn-print no-print" onclick="window.print()">🖨 Print Receipt</button>
            <a href="index.php" class="btn-continue no-print">← Continue Shopping</a>
        </div>
        <script>
            // Clear the cart after successful order
            localStorage.removeItem('cart');
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Aura & Onyx</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f4f4;
            color: #222;
            padding: 50px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        h2 {
            color: #892981;
            margin-top: 0;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .row {
            display: flex;
            gap: 15px;
        }
        .row input {
            flex: 1;
        }
        button {
            background: #892981;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #6b1f65;
        }
        label {
            font-weight: bold;
        }
        .save-info {
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .save-info input {
            width: auto;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Finalize Your Order</h2>
        <form method="POST" id="checkoutForm">
            <input type="hidden" name="total_price" id="total_input">
            <input type="hidden" name="cart_items" id="cart_items_input">

            <h3>Shipping Address</h3>
            <input type="text" name="address" placeholder="Address" required value="<?= htmlspecialchars($saved['saved_address'] ?? '') ?>">
            <div class="row">
                <input type="text" name="city" placeholder="City" required value="<?= htmlspecialchars($saved['saved_city'] ?? '') ?>">
                <input type="text" name="postal" placeholder="Postal Code" required value="<?= htmlspecialchars($saved['saved_postal'] ?? '') ?>">
            </div>

            <h3>Payment</h3>
            <select name="payment">
                <option value="Credit Card">Credit Card</option>
                <option value="PayPal">PayPal</option>
            </select>

            <?php if($user_id): ?>
                <div class="save-info">
                    <input type="checkbox" name="save_info" id="save_info">
                    <label for="save_info">Save this information for my next order</label>
                </div>
            <?php endif; ?>

            <button type="submit">CONFIRM & PAY</button>
        </form>
    </div>

    <script>
        // Refresh cart data right before submit to ensure it's up-to-date
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            document.getElementById('total_input').value = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            document.getElementById('cart_items_input').value = JSON.stringify(cart);
        });
    </script>
</body>
</html>