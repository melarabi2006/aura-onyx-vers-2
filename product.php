<?php
session_start();
require 'db.php';
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

// Related products (same category, exclude current)
$related = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? ORDER BY average_rating DESC LIMIT 4");
$related->execute([$product['category'], $id]);
$relatedProducts = $related->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> | Aura & Onyx</title>
    <style>
        :root { --bg: #fff; --accent: #892981; --light-grey: #f5f5f5; --text: #222; }
        body { font-family: 'Segoe UI', sans-serif; background: #f9f9f9; margin:0; color:var(--text); }
        header {
            display:flex; justify-content:space-between; align-items:center; padding:15px 50px;
            background:white; box-shadow:0 2px 8px rgba(0,0,0,0.05);
        }
        .logo a { font-size:1.8rem; font-weight:bold; color:var(--accent); text-decoration:none; }
        .container { max-width:1200px; margin:auto; padding:40px; display:flex; gap:40px; background:white; border-radius:15px; margin-top:30px; }
        .product-img { flex:1; }
        .product-img img { width:80%; border-radius:15px; }
        .details { flex:1; }
        .details h1 { font-size:2rem; margin-bottom:10px; }
        .price { font-size:2rem; color:var(--accent); font-weight:bold; }
        .desc { margin:20px 0; line-height:1.6; }
        .stars { color:#f0ad4e; font-size:1.2rem; margin:10px 0; }
        .quantity-selector { display:flex; align-items:center; gap:15px; margin:20px 0; }
        .quantity-selector button { background:var(--light-grey); border:1px solid #ccc; padding:8px 12px; cursor:pointer; font-size:1rem; }
        .quantity-selector input { width:60px; text-align:center; padding:8px; border:1px solid #ccc; }
        .btn-add { background:var(--accent); color:white; border:none; padding:12px 30px; border-radius:5px; font-size:1rem; cursor:pointer; }
        .btn-add:hover { background:#6b1f65; }
        .btn-like { background:white; border:2px solid var(--accent); color:var(--accent); padding:10px 25px; border-radius:5px; margin-left:15px; cursor:pointer; font-size:1rem; }
        .btn-like.active { background:var(--accent); color:white; }
        .related { margin-top:60px; }
        .related h2 { text-align:center; color:var(--accent); margin-bottom:30px; }
        .related-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
        .product-card {
            background:var(--light-grey); border-radius:10px; padding:20px; text-align:center;
            transition:transform 0.3s; cursor:pointer; border:1px solid #eee;
        }
        .product-card:hover { transform:scale(1.03); border-color:var(--accent); }
        .product-card img { width:100%; height:200px; object-fit:contain; }
        .product-card h3 { font-size:1rem; margin:10px 0; }
        .product-card .price { color:var(--accent); font-weight:bold; }
    </style>
</head>
<body>
    <header>
        <div class="logo"><a href="index.php">AURA & ONYX</a></div>
        <div>
            <a href="favorites.php">❤️ <span id="fav-count">0</span></a>
            <a href="cart.php" style="margin-left:20px;">🛒 <span id="cart-count">0</span></a>
        </div>
    </header>

    <div class="container">
        <div class="product-img">
            <img src="images/<?= $product['image_url'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='https://via.placeholder.com/400x400/eee/892981?text=Product'">
        </div>
        <div class="details">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div class="stars" id="starsDisplay"></div>
            <div class="price">$<?= number_format($product['price'], 2) ?></div>
            <p class="desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <p>Category: <?= ucfirst($product['category']) ?></p>
            <div class="quantity-selector">
                <label>Qty:</label>
                <button onclick="changeQty(-1)">−</button>
                <input type="number" id="qtyInput" value="1" min="1" max="10">
                <button onclick="changeQty(1)">+</button>
            </div>
            <button class="btn-add" onclick="addToCartWithQty()">Add to Cart</button>
            <button class="btn-like" id="likeBtn" onclick="toggleLike()">♡ Save</button>
        </div>
    </div>

    <div class="related">
        <h2>You Might Also Like</h2>
        <div class="related-grid">
            <?php foreach($relatedProducts as $rp): ?>
                <div class="product-card" onclick="location.href='product.php?id=<?= $rp['id'] ?>'">
                    <img src="images/<?= $rp['image_url'] ?>" alt="<?= htmlspecialchars($rp['name']) ?>" onerror="this.src='https://via.placeholder.com/200x200/eee/892981?text=Product'">
                    <h3><?= htmlspecialchars($rp['name']) ?></h3>
                    <div class="price">$<?= number_format($rp['price'],2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        const productId = <?= $product['id'] ?>;
        const productPrice = <?= $product['price'] ?>;
        const productName = <?= json_encode($product['name']) ?>;
        const productRating = <?= $product['average_rating'] ?>;

        function renderStars(avg) {
            let full = Math.floor(avg);
            let half = avg % 1 >= 0.5 ? 1 : 0;
            let empty = 5 - full - half;
            return '★'.repeat(full) + (half ? '½' : '') + '☆'.repeat(empty);
        }
        document.getElementById('starsDisplay').innerHTML = renderStars(productRating);

        function changeQty(delta) {
            let qty = document.getElementById('qtyInput');
            let val = parseInt(qty.value) + delta;
            if(val >= 1 && val <= 10) qty.value = val;
        }

        function addToCartWithQty() {
            let qty = parseInt(document.getElementById('qtyInput').value);
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let existing = cart.find(i => i.id === productId);
            if(existing) existing.qty += qty;
            else cart.push({id: productId, name: productName, price: productPrice, qty});
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            alert(productName + ' added to cart!');
        }

        // Favourites (localStorage)
        let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        updateLikeButton();
        function toggleLike() {
            let idx = favorites.indexOf(productId);
            if(idx > -1) favorites.splice(idx,1);
            else favorites.push(productId);
            localStorage.setItem('favorites', JSON.stringify(favorites));
            updateLikeButton();
            updateFavCount();
        }
        function updateLikeButton() {
            let btn = document.getElementById('likeBtn');
            if(favorites.includes(productId)) {
                btn.textContent = '❤️ Saved';
                btn.classList.add('active');
            } else {
                btn.textContent = '♡ Save';
                btn.classList.remove('active');
            }
        }

        function updateCartCount() {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let totalQty = cart.reduce((s, i) => s + i.qty, 0);
            document.getElementById('cart-count').innerText = totalQty;
        }
        function updateFavCount() {
            document.getElementById('fav-count').innerText = favorites.length;
        }
        updateCartCount();
        updateFavCount();
    </script>
</body>
</html>