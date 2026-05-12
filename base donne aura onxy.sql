CREATE DATABASE IF NOT EXISTS aura_onyx CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE aura_onyx;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'chief') DEFAULT 'user',
    is_active BOOLEAN DEFAULT FALSE,
    activation_token VARCHAR(255),
    saved_address VARCHAR(255),
    saved_city VARCHAR(100),
    saved_postal VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table (includes the new ENUM and rating columns)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category ENUM('jewelry', 'perfume', 'watches') NOT NULL,
    image_url VARCHAR(255),
    stock INT DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0,
    rating_count INT DEFAULT 0
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_price DECIMAL(10, 2) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order Items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_at_time DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Likes table (favourites)
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, product_id)
);

-- Password reset tokens table
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Chief Admin (password: ChiefPass!123)
INSERT INTO users (name, email, password, role, is_active) 
VALUES ('Chief Admin', 'chief@auraonyx.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'chief', TRUE);

-- Insert 30 products (10 each category)
INSERT INTO products (name, description, price, category, image_url, stock, average_rating, rating_count) VALUES
-- Jewelry
('Silver Cuban Link Chain', '925 Sterling Silver, 6mm width, 20-inch length.', 85.00, 'jewelry', 'silver_chain.jpg', 15, 4.8, 124),
('Gold Signet Ring', '18K Gold plated stainless steel, minimalist face.', 45.00, 'jewelry', 'gold_ring.jpg', 20, 4.5, 89),
('Onyx Bead Bracelet', 'Natural matte onyx stones with silver accent bead.', 30.00, 'jewelry', 'onyx_bracelet.jpg', 50, 4.7, 215),
('Diamond Stud Earrings', 'Lab-grown diamond, 1 carat total weight, 14K white gold.', 245.00, 'jewelry', 'diamond_studs.jpg', 8, 4.9, 67),
('Leather Wrap Bracelet', 'Genuine leather with sterling silver clasp.', 38.00, 'jewelry', 'leather_bracelet.jpg', 30, 4.3, 42),
('Stainless Steel Pendant', 'Brushed steel with onyx center, 24-inch chain.', 55.00, 'jewelry', 'steel_pendant.jpg', 25, 4.6, 98),
('Cufflinks Set', 'Rhodium plated with cubic zirconia accents.', 68.00, 'jewelry', 'cufflinks.jpg', 12, 4.4, 56),
('Pearl Necklace', 'Freshwater pearls, 18-inch, silver clasp.', 120.00, 'jewelry', 'pearl_necklace.jpg', 7, 4.8, 77),
('Titanium Ring', 'Aircraft grade titanium, comfort fit.', 95.00, 'jewelry', 'titanium_ring.jpg', 18, 4.7, 103),
('Anchor Bracelet', 'Nautical braided rope with magnetic clasp.', 25.00, 'jewelry', 'anchor_bracelet.jpg', 40, 4.2, 34),

-- Perfumes
('Armani Stronger With You Intensely', 'Warm spicy vanilla with chestnut and pink pepper.', 130.00, 'perfume', 'armani_stronger.jpg', 20, 4.9, 312),
('Yves Saint Laurent Y EDP', 'Fresh aromatic with bergamot, sage, and tonka bean.', 115.00, 'perfume', 'ysl_y.jpg', 18, 4.8, 256),
('Jean Paul Gaultier Le Male Elixir', 'Lavender, honey, and tonka in a rich oriental blend.', 125.00, 'perfume', 'jpg_elixir.jpg', 15, 4.7, 189),
('Dior Sauvage Elixir', 'Intense woody aromatic with licorice and lavender.', 145.00, 'perfume', 'dior_sauvage_elixir.jpg', 10, 4.9, 278),
('Creed Aventus', 'Pineapple, birch, and musk; a legendary fruity leather.', 325.00, 'perfume', 'creed_aventus.jpg', 5, 4.8, 412),
('Bleu de Chanel', 'Citrus aromatic with grapefruit, ginger, and cedar.', 135.00, 'perfume', 'bleu_chanel.jpg', 22, 4.7, 334),
('Tom Ford Oud Wood', 'Exotic rosewood, cardamom, and amber blend.', 255.00, 'perfume', 'tf_oud_wood.jpg', 6, 4.6, 156),
('Paco Rabanne Invictus Victory Elixir', 'Amber vanilla with smoky incense and tonka.', 140.00, 'perfume', 'invictus_elixir.jpg', 14, 4.5, 87),
('Versace Eros Flame', 'Mandarin, black pepper, and vanilla; fiery and fresh.', 90.00, 'perfume', 'eros_flame.jpg', 25, 4.4, 210),
('Givenchy Gentleman EDP', 'Iris, black vanilla, and smoky woods.', 110.00, 'perfume', 'givenchy_gentleman.jpg', 17, 4.6, 143),

-- Watches
('Classic Chronograph', 'Stainless steel case, leather strap, Japanese movement.', 210.00, 'watches', 'chrono_steel.jpg', 12, 4.7, 89),
('Minimalist Mesh Watch', 'Ultra-thin silver mesh band, minimalist dial.', 165.00, 'watches', 'mesh_watch.jpg', 20, 4.5, 112),
('Diver Automatic', '200m water resistance, unidirectional bezel, black dial.', 320.00, 'watches', 'diver_auto.jpg', 8, 4.8, 65),
('Luxury Gold Plated', 'Gold plated case, brown leather, Roman numerals.', 450.00, 'watches', 'gold_watch.jpg', 5, 4.9, 41),
('Smart Hybrid Watch', 'Analog hands with smart notifications, activity tracking.', 199.00, 'watches', 'smart_hybrid.jpg', 15, 4.3, 78),
('Field Watch', 'Military-inspired canvas strap, luminous hands.', 120.00, 'watches', 'field_watch.jpg', 30, 4.4, 55),
('Skeleton Automatic', 'Exhibition case back, visible movement, 42mm.', 275.00, 'watches', 'skeleton_auto.jpg', 9, 4.6, 47),
('Digital Sports Watch', 'Resin band, stopwatch, backlight, shock resistant.', 85.00, 'watches', 'digital_sport.jpg', 40, 4.2, 211),
('Vintage Pocket Watch', 'Brass case, chain included, roman numeral dial.', 85.00, 'watches', 'pocket_watch.jpg', 10, 4.5, 87),
('Dress Chronograph', 'Slim leather strap, sapphire glass, date window.', 260.00, 'watches', 'dress_chrono.jpg', 13, 4.7, 73);