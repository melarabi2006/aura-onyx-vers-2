<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Basket | Aura & Onyx</title>
    <style>
        body { background: #f4f4f4; color: #222; font-family: 'Segoe UI', sans-serif; padding: 50px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #892981; color: white; }
        .btn { background: #892981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; margin-top: 20px; }
        .btn-danger { background: #ff4c4c; cursor: pointer; padding: 5px 10px; border: none; border-radius: 5px; color: white; }
        .qty-btn { background: #eee; border:1px solid #ccc; padding: 5px 10px; cursor:pointer; border-radius:5px; }
        .qty-input { width: 40px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Shopping Basket</h2>
        <table>
            <thead>
                <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr>
            </thead>
            <tbody id="cart-body"></tbody>
        </table>
        <h3 style="text-align: right; margin-top: 20px;">Total: $<span id="cart-total">0.00</span></h3>
        <div style="text-align: right;">
            <a href="index.php" style="color:#892981; margin-right:20px; text-decoration:none;">← Continue Shopping</a>
            <a href="checkout.php" class="btn" id="checkout-btn">Proceed to Checkout</a>
        </div>
    </div>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartBody = document.getElementById('cart-body');
        const cartTotal = document.getElementById('cart-total');

        function renderCart() {
            cartBody.innerHTML = '';
            let total = 0;
            if (cart.length === 0) {
                cartBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Your basket is empty.</td></tr>';
                document.getElementById('checkout-btn').style.display = 'none';
            } else {
                cart.forEach((item, index) => {
                    let subtotal = item.price * item.qty;
                    total += subtotal;
                    cartBody.innerHTML += `
                        <tr>
                            <td>${item.name}</td>
                            <td>$${parseFloat(item.price).toFixed(2)}</td>
                            <td>
                                <button class="qty-btn" onclick="changeQty(${index}, -1)">−</button>
                                <input class="qty-input" value="${item.qty}" onchange="updateQty(${index}, this.value)">
                                <button class="qty-btn" onclick="changeQty(${index}, 1)">+</button>
                            </td>
                            <td>$${subtotal.toFixed(2)}</td>
                            <td><button class="btn-danger" onclick="removeItem(${index})">Remove</button></td>
                        </tr>
                    `;
                });
                document.getElementById('checkout-btn').style.display = 'inline-block';
            }
            cartTotal.innerText = total.toFixed(2);
        }

        function changeQty(index, delta) {
            cart[index].qty += delta;
            if (cart[index].qty <= 0) cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }
        function updateQty(index, newVal) {
            let val = parseInt(newVal);
            if (val > 0) {
                cart[index].qty = val;
            } else {
                cart.splice(index, 1);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }
        function removeItem(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }
        renderCart();
    </script>
</body>
</html>