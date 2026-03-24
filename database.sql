SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `db_reservasi_hotel` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_reservasi_hotel`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `role` enum('admin','receptionist','guest') NOT NULL DEFAULT 'guest',
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`username`, `password`, `email`, `role`, `phone`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@hotel.com', 'admin', '081234567890'),
('receptionist1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receptionist@hotel.com', 'receptionist', '081234567891'),
('guest1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guest1@gmail.com', 'guest', '081234567892'),
('guest2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guest2@yahoo.com', 'guest', '081234567893'),
('guest3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guest3@outlook.com', 'guest', '081234567894');

CREATE TABLE `room_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `max_occupancy` int(11) NOT NULL,
  `amenities` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `room_types` (`name`, `description`, `base_price`, `max_occupancy`, `amenities`) VALUES
('Standard Room', 'Kamar sederhana dengan fasilitas lengkap untuk 2 orang.', 300000.00, 2, 'TV, AC, WiFi, Kamar Mandi Dalam'),
('Deluxe Room', 'Kamar luas dengan pemandangan kota dan fasilitas premium.', 500000.00, 2, 'Smart TV, AC, WiFi, Kulkas Mini, Bathtub'),
('Family Suite', 'Ruangan yang cocok untuk keluarga dengan dua tempat tidur.', 800000.00, 4, '2 Bed, Smart TV, AC, WiFi, Sofa, Pantry'),
('Presidential Suite', 'Kemewahan maksimal dengan ruang tamu terpisah.', 2000000.00, 4, 'VIP Amenities, Living Room, Jacuzzi, Private Balcony'),
('Single Backpacker', 'Kamar hemat untuk pelancong tunggal.', 150000.00, 1, 'Kipas Angin, Kasur Single, WiFi');

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_type_id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL UNIQUE,
  `floor` int(11) NOT NULL,
  `status` enum('available','occupied','maintenance') NOT NULL DEFAULT 'available',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`room_type_id`) REFERENCES `room_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rooms` (`room_type_id`, `room_number`, `floor`, `status`, `notes`) VALUES
(1, '101', 1, 'available', 'Dekat resepsionis'),
(1, '102', 1, 'occupied', ''),
(2, '201', 2, 'available', 'Pemandangan taman'),
(3, '301', 3, 'available', ''),
(4, '401', 4, 'maintenance', 'AC rusak');

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guest_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests_count` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `special_request` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`guest_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bookings` (`guest_id`, `room_id`, `check_in`, `check_out`, `guests_count`, `total_price`, `status`, `special_request`) VALUES
(3, 2, '2026-03-10', '2026-03-12', 2, 600000.00, 'confirmed', 'Extra bantal'),
(4, 3, '2026-03-15', '2026-03-16', 2, 500000.00, 'pending', 'Lantai atas'),
(5, 4, '2026-03-20', '2026-03-22', 4, 1600000.00, 'completed', ''),
(3, 1, '2026-04-01', '2026-04-03', 1, 600000.00, 'cancelled', ''),
(4, 5, '2026-04-10', '2026-04-15', 1, 750000.00, 'pending', 'No smoking room');

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `reviews` (`booking_id`, `rating`, `comment`) VALUES
(3, 5, 'Pelayanan sangat bagus, kamar bersih dan luas! Terima kasih.'),
(1, 4, 'Kamar nyaman, tapi air panas sempat mati sebentar.'),
(4, 1, 'Awalnya cancel karena urusan mendadak, respon cancel lambat.'),
(2, 5, 'Belum check-in tapi cs sangat ramah saat dihubungi.'),
(5, 5, 'Harga terjangkau untuk backpacker');

COMMIT;
