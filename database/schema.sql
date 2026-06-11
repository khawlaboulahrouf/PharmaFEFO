-- ============================================================
-- PharmaFEFO - Schéma de base de données (correspond à l'ERD)
-- ============================================================

CREATE DATABASE IF NOT EXISTS pharmafefo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pharmafefo;

-- ----------------------------------------------------------
-- Table: users
-- ----------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('preparateur', 'pharmacien', 'administrateur') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------------------------------------
-- Table: products
-- ----------------------------------------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    reference VARCHAR(50) NOT NULL UNIQUE,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0
);

-- ----------------------------------------------------------
-- Table: stock_batches  (1 product -> * stock_batches)
-- ----------------------------------------------------------
CREATE TABLE stock_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    lot_number VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    expiry_date DATE NOT NULL,
    status ENUM('OK','WARNING','CRITICAL','EXPIRED','RETURN_PROCESS') NOT NULL DEFAULT 'OK',
    created_by INT NULL,            -- pharmacien qui gère le lot
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_batch_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_batch_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ----------------------------------------------------------
-- Table: stock_movements (1 stock_batch -> * mouvements)
-- ----------------------------------------------------------
CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_batch_id INT NOT NULL,
    user_id INT NULL,                -- préparateur qui effectue le mouvement
    type ENUM('IN','OUT') NOT NULL,
    quantity INT NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movement_batch FOREIGN KEY (stock_batch_id) REFERENCES stock_batches(id) ON DELETE CASCADE,
    CONSTRAINT fk_movement_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ----------------------------------------------------------
-- Table: alerts (1 stock_batch -> * alerts)
-- ----------------------------------------------------------
CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_batch_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    level ENUM('INFO','WARNING','CRITICAL') NOT NULL DEFAULT 'INFO',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alert_batch FOREIGN KEY (stock_batch_id) REFERENCES stock_batches(id) ON DELETE CASCADE
);

-- ----------------------------------------------------------
-- Table: alert_thresholds (Configurer seuils d'alerte - Admin)
-- ----------------------------------------------------------
CREATE TABLE alert_thresholds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warning_days INT NOT NULL DEFAULT 90,   -- < 90 jours = Orange
    critical_days INT NOT NULL DEFAULT 30   -- < 30 jours = Rouge
);

INSERT INTO alert_thresholds (warning_days, critical_days) VALUES (90, 30);

-- ============================================================
-- Utilisateurs de démo (mot de passe pour les 3 : "password")
-- Hash bcrypt généré avec password_hash('password', PASSWORD_DEFAULT)
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
('Khalid Préparateur', 'preparateur@pharmafefo.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'preparateur'),
('Dr. Salma Pharmacien', 'pharmacien@pharmafefo.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pharmacien'),
('Mouna Admin', 'admin@pharmafefo.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrateur');

-- ============================================================
-- Données de test (seed) pour visualiser le dashboard
-- ============================================================
INSERT INTO products (name, reference, unit_price) VALUES
('Paracetamol 500mg', 'PARA500', 1.50),
('Amoxicilline 1g', 'AMOX1G', 4.20),
('Ibuprofene 400mg', 'IBU400', 2.00);

INSERT INTO stock_batches (product_id, lot_number, quantity, expiry_date, status) VALUES
(1, 'LOT-A001', 100, DATE_ADD(CURDATE(), INTERVAL 200 DAY), 'OK'),
(2, 'LOT-B002', 50,  DATE_ADD(CURDATE(), INTERVAL 60 DAY),  'OK'),
(3, 'LOT-C003', 30,  DATE_ADD(CURDATE(), INTERVAL 15 DAY),  'OK');
