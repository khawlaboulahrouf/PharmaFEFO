-- Create Database
CREATE DATABASE IF NOT EXISTS `pharmafefo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pharmafefo`;
-- 1. Table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin', 'Pharmacist', 'Stock Manager') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- 2. Table `medicines`
DROP TABLE IF EXISTS `medicines`;
CREATE TABLE `medicines` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- 3. Table `batches`
DROP TABLE IF EXISTS `batches`;
CREATE TABLE `batches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `medicine_id` INT NOT NULL,
    `batch_number` VARCHAR(50) NOT NULL,
    `quantity` INT NOT NULL CHECK (`quantity` >= 0),
    `expiration_date` DATE NOT NULL,
    `status` VARCHAR(50) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
-- 4. Table `alerts`
DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `batch_id` INT NOT NULL,
    `alert_level` ENUM('Green', 'Orange', 'Red', 'Expired') NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
-- Seed Sample Medicines
INSERT INTO `medicines` (`id`, `name`, `description`) VALUES
(1, 'Paracetamol 500mg', 'Analgesic and antipyretic medication used to treat fever and mild to moderate pain.'),
(2, 'Amoxicillin 500mg', 'Broad-spectrum penicillin antibiotic used to treat bacterial infections.'),
(3, 'Ibuprofen 400mg', 'Nonsteroidal anti-inflammatory drug (NSAID) used for pain relief and reducing fever.'),
(4, 'Lipitor (Atorvastatin) 20mg', 'Statin medication used to prevent cardiovascular disease and lower lipids.'),
(5, 'Metformin 850mg', 'First-line medication for the treatment of type 2 diabetes.');
-- Seed Sample Batches
-- Current Local Time is June 2026.
-- Let's define dates relative to June 2026:
-- Green: > 90 days (Expiry > Sept 2026) -> e.g. Expiry 2026-12-15
-- Orange: 30 to 90 days (Expiry between July 9 and Sept 9, 2026) -> e.g. Expiry 2026-08-01
-- Red: < 30 days (Expiry between June 9 and July 9, 2026) -> e.g. Expiry 2026-06-25
-- Expired: < June 9, 2026 -> e.g. Expiry 2026-05-15
INSERT INTO `batches` (`medicine_id`, `batch_number`, `quantity`, `expiration_date`, `status`) VALUES
-- Paracetamol batches
(1, 'PR-2026-A', 150, '2026-12-15', 'Active'), -- Green (189 days left)
(1, 'PR-2026-B', 100, '2026-08-01', 'Active'), -- Orange (53 days left)
(1, 'PR-2026-C', 50, '2026-06-25', 'Active'),  -- Red (16 days left)
(1, 'PR-2026-D', 75, '2026-05-15', 'Active'),  -- Expired (Past date)
-- Amoxicillin batches
(2, 'AM-2026-A', 200, '2027-03-01', 'Active'), -- Green
(2, 'AM-2026-B', 80, '2026-06-20', 'Active'),  -- Red (11 days left)
-- Ibuprofen batches
(3, 'IB-2026-A', 120, '2026-07-20', 'Active'), -- Orange (41 days left)
(3, 'IB-2026-B', 40, '2026-05-01', 'Active');  -- Expired
