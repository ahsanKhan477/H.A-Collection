-- H.A Collection E-Commerce Database
-- MySQL Database Schema

CREATE DATABASE IF NOT EXISTS ha_collection CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ha_collection;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(280) NOT NULL UNIQUE,
    short_description VARCHAR(500) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2) DEFAULT NULL,
    sizes VARCHAR(255) DEFAULT NULL,
    colors VARCHAR(255) DEFAULT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    images TEXT DEFAULT NULL,
    featured TINYINT(1) DEFAULT 0,
    is_new TINYINT(1) DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(255) DEFAULT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    size VARCHAR(10) DEFAULT NULL,
    color VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_fee DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cod',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Order Items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_title VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    size VARCHAR(10) DEFAULT NULL,
    color VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert default admin user (password: admin123)
INSERT INTO users (full_name, email, password, is_admin) VALUES
('Admin', 'admin@hacollection.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert sample categories
INSERT INTO categories (name, slug, description, sort_order) VALUES
('Dresses', 'dresses', 'Elegant dresses for every occasion', 1),
('Tops & Blouses', 'tops-blouses', 'Stylish tops and blouses', 2),
('Bottoms', 'bottoms', 'Pants, skirts, and more', 3),
('Accessories', 'accessories', 'Complete your look', 4),
('Bags & Purses', 'bags-purses', 'Premium bags and purses', 5),
('Jewelry', 'jewelry', 'Beautiful jewelry pieces', 6),
('Footwear', 'footwear', 'Stylish shoes and heels', 7),
('New Arrivals', 'new-arrivals', 'Latest additions to our collection', 8);

-- Insert sample products
INSERT INTO products (category_id, title, slug, short_description, description, price, discount_price, sizes, colors, stock, featured, is_new) VALUES
(1, 'Elegant Floral Maxi Dress', 'elegant-floral-maxi-dress', 'A beautiful floral print maxi dress perfect for summer outings.', 'This stunning maxi dress features a delicate floral print on premium fabric. The flowing silhouette and adjustable waist tie create a flattering look for all body types. Perfect for garden parties, brunches, or casual outings.', 4500.00, 3500.00, 'S,M,L,XL', 'Pink,White,Lavender', 50, 1, 1),
(1, 'Velvet Evening Gown', 'velvet-evening-gown', 'Luxurious velvet gown for special evenings.', 'Make a statement with this luxurious velvet evening gown. The deep V-neckline and figure-hugging silhouette exude elegance and sophistication. Perfect for formal events, galas, and special celebrations.', 8500.00, 7200.00, 'S,M,L', 'Black,Deep Purple,Burgundy', 30, 1, 0),
(2, 'Silk Ruffle Blouse', 'silk-ruffle-blouse', 'Soft silk blouse with elegant ruffle details.', 'Elevate your wardrobe with this premium silk blouse featuring delicate ruffle details along the neckline and sleeves. The lightweight fabric drapes beautifully and offers all-day comfort. Pair with pants or skirts for a chic look.', 3200.00, 2800.00, 'S,M,L,XL', 'White,Pink,Cream', 45, 1, 1),
(2, 'Lace Trim Camisole', 'lace-trim-camisole', 'Delicate lace-trimmed camisole top.', 'A wardrobe essential featuring beautiful lace trim detailing. This versatile camisole can be worn alone or layered under blazers and cardigans. Made from soft, breathable fabric for ultimate comfort.', 1800.00, NULL, 'S,M,L', 'Black,White,Blush', 60, 0, 1),
(3, 'High-Waist Palazzo Pants', 'high-waist-palazzo-pants', 'Comfortable wide-leg palazzo pants.', 'These elegant high-waist palazzo pants combine comfort with style. The wide-leg design creates a flowing, graceful silhouette. Features a comfortable elastic waistband and premium fabric that drapes beautifully.', 2800.00, 2200.00, 'S,M,L,XL', 'Black,Navy,Beige', 40, 1, 0),
(3, 'Pleated Midi Skirt', 'pleated-midi-skirt', 'Classic pleated midi skirt in elegant colors.', 'A timeless classic, this pleated midi skirt adds sophistication to any outfit. The accordion pleats create beautiful movement, while the midi length keeps it versatile. Perfect for office wear or weekend outings.', 2500.00, NULL, 'S,M,L', 'Dusty Rose,Navy,Sage Green', 35, 0, 1),
(4, 'Pearl Hair Clips Set', 'pearl-hair-clips-set', 'Set of 6 elegant pearl hair clips.', 'Add a touch of elegance to any hairstyle with this beautiful set of 6 pearl hair clips. Each clip features faux pearls in varying sizes, creating a luxurious look. Perfect for everyday wear or special occasions.', 850.00, 650.00, '', 'Gold,Silver', 100, 1, 1),
(5, 'Quilted Crossbody Bag', 'quilted-crossbody-bag', 'Premium quilted leather crossbody bag.', 'This stunning quilted crossbody bag combines fashion with functionality. Features premium faux leather, a gold chain strap, and multiple compartments. Compact yet spacious enough for all your essentials.', 3500.00, 2900.00, '', 'Black,Pink,White', 25, 1, 0),
(6, 'Crystal Drop Earrings', 'crystal-drop-earrings', 'Sparkling crystal drop earrings.', 'These gorgeous crystal drop earrings catch the light beautifully, adding sparkle to any outfit. Hypoallergenic posts ensure comfortable all-day wear. Perfect for both casual and formal occasions.', 1200.00, 950.00, '', 'Rose Gold,Silver,Gold', 80, 1, 1),
(7, 'Pointed Toe Stiletto Heels', 'pointed-toe-stiletto-heels', 'Classic pointed-toe heels in premium finish.', 'Step out in style with these classic pointed-toe stiletto heels. The sleek design and premium finish make them perfect for both professional and evening wear. Padded insole for comfort during extended wear.', 4200.00, 3600.00, '36,37,38,39,40', 'Black,Nude,Red', 30, 1, 0),
(1, 'Butterfly Sleeve Wrap Dress', 'butterfly-sleeve-wrap-dress', 'Romantic wrap dress with butterfly sleeves.', 'Embrace romance with this beautiful wrap dress featuring delicate butterfly sleeves. The flattering V-neckline and self-tie waist create a feminine silhouette. Made from lightweight, flowy fabric perfect for warm days.', 3800.00, 3200.00, 'S,M,L,XL', 'Floral Pink,Lavender,Sky Blue', 40, 0, 1),
(2, 'Oversized Knit Cardigan', 'oversized-knit-cardigan', 'Cozy oversized cardigan in soft knit.', 'Wrap yourself in comfort with this oversized knit cardigan. The chunky knit texture and relaxed fit make it perfect for layering. Features front button closure and deep pockets. A must-have for cozy days.', 3000.00, NULL, 'S/M,L/XL', 'Cream,Dusty Pink,Gray', 50, 0, 0);
