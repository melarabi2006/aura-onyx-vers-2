<?php
require 'db.php';
// Fetch a few products to highlight as "specials" (optional)
$specials = $pdo->query("SELECT * FROM products ORDER BY price ASC LIMIT 4")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Promotions | Aura & Onyx</title>
    <style>
        body { font-family:'Segoe UI',sans-serif; background:#f4f4f4; color:#222; padding:50px; }
        .container { max-width:1000px; margin:auto; background:white; padding:40px; border-radius:15px; }
        h1 { color:#892981; text-align:center; }
        .banner { background:#892981; color:white; text-align:center; padding:30px; border-radius:10px; margin-bottom:30px; }
        .banner h2 { margin:0; font-size:2rem; }
        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:20px; }
        .card { background:#f9f9f9; padding:20px; border-radius:10px; text-align:center; }
        .card img { width:100%; height:180px; object-fit:contain; }
        .price { color:#892981; font-weight:bold; }
        .old-price { text-decoration:line-through; color:#888; }
        a { color:#892981; }
    </style>
</head>
<body>
    <div class="container">
        <div class="banner">
            <h2>🔥 Limited Time Offer</h2>
            <p>Up to 30% off on selected jewelry, perfumes, and watches</p>
        </div>
        <h1>Current Specials</h1>
        <div class="grid">
            <?php foreach($specials as $s): ?>
            <div class="card">
                <a href="product.php?id=<?= $s['id'] ?>">
                    <img src="images/<?= $s['image_url'] ?>" alt="<?= htmlspecialchars($s['name']) ?>">
                </a>
                <h3><?= htmlspecialchars($s['name']) ?></h3>
                <p class="price">$<?= number_format($s['price'] * 0.85, 2) ?> <span class="old-price">$<?= number_format($s['price'],2) ?></span></p>
                <a href="product.php?id=<?= $s['id'] ?>" style="font-weight:bold;">Shop Now</a>
            </div>
            <?php endforeach; ?>
        </div>
        <p style="text-align:center; margin-top:30px;"><a href="index.php">← Back to shop</a></p>
    </div>
</body>
</html>