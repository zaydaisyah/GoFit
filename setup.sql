-- setup.sql
-- Create Database
CREATE DATABASE IF NOT EXISTS gofit_db;
USE gofit_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    avatar VARCHAR(255) DEFAULT 'img/team/team-1.jpg',
    tier ENUM('Bronze', 'Silver', 'Gold', 'Platinum') DEFAULT 'Bronze',
    points INT DEFAULT 0,
    streak INT DEFAULT 0,
    joined_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    reset_token VARCHAR(255) NULL,
    reset_expiry DATETIME NULL
);

-- 1b. Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'img/team/team-1.jpg',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert Dummy Users for Leaderboard
-- Insert Users (Customers and Admin)
INSERT INTO users (name, email, password, role, tier, points, avatar) VALUES 
('Sarah Connor', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Platinum', 5200, 'img/team/team-2.jpg'),
('Mike Tyson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Platinum', 4800, 'img/team/team-3.jpg'),
('Bruce Lee', 'bruce@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Platinum', 4500, 'img/team/team-1.jpg'),
('Lara Croft', 'lara@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Gold', 3900, 'img/team/team-2.jpg'),
('Rocky Balboa', 'rocky@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Gold', 3500, 'img/team/team-3.jpg'),
('Wonder Woman', 'diana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Silver', 2800, 'img/team/team-1.jpg'),
('Thor Odinson', 'thor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Silver', 2500, 'img/team/team-2.jpg'),
('Hulk Hogan', 'hulk@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Bronze', 1200, 'img/team/team-3.jpg'),
('Black Widow', 'natasha@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Bronze', 900, 'img/team/team-1.jpg'),
('Steve Rogers', 'steve@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Bronze', 500, 'img/team/team-2.jpg'),
('Admin User', 'admin@gofit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Bronze', 0, 'img/team/team-1.jpg');

-- Insert Dummy Admin
INSERT INTO admins (name, email, password, avatar) VALUES 
('System Admin', 'admin@gofit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'img/team/team-1.jpg');

-- 2. Rewards Table (Pre-populated)
CREATE TABLE IF NOT EXISTS rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    cost INT NOT NULL,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

INSERT INTO rewards (title, description, cost) VAlUES 
('Free Protein Shake', 'Redeem for a post-workout fuel up.', 500),
('Gym Towel', 'Microfiber towel for sweat sessions.', 800),
('10% Off Merch', 'Get 10% discount on any store item.', 1000),
('GoFit Water Bottle', 'Stay hydrated with a premium bottle.', 2000),
('Free Personal Training', 'One-hour session with an expert trainer.', 2500),
('Yoga Mat', 'High quality non-slip yoga mat.', 3500),
('Nutrition Plan', 'Customized diet plan by experts.', 4000),
('Hoodie', 'Comfortable GoFit branded hoodie.', 4500),
('Supplements Pack', 'Starter pack of vitamins/protein.', 5500),
('Massage Session', '30-minute recovery massage.', 6000),
('1 Month Membership', 'Get a free month of access.', 12000),
('Smart Band', 'Track your fitness stats.', 15000);

-- 3. Activity Log (Optional, for timeline)
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity VARCHAR(255),
    points_earned INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10, 2),
    payment_method VARCHAR(100) DEFAULT 'Cash on Delivery',
    status VARCHAR(50) DEFAULT 'Pending',
    processed_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (processed_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- 5. Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_name VARCHAR(255),
    quantity INT,
    price DECIMAL(10, 2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- 6. User Rewards (Redemptions)
CREATE TABLE IF NOT EXISTS user_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    reward_id INT,
    coupon_code VARCHAR(50),
    redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE CASCADE
);

-- 8. Admin Logs
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- 9. Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    category ENUM('Merchandise', 'Supplements') DEFAULT 'Merchandise',
    image VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert Dummy Products (Merchandise)
INSERT INTO products (name, price, stock, category, image) VALUES 
('GoFit Towel', 35.00, 50, 'Merchandise', 'img/shop/towel.png'),
('Gym Sandals', 45.00, 30, 'Merchandise', 'img/shop/sandals.png'),
('Dumbbell 1kg', 17.00, 100, 'Merchandise', 'img/shop/dumbbell_1kg.png'),
('Dumbbell 5kg', 25.00, 80, 'Merchandise', 'img/shop/dumbbell_5kg.png'),
('Dumbbell 10kg', 45.00, 40, 'Merchandise', 'img/shop/dumbbell_10kg.png'),
('Water Bottle', 32.00, 60, 'Merchandise', 'img/shop/bottle.png'),
('T-Shirt Jersey', 48.00, 45, 'Merchandise', 'img/shop/jersey.png'),
('Fitness Pants', 38.00, 35, 'Merchandise', 'img/shop/pants.png'),
('Sport Socks', 20.00, 120, 'Merchandise', 'img/shop/socks.png');

-- Insert Dummy Products (Supplements - Based on typical gym shop)
INSERT INTO products (name, price, stock, category, image) VALUES 
('Whey Protein 2kg', 180.00, 25, 'Supplements', 'img/shop/whey.png'),
('Pre-Workout', 120.00, 15, 'Supplements', 'img/shop/pre-workout.png'),
('BCAA Capsules', 85.00, 40, 'Supplements', 'img/shop/bcaa.png'),
('Creatine 500g', 95.00, 30, 'Supplements', 'img/shop/creatine.png');

-- 7. Insert Dummy Orders (Assuming IDs 1 and 2 exist from previous inserts)
-- Sarah (ID 1) bought a Hoodie
INSERT INTO orders (user_id, total, status) VALUES (1, 45.00, 'Completed');
INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (1, 'GoFit Hoodie', 1, 45.00);

-- Mike (ID 2) bought Supplements
INSERT INTO orders (user_id, total, status) VALUES (2, 120.00, 'Shipped');
INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (2, 'Whey Protein', 1, 120.00);

-- Bruce (ID 3) bought Gear [Assuming ID 3 exists]
INSERT INTO orders (user_id, total, status) VALUES (3, 85.00, 'Pending');
INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (3, 'Gym Gloves', 1, 35.00);
INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (3, 'Lifting Belt', 1, 50.00);
