<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>My Favourites | Aura & Onyx</title>
    <style>
        :root { --bg: #fff; --accent: #892981; --light-grey: #f5f5f5; }
        body { font-family: 'Segoe UI', sans-serif; background: #f9f9f9; margin:0; padding:30px; color:#222; }
        header { text-align:center; margin-bottom:40px; }
        .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:25px; }
        .product-card {
            background:var(--light-grey); border-radius:12px; padding:15px; text-align:center;
            border:1px solid #eee; cursor:pointer; transition:transform 0.2s;
        }
        .product-card:hover { transform:scale(1.03); border-color:var(--accent); }
        .product-card img { width:100%; height:180px; object-fit:contain; }
        .product-card h3 { font-size:1rem; }
        .price { color:var(--accent); font-weight:bold; }
        .btn-remove { background:#ccc; border:none; padding:5px 10px; margin-top:10px; cursor:pointer; border-radius:5px; }
    </style>
</head>
<body>
    <header>
        <h1>Your Favourites</h1>
        <a href="index.php">← Back to shop</a>
    </header>
    <div class="grid" id="favGrid"></div>

    <script>
        let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        async function loadFavs() {
            if (favorites.length === 0) {
                document.getElementById('favGrid').innerHTML = '<p>No favourites yet.</p>';
                return;
            }
            const resp = await fetch('api_products.php?category=all');
            const allProducts = await resp.json();
            const favProducts = allProducts.filter(p => favorites.includes(p.id));
            document.getElementById('favGrid').innerHTML = favProducts.map(p => `
                <div class="product-card">
                    <a href="product.php?id=${p.id}">
                        <img src="images/${p.image_url}" onerror="this.src='https://via.placeholder.com/200/eee/892981?text=Product'">
                        <h3>${p.name}</h3>
                        <div class="price">$${parseFloat(p.price).toFixed(2)}</div>
                    </a>
                    <button class="btn-remove" onclick="removeFav(${p.id})">Remove</button>
                </div>
            `).join('');
        }
        function removeFav(id) {
            favorites = favorites.filter(f => f !== id);
            localStorage.setItem('favorites', JSON.stringify(favorites));
            loadFavs();
        }
        loadFavs();
    </script>
</body>
</html>