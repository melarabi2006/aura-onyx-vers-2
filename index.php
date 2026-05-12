<?php
require 'db.php';
session_start();

// Fetch popular products (by rating and like count)
$popStmt = $pdo->query("
    SELECT p.*, COUNT(l.id) as like_count 
    FROM products p 
    LEFT JOIN likes l ON p.id = l.product_id 
    GROUP BY p.id 
    ORDER BY p.average_rating DESC, like_count DESC 
    LIMIT 8
");
$popularProducts = $popStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aura & Onyx | Premium Men's Essentials</title>
    <style>
        :root {
            --bg: #ffffff;
            --card-bg: #f5f5f5;
            --text: #222;
            --accent: #892981;
            --hover-accent: #6b1f65;
            --light-grey: #eaeaea;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); }

        /* NAV */
        header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 50px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            position: sticky; top:0; z-index: 1000;
        }
        .logo a {
            font-size: 1.8rem; font-weight: bold; letter-spacing: 3px;
            color: var(--accent); text-decoration: none;
        }
        nav a {
            color: #333; text-decoration: none; margin: 0 18px;
            font-weight: 500; transition: color 0.3s;
        }
        nav a:hover { color: var(--accent); }
        .header-icons a { color: #333; margin-left: 20px; text-decoration: none; font-size: 1.2rem; }

        /* HERO */
        .hero {
            height: 100vh; background: url('images/hero_banner.jpg') center/cover no-repeat;
            background-color: #892981; /* fallback */
            display: flex; align-items: center; justify-content: center; position: relative;
        }
        .hero::before { content: ''; position: absolute; inset:0; background: rgba(0,0,0,0.4); }
        .hero-content { text-align: center; color: white; z-index: 2; max-width: 700px; }
        .hero-content h1 { font-size: 4rem; margin-bottom: 20px; text-shadow: 2px 2px 10px black; }
        .hero-content p { font-size: 1.2rem; margin-bottom: 40px; }
        .btn-main {
            background: var(--accent); color: white; padding: 15px 40px; border-radius: 50px;
            text-decoration: none; font-weight: bold; font-size: 1.1rem; transition: 0.3s;
            display: inline-block; border: none; cursor: pointer;
        }
        .btn-main:hover { background: var(--hover-accent); transform: scale(1.05); }

        /* CATEGORIES SECTION */
        .categories { padding: 80px 50px; background: #f9f9f9; text-align: center; }
        .categories h2 { font-size: 2.5rem; margin-bottom: 50px; color: var(--accent); }
        .cat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; max-width: 1200px; margin: auto; }
        .cat-card {
            background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .cat-card:hover { transform: translateY(-10px); }
        .cat-card img { width: 100%; height: 300px; object-fit: cover; }
        .cat-card h3 { margin: 20px 0 10px; font-size: 1.8rem; }
        .cat-card a { display: inline-block; margin-bottom: 25px; color: var(--accent); font-weight: bold; text-decoration: none; }

        /* PRODUCTS SECTION */
        .section { padding: 80px 50px; }
        .section h2 { text-align: center; font-size: 2.5rem; color: var(--accent); margin-bottom: 40px; }

        .sort-bar { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .sort-bar select { padding: 10px 20px; border: 1px solid #ddd; border-radius: 5px; }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 25px;
            max-width: 1400px;
            margin: auto;
        }
        @media (max-width: 1200px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (max-width: 992px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }

        .product-card {
            background: var(--card-bg); border-radius: 15px; padding: 20px;
            text-align: center; transition: transform 0.25s, box-shadow 0.25s;
            cursor: pointer; position: relative; overflow: hidden;
            border: 1px solid var(--light-grey);
        }
        .product-card:hover {
            transform: scale(1.05); box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: var(--accent);
        }
        .product-card img { width: 100%; height: 200px; object-fit: contain; margin-bottom: 15px; }
        .product-card h3 { font-size: 1rem; margin-bottom: 10px; }
        .product-card .price { color: var(--accent); font-weight: bold; font-size: 1.2rem; }
        .stars { color: #f0ad4e; margin: 8px 0; }
        .product-hover-details { display: none; margin-top: 10px; font-size: 0.9rem; color: #555; height: 50px; }
        .product-card:hover .product-hover-details { display: block; }
        .product-card button {
            background: var(--accent); color: white; border: none; padding: 8px 20px;
            border-radius: 5px; cursor: pointer; margin-top: 10px; transition: 0.3s;
        }
        .product-card button:hover { background: var(--hover-accent); }

        /* FOOTER PANELS */
        .footer-panels { background: #f0f0f0; padding: 60px 50px; display: flex; justify-content: space-around; flex-wrap: wrap; }
        .footer-col { flex: 1; min-width: 200px; margin: 20px; }
        .footer-col h3 { color: var(--accent); margin-bottom: 20px; }
        .footer-col a { display: block; color: #444; text-decoration: none; margin-bottom: 10px; }
    </style>
</head>
<body>

<header>
    <div class="logo"><a href="index.php">AURA & ONYX</a></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="index.php?category=jewelry">Jewelry</a>
        <a href="index.php?category=perfume">Perfume</a>
        <a href="index.php?category=watches">Watches</a>
        <a href="promotions.php">Promotions</a>
    </nav>
    <div class="header-icons">
        <a href="favorites.php">❤️ <span id="fav-count">0</span></a>
        <a href="cart.php">🛒 <span id="cart-count">0</span></a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</header>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <h1>Define Your Style</h1>
        <p>Premium jewelry, iconic fragrances, and timeless watches.</p>
        <a href="index.php?category=all" class="btn-main">Order Now</a>
    </div>
</section>

<!-- CATEGORIES -->
<section class="categories">
    <h2>Explore Collections</h2>
    <div class="cat-grid">
        <div class="cat-card">
            <img src="images/cat_jewelry.jpg" alt="Jewelry">
            <h3>Jewelry</h3>
            <a href="index.php?category=jewelry">Explore →</a>
        </div>
        <div class="cat-card">
            <img src="images/cat_perfume.jpg" alt="Perfumes">
            <h3>Perfumes</h3>
            <a href="index.php?category=perfume">Explore →</a>
        </div>
        <div class="cat-card">
            <img src="images/cat_watches.jpg" alt="Watches">
            <h3>Watches</h3>
            <a href="index.php?category=watches">Explore →</a>
        </div>
    </div>
</section>

<!-- ALL PRODUCTS with sorting -->
<section class="section">
    <h2>All Products</h2>
    <div class="sort-bar">
        <select id="sortSelect" onchange="sortProducts(this.value)">
            <option value="default">Default</option>
            <option value="price-asc">Price: Low to High</option>
            <option value="price-desc">Price: High to Low</option>
            <option value="name">Name A-Z</option>
            <option value="rating">Top Rated</option>
        </select>
    </div>
    <div class="product-grid" id="allProductsGrid">
        <!-- populated by JS -->
    </div>
</section>

<!-- POPULAR PRODUCTS -->
<section class="section" style="background: #f9f9f9;">
    <h2>Fan Favorites</h2>
    <div class="product-grid" id="popularGrid">
        <?php foreach($popularProducts as $p): ?>
            <?php
                // Dynamic star rating
                $fullStars = floor($p['average_rating']);
                $halfStar = ($p['average_rating'] - $fullStars) >= 0.5 ? 1 : 0;
                $emptyStars = 5 - $fullStars - $halfStar;
                $starsHtml = str_repeat('★', $fullStars) . ($halfStar ? '½' : '') . str_repeat('☆', $emptyStars);
            ?>
            <div class="product-card" onclick="location.href='product.php?id=<?= $p['id'] ?>'">
                <img src="images/<?= $p['image_url'] ?>" onerror="this.src='https://via.placeholder.com/200x200/eee/892981?text=Product'">
                <h3><?= htmlspecialchars($p['name']) ?></h3>
                <div class="stars"><?= $starsHtml ?></div>
                <div class="price">$<?= number_format($p['price'], 2) ?></div>
                <div class="product-hover-details"><?= substr($p['description'],0,80) ?>...</div>
                <button onclick="event.stopPropagation(); addToCart(<?= $p['id'] ?>, <?= json_encode($p['name']) ?>, <?= $p['price'] ?>)">Add to Cart</button>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- FOOTER WITH FAQ, ABOUT, CONTACT -->
<footer class="footer-panels">
    <div class="footer-col">
        <h3>About Us</h3>
        <p>Aura & Onyx is dedicated to providing premium men's essentials – from timeless jewelry to iconic fragrances and watches.</p>
        <a href="about.php">Learn More</a>
    </div>
    <div class="footer-col">
        <h3>FAQ</h3>
        <a href="faq.php">Shipping & Returns</a>
        <a href="faq.php">Sizing Guide</a>
        <a href="faq.php">Payment Options</a>
    </div>
    <div class="footer-col">
        <h3>Support</h3>
        <a href="contact.php">Contact Us</a>
        <a href="mailto:support@auraonyx.com">support@auraonyx.com</a>
        <p>+1 (800) 555-ONYX</p>
    </div>
</footer>

<script>
    // Load all products via AJAX for sorting
    async function loadAllProducts(sort = 'default', category = 'all') {
        const resp = await fetch(`api_products.php?sort=${sort}&category=${category}`);
        const products = await resp.json();
        const grid = document.getElementById('allProductsGrid');
        grid.innerHTML = products.map(p => `
            <div class="product-card" onclick="location.href='product.php?id=${p.id}'">
                <img src="images/${p.image_url}" onerror="this.src='https://via.placeholder.com/200x200/eee/892981?text=Product'">
                <h3>${p.name}</h3>
                <div class="stars">${renderStars(p.average_rating)}</div>
                <div class="price">$${parseFloat(p.price).toFixed(2)}</div>
                <div class="product-hover-details">${p.description.substring(0,80)}...</div>
                <button onclick="event.stopPropagation(); addToCart(${p.id}, ${JSON.stringify(p.name)}, ${p.price})">Add to Cart</button>
            </div>
        `).join('');
    }

    function renderStars(avg) {
        let full = Math.floor(avg);
        let half = avg % 1 >= 0.5 ? 1 : 0;
        let empty = 5 - full - half;
        return '★'.repeat(full) + (half ? '½' : '') + '☆'.repeat(empty);
    }

    function sortProducts(value) { loadAllProducts(value); }
    loadAllProducts();

    // Cart & Favorites logic
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    updateCartCount(); updateFavCount();

    function updateCartCount() {
        document.getElementById('cart-count').innerText = cart.reduce((sum, i)=>sum+i.qty,0);
    }
    function updateFavCount() {
        document.getElementById('fav-count').innerText = favorites.length;
    }

    function addToCart(id, name, price) {
        let existing = cart.find(item => item.id === id);
        if (existing) existing.qty++;
        else cart.push({id, name, price, qty:1});
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        alert(name + ' added!');
    }
</script>
</body>
</html>