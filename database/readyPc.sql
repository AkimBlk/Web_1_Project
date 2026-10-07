-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 21 avr. 2025 à 01:38
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ready_pc`
--
CREATE DATABASE IF NOT EXISTS `ready_pc` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ready_pc`;

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pc_id` int(11) NOT NULL,
  `user` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pc_id` (`pc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tronquer la table avant d'insérer `comments`
--

--
-- Déchargement des données de la table `comments`
--

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `ssd` varchar(40) NOT NULL,
  `prix` int(20) NOT NULL,
  `carte_graphique` varchar(50) NOT NULL,
  `processeur` varchar(40) NOT NULL,
  `ram` varchar(40) NOT NULL,
  `boitier` varchar(40) NOT NULL,
  `carte_mere` varchar(50) NOT NULL,
  `refroidissement` varchar(50) NOT NULL,
  `os` varchar(40) NOT NULL,
  `url_image` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tronquer la table avant d'insérer `products`
--

TRUNCATE TABLE `products`;
--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `ssd`, `prix`, `carte_graphique`, `processeur`, `ram`, `boitier`, `carte_mere`, `refroidissement`, `os`, `url_image`) VALUES
(68, 'Horizon 3000', '512 Go NVMe PCIe 4.0', 799, 'NVIDIA GeForce RTX 3050', 'Intel Core i5-12400F', '16 Go DDR4 3200 MHz (2×8)', 'NZXT H510 Flow', 'MSI B660M-A DDR4', 'Cooler Master Hyper 212 RGB', 'Windows 11 Home', 'Image/1744985461_ImagePc6.png'),
(69, 'Velocity 4070', '1 To Samsung 980 PRO', 1399, 'NVIDIA GeForce RTX 4070', 'AMD Ryzen 7 5800X', '32 Go DDR4 3600 MHz (2×16)', 'Corsair 4000D Airflow', 'ASUS ROG Strix B550-F Gaming', 'NZXT Kraken X53', 'Windows 11 Pro', 'Image/1744985472_ImagePc8.png'),
(70, 'Aqua Elite', '2 To WD BLACK SN850X', 2499, 'NVIDIA GeForce RTX 4080 SUPER', 'Intel Core i9-13900K', '64 Go DDR5 6000 MHz (2×32)', 'Lian Li O11 Dynamic EVO', 'MSI MPG Z790 EDGE WIFI', 'Corsair iCUE H150i ELITE CAPELLIX', 'Windows 11 Pro', 'Image/1744546755_ChatGPT Image 13 avr. 2025, 14_19_01.png'),
(71, 'Radeon Fury', '1 To Crucial P3 Plus', 1399, 'AMD Radeon RX 7800 XT', 'AMD Ryzen 7 7700X', '32 Go DDR5 5200 MHz (2×16)', 'be quiet! Pure Base 500DX', 'ASRock B650 PG Riptide', 'Cooler Master MA612 Stealth', 'Windows 11 Home', 'Image/1744546934_ChatGPT Image 13 avr. 2025, 14_22_09.png'),
(72, 'hellstorm pro', '2 To Samsung 990 PRO', 666, 'NVIDIA RTX 4070 Ti SUPER', 'AMD Ryzen 9 7900X', '64 Go DDR5 5600 MHz (2×32)', 'Fractal Design Define 7', 'ASUS ProArt X670E-CREATOR WIFI', 'Arctic Liquid Freezer II 360', 'Windows 11 Pro', 'Image/1744547282_ChatGPT Image 13 avr. 2025, 14_27_26.png'),
(73, 'bourbier', '256 Go Kingston NV2', 399, 'Intel UHD Graphics 730', 'Intel Core i3-13100', '8 Go DDR4 3200 MHz (1×8)', 'Fractal Design North Compact', 'Gigabyte B550M DS3H', 'Be Quiet! Pure Rock Slim 2', 'Windows 10 Home', 'Image/1744547606_ChatGPT Image 13 avr. 2025, 14_31_04.png'),
(76, 'Supernova Station', 'Quantum Void Storage NVMe', 5000, 'Supernova RX 7900 XT', 'Nebula Ryzen 9', 'Celestial Memory 32 Go DD6 6666MHz', 'vent Horizon Case', 'Stellar Hub X570', 'Absolute Zero AIO', 'Starfield 69', 'Image/1744985423_ImagePc5.png'),
(80, 'Zone Critique', 'ReactorCore Nova 3000	', 1945, 'Radium RTX 6060', 'NuclearSpark I5-6500R', 'ParticleBeam Cobalt 2x16 Go', 'IsotopeCage Midtower', 'GammaShield A660	', 'CriticalMass Flow 360', 'Nuclear 32', 'Image/1745189879_ImagePc13.png'),
(81, 'Resonance Infinite', 'DiamondState Brilliance 9000', 10000, 'CrystalForce RTX 9080 Ultra', 'PrismCore Diamond X-8800	', 'LuminaGem Sapphire Elite 4x32 Go', 'GemCage Infinity	', 'GeodeTech Z880 LATTICE', 'GlacialPrism Facet 1000', 'PrismOS Clarity Edition	', 'Image/1745190085_ImagePc14.png'),
(82, 'Tempest Prime', 'TempestDrive Zephyr 9000', 6500, 'GaleForce RTX 8090 Typhoon', 'AeroCore Cyclone X-7800	', 'CumulusX Stratus Elite 4X32 Go', 'AeroVault Nimbus	', 'WindFlow Z800 GUST', 'AeroFlow Hurricane 900	', 'ZephyrOS Drift	', 'Image/1745190409_ImagePc9.png'),
(83, 'Mesozoic Dominance', 'AmberCore Genesis 5000', 2010, 'MegafaunaGPU RTX 7090 Savage', 'FossilCore Apex 7700X', 'TerraRAM Gigantis	2x16 Go', 'PrimalCase Terra', 'ExtinctionTech X670 FOSSIL', 'GlacialEra Permafrost 700', 'EonOS Evolution', 'Image/1745191043_ImagePc12.png');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(15) NOT NULL,
  `password` varchar(20) NOT NULL,
  `access_level` tinyint(4) DEFAULT NULL,
  `profile_picture` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tronquer la table avant d'insérer `users`
--

--
-- Déchargement des données de la table `users`
--


--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`pc_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
