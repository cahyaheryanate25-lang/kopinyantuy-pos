CREATE DATABASE IF NOT EXISTS `db_kopinyantuy`;
USE `db_kopinyantuy`;

CREATE TABLE IF NOT EXISTS `pesanan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_order` VARCHAR(20) NOT NULL,
  `nomor_meja` VARCHAR(50) NOT NULL,
  `metode_bayar` VARCHAR(50) NOT NULL,
  `rincian_menu` TEXT NOT NULL,
  `total_harga` INT(11) NOT NULL,
  `status` ENUM('Pending','Diproses','Selesai','Dibatalkan') DEFAULT 'Pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;