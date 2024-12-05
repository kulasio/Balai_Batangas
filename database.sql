-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2024 at 02:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `balaibatangas1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_name` varchar(255) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`setting_id`, `setting_name`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'forgot_password_key', 'popcorn', '2024-11-27 16:40:25', '2024-11-27 16:46:25');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `brand_active` int(11) NOT NULL DEFAULT 0,
  `brand_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_active`, `brand_status`) VALUES
(10, 'FBev', 1, 1),
(13, 'APtuu', 1, 1),
(14, 'PF Foods', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(30, 5, 20, 1, '2024-11-26 06:14:47', '2024-11-26 06:14:47');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categories_id` int(11) NOT NULL,
  `categories_name` varchar(255) NOT NULL,
  `categories_active` int(11) NOT NULL DEFAULT 0,
  `categories_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categories_id`, `categories_name`, `categories_active`, `categories_status`) VALUES
(12, 'Beverage', 1, 1),
(14, 'Food', 1, 1),
(15, 'Natural Products', 1, 1),
(16, 'test', 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `customer_profiles`
--

CREATE TABLE `customer_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `preferences` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_reactions`
--

CREATE TABLE `feedback_reactions` (
  `reaction_id` int(11) NOT NULL,
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_helpful` tinyint(1) NOT NULL,
  `reaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `library`
--

CREATE TABLE `library` (
  `festival_id` int(11) NOT NULL,
  `festival_name` varchar(255) NOT NULL,
  `short_intro` text DEFAULT NULL,
  `festival_image` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `cultural_significance` text DEFAULT NULL,
  `activities` text DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `map_coordinates` varchar(255) NOT NULL,
  `date_celebrated` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library`
--

INSERT INTO `library` (`festival_id`, `festival_name`, `short_intro`, `festival_image`, `description`, `cultural_significance`, `activities`, `location`, `venue`, `map_coordinates`, `date_celebrated`, `created_at`, `updated_at`, `status`) VALUES
(1, 'Lambayok Festival', 'The Lambayok Festival uniquely combines San Juan\'s three economic pillars: Lambanog (coconut wine), Palayok (clay pots), and Karagatan (sea), showcasing local craftsmanship and traditions.', 'lambayok.webp', 'The Lambayok Festival is an annual celebration in San Juan, Batangas, Philippines, that highlights the town\'s three main sources of livelihood: \"Lambanog\" (local coconut wine), \"Palayok\" (clay pots), and \"Karagatan\" (the sea). The festival showcases the town\'s rich cultural heritage through parades, street dances, and various contests. It\'s held every December and serves as a way to promote local products and tourism.', 'The Lambayok Festival embodies San Juan\'s cultural identity through its celebration of traditional industries. The festival preserves ancient pottery-making techniques, promotes sustainable coconut wine production, and honors the fishing community\'s contribution to local heritage. It serves as a bridge between generations, passing down cultural knowledge and skills.', '• Traditional Pottery Making Demonstrations\n• Lambanog Production Showcase\n• Cultural Street Dancing\n• Local Products Trade Fair\n• Fishing Village Tours\n• Cultural Performances\n• Community Parade\n• Culinary Competitions', 'San Juan, Batangass', 'San Juan Town Plaza, Cultural Center, and Coastal Areas', '13.8283° N, 121.3953° E', '2024-12-12', '2024-11-15 02:42:18', '2024-11-25 14:10:31', 1),
(2, 'Kabakahan Festival', 'The Kabakahan Festival celebrates Padre Garcia\'s prominence as the Cattle Trading Capital of the Philippines. This vibrant festival showcases the municipality\'s thriving livestock industry, featuring the country\'s largest cattle market, the \"Padre Garcia Livestock Trading Center.\"', 'bakahan.jpg', 'The Kabakahan Festival is the grandest celebration in Padre Garcia, Batangas, highlighting its status as the premier cattle trading hub in the Philippines. The festival name comes from \"kabaka\" (cattle), reflecting the municipality\'s primary industry. The Padre Garcia Livestock Trading Center facilitates millions worth of transactions weekly, making it the largest cattle market in the country. The festival features various activities that showcase both the economic importance and cultural heritage of cattle raising in the region, bringing together traders, farmers, and visitors from across the Philippines.', 'The Kabakahan Festival represents more than just economic activity; it embodies the rich agricultural heritage of Padre Garcia and its people. The festival celebrates the municipality\'s evolution into the Philippines\' Cattle Trading Capital, a title earned through generations of expertise in livestock trading and animal husbandry. It highlights the community\'s strong agricultural roots, entrepreneurial spirit, and the vital role of the cattle industry in local culture and identity. The festival also serves as a platform for promoting sustainable livestock practices and preserving traditional farming knowledge.', '• Grand Cattle Parade\n\r\n• Livestock Trading Exhibition\n\r\n• Cattle Show and Competition\n\r\n• Traditional Batangas Games\n\r\n• Rodeo Shows and Demonstrations\n\r\n• Best Cattle Raiser Awards\n\r\n• Agricultural Trade Fair\n\r\n• Local Food Festival featuring Batangas Beef Dishes\n\r\n• Cultural Street Dancing\n\r\n• Livestock Industry Seminars\n\r\n• Traditional \"Tawiran\" (Trading) Demonstrations\n\r\n• Community Feast\n\r\n• Agri-Tourism Activities\n\r\n• Local Entertainment and Performances', 'Padre Garcia, Batangas', 'Padre Garcia Livestock Trading Center, Municipal Plaza, and Town Center', '13.8789° N, 121.2089° E', 'January (Annual)', '2024-11-15 02:42:18', '2024-11-25 14:12:37', 1),
(3, 'Tapusan Festival', 'Tapusan Festival marks the end of Flores de Mayo, blending religious devotion with cultural celebrations through grand processions and community gatherings.', 'tapusan.jpg', 'The Tapusan Festival is an annual celebration held in Batangas, Philippines, typically in January...', 'The Tapusan Festival represents the strong Catholic faith and devotion to the Blessed Virgin Mary in Batangas. It marks the culmination of the month-long Flores de Mayo celebrations, showcasing the blend of religious traditions with Filipino cultural practices.', '• Grand Religious Procession\n• Santacruzan Pageant\n• Traditional Prayer Ceremonies\n• Cultural Performances\n• Community Feast\n• Flower Offering Ceremony\n• Religious Art Exhibition\n• Evening Celebrations', 'Batangas City', 'Batangas City Cathedral and Main Streets', '13.7565° N, 121.0583° E', 'January', '2024-11-15 02:42:18', '2024-11-25 14:10:31', 1),
(4, 'Sublian Festival', 'Sublian Festival showcases the traditional Subli dance, a centuries-old devotional performance honoring the Holy Cross, unique to Batangas culture.', 'Sublian.png', 'The Sublian Festival celebrates the rich cultural heritage...', 'The Sublian Festival centers around the Subli, a traditional dance-ritual that dates back to Spanish colonial times. This sacred performance combines indigenous and Catholic elements, demonstrating the unique cultural synthesis in Batangas. The festival ensures the preservation of this important intangible cultural heritage.', '• Subli Dance Performances\n• Religious Processions\n• Cultural Workshops\n• Traditional Music Shows\n• Historical Exhibits\n• Community Prayers\n• Local Art Displays\n• Street Dancing', 'Batangas City', 'Batangas City Plaza and Various Churches', '13.7565° N, 121.0583° E', 'July', '2024-11-15 02:42:18', '2024-11-25 14:10:31', 1),
(5, 'Parada ng Lechon Festival', 'Parada ng Lechon transforms Balayan\'s streets into a festive showcase of decorated roasted pigs, celebrating the feast of St. John the Baptist.', 'parada ng lechon.png', 'A festive celebration featuring the famous Filipino roasted pig...', 'The Parada ng Lechon is more than a food festival; it represents Balayan\'s communal spirit and thanksgiving. The tradition of parading decorated lechon dates back to the Spanish era, symbolizing abundance, celebration, and the town\'s culinary expertise.', '• Lechon Parade\n• Cooking Demonstrations\n• Street Dancing\n• Cultural Shows\n• Culinary Competitions\n• Community Feast\n• Traditional Games\n• Local Food Fair', 'Balayan, Batangas', 'Balayan Town Plaza and Main Streets', '13.9467° N, 120.7281° E', 'June', '2024-11-15 02:42:18', '2024-11-25 14:10:31', 1),
(6, 'El Pasubat Festival', 'El Pasubat represents Batangas\' finest: Panutsa, Suman, Bagoong, Tapa, and other local products, promoting the province\'s culinary and cultural heritage.', 'El pasubat.png', 'El Pasubat Festival showcases the various local products...', 'El Pasubat Festival serves as a provincial showcase of Batangas\' cultural identity through its local products. Each featured item represents a unique aspect of Batangueño culture, from traditional food preservation methods to indigenous sweets and delicacies.', '• Product Exhibitions\n• Cooking Demonstrations\n• Cultural Performances\n• Trade Fair\n• Food Tasting Events\n• Agricultural Shows\n• Craft Workshops\n• Local Entertainment', 'Batangas Province', 'Provincial Capitol Complex and Various Municipal Venues', '13.7565° N, 121.0583° E', 'March', '2024-11-15 02:42:18', '2024-11-25 14:10:31', 1),
(7, 'Balsa Festival', 'Balsa Festival celebrates Lian\'s maritime culture with boat races, fishing contests, and seafood feasts, honoring the town\'s fishing community.', 'Balsa.png', 'The Balsa Festival celebrates the maritime heritage...', 'The Balsa Festival highlights the maritime heritage of Lian and the crucial role of its fishing industry. It celebrates the symbiotic relationship between the community and the sea, promoting sustainable fishing practices and marine conservation.', '• Boat Racing Competition\n• Fishing Contest\n• Seafood Festival\n• Maritime Exhibits\n• Beach Activities\n• Cultural Shows\n• Environmental Programs\n• Community Games', 'Lian, Batangas', 'Lian Beachfront and Town Plaza', '14.0333° N, 120.6500° E', 'May', '2024-11-15 02:42:18', '2024-11-25 14:10:31', 1),
(8, 'Tinapay Festival', 'Tinapay Festival highlights Cuenca\'s famous bread-making tradition, featuring unique local pastries and baking competitions.', 'Tinapay.png', 'A celebration of local bread-making traditions...', 'The Tinapay Festival preserves Cuenca\'s heritage as a bread-making town. It celebrates the artisanal skills passed down through generations and the role of traditional bakeries in maintaining community bonds.', '• Bread Making Demonstrations\n• Baking Competitions\n• Food Fair\n• Cultural Shows\n• Bread Art Exhibition\n• Traditional Games\n• Community Feast\n• Local Entertainment', 'Cuenca, Batangas', 'Cuenca Municipal Plaza and Local Bakeries', '13.9089° N, 121.0486° E', 'October', '2024-11-15 02:42:18', '2024-11-25 14:10:31', 1),
(9, 'Anihan Festival', NULL, 'Anihan.png', 'The harvest festival that celebrates agricultural abundance...', NULL, NULL, 'Lobo, Batangas', NULL, '13.6458° N, 121.2439° E', 'April', '2024-11-15 02:42:18', '2024-11-15 02:42:18', 1),
(10, 'Kawayan Festival', NULL, 'kawayan.png', 'A festival celebrating the versatile bamboo plant...', NULL, NULL, 'Tuy, Batangas', NULL, '14.0167° N, 120.7278° E', 'September', '2024-11-15 02:42:18', '2024-11-15 02:42:18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_contact` varchar(255) NOT NULL,
  `sub_total` decimal(10,2) NOT NULL,
  `vat` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `paid` decimal(10,2) NOT NULL,
  `due` decimal(10,2) NOT NULL,
  `payment_type` int(11) NOT NULL,
  `payment_status` int(11) NOT NULL,
  `payment_place` int(11) NOT NULL,
  `gstn` varchar(255) NOT NULL,
  `order_status` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `gst_rate` decimal(5,2) DEFAULT NULL,
  `gst_amount` decimal(10,2) DEFAULT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `additional_charges` decimal(10,2) DEFAULT 0.00,
  `additional_discount_percent` decimal(5,2) DEFAULT 0.00,
  `additional_discount_amount` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_date`, `client_name`, `client_contact`, `sub_total`, `vat`, `total_amount`, `discount`, `grand_total`, `paid`, `due`, `payment_type`, `payment_status`, `payment_place`, `gstn`, `order_status`, `user_id`, `gst_rate`, `gst_amount`, `shipping_cost`, `additional_charges`, `additional_discount_percent`, `additional_discount_amount`, `notes`, `delivery_date`, `product_id`, `product_name`) VALUES
(64, '2024-11-27', '', '', 0.00, 0.00, 649.00, 0.00, 0.00, 0.00, 0.00, 0, 0, 0, '', 0, 17, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(65, '2024-11-27', 'Nikko Mission', '09270533556', 550.00, 99.00, 649.00, 0.00, 649.00, 649.00, 0.00, 2, 1, 1, '99.00', 2, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(66, '2024-11-27', 'test', '123', 500.00, 90.00, 590.00, 0.00, 590.00, 590.00, 0.00, 2, 1, 1, '90.00', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(67, '2024-11-27', 'test', '123', 600.00, 108.00, 708.00, 0.00, 708.00, 708.00, 0.00, 2, 1, 1, '108.00', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(68, '2024-11-27', 'Nikko Mission', '09270533556', 500.00, 90.00, 590.00, 0.00, 590.00, 590.00, 0.00, 2, 1, 1, '90.00', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(69, '2024-11-27', 'Nikko Mission', '09270533556', 600.00, 108.00, 708.00, 0.00, 708.00, 708.00, 0.00, 1, 1, 1, '108.00', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(70, '2024-11-27', 'Nikko Mission', '09270533556', 600.00, 108.00, 708.00, 0.00, 708.00, 708.00, 0.00, 2, 1, 1, '108.00', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(71, '2024-11-27', 'Nikko Mission', '09270533556', 600.00, 108.00, 708.00, 0.00, 708.00, 708.00, 0.00, 2, 1, 1, '108.00', 2, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(72, '2024-11-27', 'Nikko Mission', '09270533556', 600.00, 108.00, 708.00, 0.00, 708.00, 708.00, 0.00, 2, 1, 1, '108.00', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(73, '2024-11-27', 'Nikko Mission', '09270533556', 60.00, 10.80, 70.80, 0.00, 70.80, 70.80, 0.00, 2, 1, 1, '10.80', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(74, '2024-11-27', 'Nikko Mission', '09270533556', 60.00, 10.80, 70.80, 0.00, 70.80, 70.80, 0.00, 2, 1, 1, '10.80', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(75, '2024-11-27', '123', '123', 100.00, 18.00, 118.00, 0.00, 118.00, 129.80, -11.80, 2, 1, 1, '18.00', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(76, '2024-11-27', '', '', 0.00, 0.00, 153.40, 0.00, 0.00, 0.00, 0.00, 0, 0, 0, '', 2, 32, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(77, '2024-11-27', 'Nikko', '09270533556', 550.00, 99.00, 649.00, 0.00, 649.00, 649.00, 0.00, 2, 1, 1, '99.00', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(78, '2024-11-28', '', '', 0.00, 0.00, 153.40, 0.00, 0.00, 0.00, 0.00, 0, 0, 0, '', 1, 38, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(79, '2024-11-28', 'test', '123123123', 130.00, 23.40, 153.40, 0.00, 153.40, 153.40, 0.00, 2, 1, 1, '23.40', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(80, '2024-12-02', 'Nikko', '1231231231', 600.00, 150.00, 0.00, 0.00, 750.00, 750.00, 0.00, 2, 1, 1, '150', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL),
(81, '2024-12-02', '123', '123', 260.00, 65.00, 0.00, 0.00, 325.00, 325.00, 0.00, 2, 1, 1, '65.00', 1, 1, NULL, NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `quantity` varchar(255) NOT NULL,
  `rate` varchar(255) NOT NULL,
  `total` varchar(255) NOT NULL,
  `order_item_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `order_id`, `product_id`, `quantity`, `rate`, `total`, `order_item_status`) VALUES
(49, 65, 20, '1', '550', '550.00', 1),
(50, 66, 37, '25', '100', '500.00', 1),
(51, 67, 37, '6', '100', '600.00', 1),
(52, 68, 37, '10', '100', '500.00', 1),
(53, 69, 37, '6', '100', '600.00', 1),
(54, 70, 37, '6', '100', '600.00', 1),
(55, 71, 37, '6', '100', '600.00', 1),
(56, 72, 37, '6', '100', '600.00', 1),
(57, 73, 37, '6', '10', '60.00', 1),
(58, 74, 37, '6', '10', '60.00', 1),
(59, 75, 37, '10', '10', '100.00', 1),
(60, 77, 20, '1', '550', '550.00', 1),
(61, 79, 24, '1', '130', '130.00', 1),
(62, 80, 21, '3', '200', '600', 1),
(63, 81, 17, '2', '130', '260.00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `tracking_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `status_message` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_proofs`
--

CREATE TABLE `payment_proofs` (
  `proof_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_proofs`
--

INSERT INTO `payment_proofs` (`proof_id`, `order_id`, `user_id`, `image_path`, `upload_date`, `status`, `admin_remarks`) VALUES
(14, 52, 5, 'uploads/payment_proofs/674454c072e83.jpg', '2024-11-25 10:43:12', 'verified', NULL),
(15, 55, 5, 'uploads/payment_proofs/674565f611498.jpg', '2024-11-26 06:08:54', 'verified', NULL),
(16, 56, 5, 'uploads/payment_proofs/6745665a5a602.jpg', '2024-11-26 06:10:34', 'verified', NULL),
(17, 57, 5, 'uploads/payment_proofs/67456689719e3.jpg', '2024-11-26 06:11:21', 'verified', NULL),
(18, 58, 17, 'uploads/payment_proofs/6746cf5f595b6.png', '2024-11-27 07:50:55', 'verified', NULL),
(19, 59, 17, 'uploads/payment_proofs/6746e0fc4b794.png', '2024-11-27 09:06:04', 'pending', NULL),
(20, 60, 17, 'uploads/payment_proofs/6746e79a08833.png', '2024-11-27 09:34:18', 'verified', NULL),
(24, 64, 17, 'uploads/payment_proofs/67470521a550c.jpg', '2024-11-27 11:40:17', 'pending', NULL),
(25, 76, 32, 'uploads/payment_proofs/674737f8f2920.jpg', '2024-11-27 15:17:12', 'verified', NULL),
(26, 78, 38, 'uploads/payment_proofs/674757cc5af16.jpg', '2024-11-27 17:33:00', 'verified', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` varchar(600) NOT NULL,
  `product_image` text NOT NULL,
  `brand_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `rate` varchar(255) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `description`, `product_image`, `brand_id`, `categories_id`, `quantity`, `rate`, `active`, `status`) VALUES
(17, 'Kapeng Barako', 'Kapeng Barako, the pride of Batangas, is a distinct variety of coffee (Coffea Liberica) known for its strong flavor and intense aroma. Grown in the rich volcanic soils of Batangas, this coffee variety has been part of Filipino culture for generations. Each cup delivers a bold, full-bodied taste with a distinctive smoky flavor and notes of dark chocolate. Perfect for traditional Filipino coffee preparation methods.\r\n\r\nFeatures:\r\n• 100% Pure Liberica Coffee\r\n• Strong, Full-bodied Flavor\r\n• Rich Aroma\r\n• Locally Grown in Batangas\r\n• Available in whole bean or ground options\r\n\r\nBrewing Suggestion:', '../assests/images/stock/560274716732b8fbb055a.jpg', 10, 12, '98', '130', 1, 1),
(20, 'Dried Fish', 'Premium Dried Fish (Tuyo) from the fishing communities of Batangas, carefully processed using traditional sun-drying methods passed down through generations. Our dried fish is selected from the freshest catch and prepared with the perfect balance of salt to enhance its natural flavors.\r\n\r\nFeatures:\r\n• Traditionally Sun-dried\r\n• Premium Quality Selection\r\n• Perfect Salt Balance\r\n• Rich in Protein and Calcium\r\n• Vacuum Sealed for Freshness\r\n\r\nServing Suggestion: Best served with fried rice and eggs for a traditional Filipino breakfast, or crumbled as a savory topping.', '../assests/images/stock/184470098167334f1a18f01.jpg', 13, 14, '586', '550', 1, 1),
(21, 'Lambanog', 'Authentic Batangas Lambanog, a traditional Filipino coconut wine distilled from the sap of coconut flowers. This artisanal spirit is crafted using time-honored methods, resulting in a pure, clean-tasting drink that represents the heritage of Batangas distilling.\r\n\r\nFeatures:\r\n• 100% Pure Coconut Sap\r\n• Traditional Distillation Process\r\n• Crystal Clear Appearance\r\n• Smooth, Clean Taste\r\n• No Artificial Additives\r\n\r\nServing Suggestion: Best served chilled or as a base for tropical cocktails.', '../assests/images/stock/2126617513673825b0a918d.jpg', 10, 12, '97', '200', 1, 1),
(22, 'sample', '', '../assests/images/stock/549978077673c38f641d68.jpg', 10, 12, '5', '5', 2, 2),
(24, 'Bagoong Balayan', 'Bagoong Balayan, a heritage product of Batangas, is a traditional fermented fish paste that has been produced in Balayan for centuries. This rich, flavorful condiment is made using anchovy and salt, naturally fermented to develop its complex taste profile.\r\n\r\nFeatures:\r\n• Traditional Fermentation Process\r\n• Rich Umami Flavor\r\n• No Artificial Preservatives\r\n• Authentic Balayan Recipe\r\n• Versatile Cooking Ingredient\r\n\r\nUsage: Perfect as a cooking ingredient, condiment, or dipping sauce for green mangoes.', '../assests/images/stock/417242946746ca6b61dd0.jpg', 10, 14, '99', '130', 1, 1),
(25, 'Suman', 'Suman, a beloved Filipino rice cake made from glutinous rice and coconut milk, carefully wrapped in banana leaves. This traditional delicacy is handcrafted following authentic Batangas recipes, resulting in a soft, chewy texture and subtle sweetness.\r\n\r\nFeatures:\r\n• Made with Premium Glutinous Rice\r\n• Fresh Coconut Milk\r\n• Natural Banana Leaf Wrapper\r\n• No Artificial Preservatives\r\n• Handcrafted Daily\r\n\r\nServing Suggestion: Best enjoyed with muscovado sugar or fresh mangoes.', '../assests/images/stock/14303574716746cabfdefc9.png', 10, 14, '100', '30', 1, 1),
(26, 'Kalamay', 'Kalamay, a traditional Filipino sticky rice delicacy made from glutinous rice, coconut milk, and brown sugar. Our version follows the authentic Batangas recipe, creating a rich, chewy treat with a deep caramel flavor.\r\n\r\nFeatures:\r\n• Premium Glutinous Rice\r\n• Pure Coconut Milk\r\n• Local Brown Sugar\r\n• Traditional Cooking Method\r\n• No Artificial Ingredients\r\n\r\nServing Suggestion: Best enjoyed as is or slightly warmed.', '../assests/images/stock/5561899156746cada37f3c.jpg', 10, 14, '100', '50', 1, 1),
(27, 'Puto', 'Traditional Batangas Puto, a fluffy Filipino rice cake made fresh daily. Our puto is crafted using premium rice flour and follows traditional recipes, resulting in a light, airy texture with just the right amount of sweetness.\r\n\r\nFeatures:\r\n• Made Fresh Daily\r\n• Premium Rice Flour\r\n• Perfect Sweetness\r\n• Soft and Fluffy Texture\r\n• No Artificial Preservatives\r\n\r\nServing Suggestion: Perfect with dinuguan or as a snack with butter.', '../assests/images/stock/20324463556746caf30f617.jpg', 10, 14, '100', '78', 1, 1),
(28, 'Sinukmani', 'Sinukmani (Biko), a traditional Filipino rice cake made with sticky rice, coconut milk, and brown sugar. This classic Batangas version is cooked slowly to develop a rich flavor and topped with latik (coconut caramel).\r\n\r\nFeatures:\r\n• Premium Sticky Rice\r\n• Fresh Coconut Milk\r\n• Local Brown Sugar\r\n• Traditional Latik Topping\r\n• Handcrafted in Small Batches\r\n\r\nServing Suggestion: Best served warm or at room temperature.', '../assests/images/stock/8584973786746cb071bf68.png', 14, 14, '100', '50', 1, 1),
(29, 'Lomi', 'Famous Batangas Lomi, a hearty Filipino-Chinese soup featuring thick noodles, various meats, and vegetables in a rich, flavorful broth. Our version follows the authentic Batangas recipe that has made this dish a local favorite.\r\n\r\nFeatures:\r\n• Fresh, Thick Noodles\r\n• Rich, Flavorful Broth\r\n• Quality Meat Ingredients\r\n• Fresh Vegetables\r\n• Generous Portions\r\n\r\nServing Suggestion: Best enjoyed piping hot with calamansi and chili garlic sauce.', '../assests/images/stock/12095941586746cb28c7e54.jpg', 10, 14, '100', '55', 1, 1),
(30, 'Kapeng Tablea', 'Kapeng Tablea, traditional Filipino chocolate tablets made from pure, locally-sourced cacao beans. Each tablet is carefully roasted and formed following time-honored methods to create the perfect base for traditional Filipino hot chocolate.\r\n\r\nFeatures:\r\n• 100% Pure Cacao\r\n• Traditional Roasting Method\r\n• No Additives\r\n• Rich Chocolate Flavor\r\n• Perfect for Tsokolate\r\n\r\nPreparation: Best prepared using traditional batidor (wooden whisk) for authentic Filipino hot chocolate.', '../assests/images/stock/6452214726746cb457d811.png', 10, 12, '100', '350', 1, 1),
(31, 'Coconut Wine', 'Premium Coconut Wine crafted from carefully selected coconut flowers. This refined version of traditional lambanog is produced using modern distillation techniques while maintaining traditional flavor profiles.\r\n\r\nFeatures:\r\n• Premium Coconut Flower Sap\r\n• Refined Distillation Process\r\n• Balanced Alcohol Content\r\n• Smooth Finish\r\n• Quality Controlled Production\r\n\r\nServing Suggestion: Best served chilled or in tropical cocktails.', '../assests/images/stock/8518519586746cba7e83dc.jpg', 10, 12, '100', '350', 1, 1),
(32, 'Honey', 'Pure, Natural Honey harvested from Batangas bee farms. Our honey is minimally processed to maintain its natural health benefits and unique flavor profiles from local flora.\r\n\r\nFeatures:\r\n• 100% Pure Honey\r\n• Raw and Unfiltered\r\n• No Artificial Additives\r\n• Local Floral Sources\r\n• Rich in Natural Enzymes\r\n\r\nUsage: Perfect as a natural sweetener, health supplement, or traditional remedy.', '../assests/images/stock/1474414466746cbf77b341.jpg', 14, 15, '100', '55', 1, 1),
(33, 'Luyang Dilaw', 'Fresh Luyang Dilaw (Turmeric) known for its potent anti-inflammatory and antioxidant properties. Our turmeric is locally grown in Batangas soil, ensuring maximum freshness and potency.\r\n\r\nFeatures:\r\n• Locally Grown\r\n• Fresh and Potent\r\n• Natural Anti-inflammatory\r\n• Rich in Curcumin\r\n• Versatile Usage\r\n\r\nUsage: Ideal for traditional medicine, cooking, and natural food coloring.', '../assests/images/stock/6718796126746cc1554c93.png', 14, 15, '100', '130', 1, 1),
(34, 'Saging na Saba', 'Premium Saging na Saba (Cardaba Bananas) carefully selected for optimal ripeness and quality. These versatile cooking bananas are perfect for both traditional Filipino desserts and savory dishes.\r\n\r\nFeatures:\r\n• Carefully Selected\r\n• Perfect Ripeness\r\n• Versatile Usage\r\n• Natural Ripening\r\n• No Artificial Ripening Agents\r\n\r\nUsage: Ideal for traditional Filipino desserts like minatamis na saging or savory dishes like pochero.', '../assests/images/stock/9735504686746cc2e78a78.jpg', 14, 15, '100', '130', 1, 1),
(37, 'test', '', '0', 10, 12, '5', '10', 1, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_average_ratings`
-- (See below for the actual view)
--
CREATE TABLE `product_average_ratings` (
`product_id` int(11)
,`product_name` varchar(255)
,`average_rating` decimal(12,1)
,`total_ratings` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `product_feedback`
--

CREATE TABLE `product_feedback` (
  `feedback_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `feedback_text` text NOT NULL,
  `feedback_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','hidden') NOT NULL DEFAULT 'active',
  `helpful_count` int(11) NOT NULL DEFAULT 0,
  `report_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_feedback`
--

INSERT INTO `product_feedback` (`feedback_id`, `product_id`, `user_id`, `feedback_text`, `feedback_date`, `status`, `helpful_count`, `report_count`) VALUES
(11, 17, 5, 'Would love to see larger package sizes available for this coffee.', '2024-11-27 07:39:50', 'active', 3, 0),
(12, 20, 1, 'Great product but could improve packaging for longer freshness.', '2024-11-27 07:39:50', 'active', 2, 0),
(13, 21, 5, 'Perfect size bottle for gatherings. Would recommend secure packaging for shipping.', '2024-11-27 07:39:50', 'active', 4, 0),
(14, 24, 1, 'Consistent quality across multiple purchases. Great product!', '2024-11-27 07:39:50', 'active', 5, 0),
(15, 25, 5, 'Always fresh and well-wrapped. Never disappoints.', '2024-11-27 07:39:50', 'active', 3, 0),
(16, 29, 1, 'Generous portions and authentic taste. Worth every peso!', '2024-11-27 07:39:50', 'active', 6, 0),
(17, 30, 5, 'Makes perfect traditional tsokolate. Would love recipe suggestions included.', '2024-11-27 07:39:50', 'active', 4, 0),
(18, 31, 1, 'Good quality but could use better sealing for freshness.', '2024-11-27 07:39:50', 'active', 2, 0),
(19, 32, 5, 'Pure and natural taste. Appreciate the quality control.', '2024-11-27 07:39:50', 'active', 5, 0),
(20, 33, 1, 'Fresh and potent. Great for traditional remedies.', '2024-11-27 07:39:50', 'active', 3, 0),
(21, 24, 17, 'test', '2024-11-27 09:18:04', 'active', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_ratings`
--

CREATE TABLE `product_ratings` (
  `rating_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stars` int(1) NOT NULL CHECK (`stars` >= 1 and `stars` <= 5),
  `rating_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_ratings`
--

INSERT INTO `product_ratings` (`rating_id`, `product_id`, `user_id`, `stars`, `rating_date`) VALUES
(1, 17, 1, 5, '2024-01-15 00:30:00'),
(2, 17, 5, 4, '2024-01-16 01:15:00'),
(3, 20, 1, 4, '2024-01-17 02:20:00'),
(4, 20, 5, 5, '2024-01-18 03:45:00'),
(5, 21, 1, 5, '2024-01-19 05:30:00'),
(6, 21, 5, 4, '2024-01-20 06:25:00'),
(7, 24, 1, 5, '2024-01-21 07:40:00'),
(8, 24, 5, 5, '2024-01-22 08:55:00'),
(9, 25, 1, 4, '2024-01-23 09:20:00'),
(10, 25, 5, 5, '2024-01-24 10:35:00'),
(11, 26, 1, 5, '2024-01-25 11:40:00'),
(12, 26, 5, 4, '2024-01-26 12:15:00'),
(13, 27, 1, 4, '2024-01-27 13:30:00'),
(14, 27, 5, 5, '2024-01-28 14:45:00'),
(15, 28, 1, 5, '2024-01-29 15:50:00'),
(16, 28, 5, 4, '2024-01-29 16:55:00'),
(17, 29, 1, 5, '2024-01-30 17:20:00'),
(18, 29, 5, 5, '2024-01-31 18:35:00'),
(19, 30, 1, 4, '2024-02-01 19:40:00'),
(20, 30, 5, 5, '2024-02-02 20:45:00'),
(21, 31, 1, 4, '2024-02-03 21:50:00'),
(22, 31, 5, 5, '2024-02-04 22:55:00'),
(23, 32, 1, 5, '2024-02-05 23:20:00'),
(24, 32, 5, 5, '2024-02-07 00:25:00'),
(25, 33, 1, 4, '2024-02-08 01:30:00'),
(26, 33, 5, 5, '2024-02-09 02:35:00'),
(27, 34, 1, 5, '2024-02-10 03:40:00'),
(28, 34, 5, 4, '2024-02-11 04:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text NOT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`review_id`, `product_id`, `user_id`, `rating`, `review_text`, `review_date`, `status`, `admin_response`) VALUES
(11, 17, 5, 5, 'Excellent coffee! The strong aroma and rich flavor are exactly what you expect from authentic Batangas Barako.', '2024-11-27 07:39:50', 'approved', NULL),
(12, 17, 1, 4, 'Great traditional coffee. Perfect morning brew with strong kick.', '2024-11-27 07:39:50', 'approved', NULL),
(13, 20, 5, 5, 'Very high quality dried fish. Perfect size and not too salty.', '2024-11-27 07:39:50', 'approved', NULL),
(14, 21, 1, 4, 'Smooth and clean-tasting lambanog. Great representation of traditional Filipino spirits.', '2024-11-27 07:39:50', 'approved', NULL),
(15, 24, 5, 5, 'The best bagoong I\'ve tried! Rich flavor that enhances any dish.', '2024-11-27 07:39:50', 'approved', NULL),
(16, 25, 1, 5, 'Soft, fresh, and perfectly sweet. Reminds me of my childhood.', '2024-11-27 07:39:50', 'approved', NULL),
(17, 29, 5, 5, 'Authentic Batangas Lomi! The serving size is generous and the taste is incredible.', '2024-11-27 07:39:50', 'approved', NULL),
(18, 30, 1, 4, 'Rich and dark tablea, makes perfect traditional hot chocolate.', '2024-11-27 07:39:50', 'approved', NULL),
(19, 31, 5, 4, 'Smooth coconut wine with great flavor. Not too strong.', '2024-11-27 07:39:50', 'approved', NULL),
(20, 32, 1, 5, 'Pure and natural honey. You can really taste the difference!', '2024-11-27 07:39:50', 'approved', NULL),
(21, 24, 17, 5, 'test', '2024-11-27 09:16:19', 'pending', NULL),
(22, 34, 17, 1, 'test', '2024-11-27 08:36:08', 'approved', NULL),
(23, 24, 38, 5, 'test', '2024-11-27 17:28:44', 'approved', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text NOT NULL,
  `return_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `order_id`, `product_id`, `quantity`, `reason`, `return_date`, `status`, `remarks`) VALUES
(1, 18, 17, 5, 'Sample  ', '2024-11-17 02:09:34', 'approved', NULL),
(2, 26, 21, 5, 'na', '2024-11-19 06:31:25', 'rejected', NULL),
(3, 26, 21, 5, 'na', '2024-11-19 07:05:18', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `review_images`
--

CREATE TABLE `review_images` (
  `image_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_images`
--

INSERT INTO `review_images` (`image_id`, `review_id`, `image_path`, `upload_date`) VALUES
(1, 1, 'assets/images/reviews/kapeng-barako-review1.jpg', '2024-01-15 00:35:00'),
(2, 1, 'assets/images/reviews/kapeng-barako-brewing.jpg', '2024-01-15 00:36:00'),
(3, 2, 'assets/images/reviews/barako-package.jpg', '2024-01-20 06:20:00'),
(4, 3, 'assets/images/reviews/traditional-brew.jpg', '2024-02-01 01:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(120) NOT NULL,
  `role` varchar(256) NOT NULL,
  `profile_picture` varchar(256) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `phone_number`, `role`, `profile_picture`, `reset_token`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@example.com', '0', 'admin', '', NULL),
(37, 'Nikko', '202cb962ac59075b964b07152d234b70', 'nikko.mission09@gmail.com', '09270533556', 'user', 'uploads/profiles/674747256cdf1.png', NULL),
(38, 'test', '202cb962ac59075b964b07152d234b70', 'nikko.mission@gmail.com', '09270533554', 'user', 'uploads/profiles/67475a8c21a11.png', NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_user_register_notification` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_notifications 
    (user_id, title, message, type, is_read) 
    VALUES 
    (NEW.user_id, 
     'Welcome to Balai Batangas!', 
     'Thank you for joining our community! We''re excited to have you explore our authentic Batangas products. Enjoy shopping with us!',
     'welcome',
     0);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receiver_name` varchar(255) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`address_id`, `user_id`, `receiver_name`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `phone`, `is_default`) VALUES
(2, 5, 'MISSION, PAUL NIKKO D.', 'anilao labac lipa batangas', '', 'batangas', 'Manila', '4217', '09270533556', 1),
(3, 17, 'MISSION, PAUL NIKKO D.', 'anilao labac lipa batangas', '', 'batangas', 'Manila', '4217', '09270533556', 1),
(4, 32, 'MISSION, PAUL NIKKO D.', 'anilao labac lipa batangas', '', 'batangas', 'Manila', '4217', '09270533556', 1),
(5, 37, 'MISSION, PAUL NIKKO D.', 'anilao labac lipa batangas', '', 'batangas', 'Manila', '4217', '09270533556', 1),
(6, 38, 'MISSION, PAUL NIKKO D.', 'anilao labac lipa batangas', '', 'batangas', 'Manila', '4217', '09270533556', 1),
(7, 38, '123', '123123', '', '123123', '123123', '123123', '123123123', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

CREATE TABLE `user_details` (
  `detail_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`detail_id`, `user_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `phone_number`, `address`, `created_at`) VALUES
(20, 37, 'Paul Nikko', 'Mission', '2002-02-09', 'male', '09270533556', 'anilao labac lipa batangas', '2024-11-27 16:21:30'),
(21, 38, 'test', 'test', '2002-09-09', 'male', '09270533554', 'anilao labac lipa batangas', '2024-11-27 17:14:00');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `type` enum('welcome','order','system','alert') NOT NULL DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`notification_id`, `user_id`, `title`, `message`, `reference_id`, `type`, `is_read`, `created_at`) VALUES
(48, 1, 'Welcome to Balai Batangas', 'Welcome to our online store! Thank you for joining.', NULL, 'welcome', 0, '2024-11-27 07:57:28'),
(49, 5, 'Welcome to Balai Batangas', 'Welcome to our online store! Thank you for joining.', NULL, 'welcome', 0, '2024-11-27 07:57:28'),
(50, 6, 'Welcome to Balai Batangas', 'Welcome to our online store! Thank you for joining.', NULL, 'welcome', 0, '2024-11-27 07:57:28'),
(51, 14, 'Welcome to Balai Batangas', 'Welcome to our online store! Thank you for joining.', NULL, 'welcome', 0, '2024-11-27 07:57:28'),
(52, 15, 'Welcome to Balai Batangas', 'Welcome to our online store! Thank you for joining.', NULL, 'welcome', 0, '2024-11-27 07:57:28'),
(53, 17, 'Welcome to Balai Batangas', 'Welcome to our online store! Thank you for joining.', NULL, 'welcome', 1, '2024-11-27 07:57:28'),
(55, 18, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 09:21:39'),
(56, 19, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 13:49:10'),
(58, 21, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 14:01:34'),
(60, 23, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 14:11:17'),
(61, 24, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 14:14:44'),
(63, 26, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 14:20:43'),
(65, 28, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 14:24:24'),
(66, 29, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 14:25:44'),
(67, 30, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 14:29:29'),
(69, 32, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 15:04:01'),
(70, 33, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 15:40:10'),
(72, 35, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 16:04:57'),
(74, 37, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 16:21:30'),
(75, 38, 'Welcome to Balai Batangas!', 'Thank you for joining our community! We\'re excited to have you explore our authentic Batangas products. Enjoy shopping with us!', NULL, 'welcome', 0, '2024-11-27 17:14:00');

-- --------------------------------------------------------

--
-- Structure for view `product_average_ratings`
--
DROP TABLE IF EXISTS `product_average_ratings`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_average_ratings`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_name` AS `product_name`, round(avg(`pr`.`stars`),1) AS `average_rating`, count(`pr`.`rating_id`) AS `total_ratings` FROM (`product` `p` left join `product_ratings` `pr` on(`p`.`product_id` = `pr`.`product_id`)) GROUP BY `p`.`product_id`, `p`.`product_name` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categories_id`);

--
-- Indexes for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback_reactions`
--
ALTER TABLE `feedback_reactions`
  ADD PRIMARY KEY (`reaction_id`),
  ADD UNIQUE KEY `unique_reaction` (`feedback_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `library`
--
ALTER TABLE `library`
  ADD PRIMARY KEY (`festival_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`tracking_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD PRIMARY KEY (`proof_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_feedback`
--
ALTER TABLE `product_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_user_product_rating` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `review_images`
--
ALTER TABLE `review_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categories_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback_reactions`
--
ALTER TABLE `feedback_reactions`
  MODIFY `reaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `library`
--
ALTER TABLE `library`
  MODIFY `festival_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  MODIFY `proof_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `product_feedback`
--
ALTER TABLE `product_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_ratings`
--
ALTER TABLE `product_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `review_images`
--
ALTER TABLE `review_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_details`
--
ALTER TABLE `user_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD CONSTRAINT `customer_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
