-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           8.4.3 - MySQL Community Server - GPL
-- SE du serveur:                Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Listage de la structure de table cook_book. categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table cook_book.categories : ~9 rows (environ)
INSERT INTO `categories` (`id`, `name`) VALUES
	(4, 'Boisson'),
	(3, 'Dessert'),
	(1, 'Entrée'),
	(7, 'Petit-déjeuner'),
	(2, 'Plat principal'),
	(9, 'Sauce'),
	(8, 'Snack'),
	(6, 'Vegan'),
	(5, 'Végétarien');

-- Listage de la structure de table cook_book. comments
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `recipe_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_comments_recipe` (`recipe_id`),
  KEY `fk_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table cook_book.comments : ~8 rows (environ)
INSERT INTO `comments` (`id`, `recipe_id`, `user_id`, `content`, `created_at`) VALUES
	(1, 7, 25, 'Super recette, super bonne et rapide !', '2026-01-07 13:57:05'),
	(2, 8, 24, 'Quel regale !', '2026-01-07 14:00:20'),
	(3, 9, 23, 'Miam, je recommande.', '2026-01-07 14:02:39'),
	(4, 10, 22, 'Super conviviale pour un apero', '2026-01-07 14:05:16'),
	(5, 11, 19, 'Incroyable !', '2026-01-13 08:08:50'),
	(6, 12, 17, 'Excellente recette ! Je me suis régalée !', '2026-01-13 08:12:48'),
	(7, 13, 18, 'Miam', '2026-01-13 08:18:30'),
	(8, 14, 20, 'J\'ai adoré !', '2026-01-13 08:31:41');

-- Listage de la structure de table cook_book. messages
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` int unsigned NOT NULL,
  `receiver_id` int unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_messages_sender` (`sender_id`),
  KEY `fk_messages_receiver` (`receiver_id`),
  CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table cook_book.messages : ~0 rows (environ)
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `content`, `is_read`, `created_at`) VALUES
	(1, 17, 25, 0, '2026-01-13 08:13:32');

-- Listage de la structure de table cook_book. recipes
CREATE TABLE IF NOT EXISTS `recipes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ingredients` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `steps` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_recipes_user` (`user_id`),
  KEY `fk_recipes_category` (`category_id`),
  CONSTRAINT `fk_recipes_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_recipes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table cook_book.recipes : ~9 rows (environ)
INSERT INTO `recipes` (`id`, `user_id`, `category_id`, `title`, `description`, `ingredients`, `steps`, `image_path`, `created_at`, `updated_at`) VALUES
	(7, 26, 2, 'Pates à l\'ail', 'Une recette italienne simple et rapide, parfaite quand on manque de temps.', '200 g de pâtes\n2 gousses d’ail\n3 cuillères à soupe d’huile d’olive\nSel\nPoivre', 'Faire cuire les pâtes dans une grande casserole d’eau salée.\nÉmincer l’ail finement.\nFaire chauffer l’huile d’olive à feu doux et ajouter l’ail.\nÉgoutter les pâtes et les mélanger avec l’huile à l’ail.\nSaler, poivrer et servir chaud.', '/CookBook/public/uploads/recipe_1767794007_5401.jpg', '2026-01-07 13:53:27', NULL),
	(8, 25, 2, 'Omelette nature', 'Une omelette rapide et savoureuse pour un repas léger.', '3 œufs\n1 noisette de beurre\nSel\nPoivre', 'Battre les œufs dans un bol.\nSaler et poivrer.\nFaire fondre le beurre dans une poêle.\nVerser les œufs et cuire à feu moyen.\nReplier l’omelette et servir.', '/CookBook/public/uploads/recipe_1767794205_1943.jpg', '2026-01-07 13:56:45', NULL),
	(9, 24, 1, 'Salade de tomates', 'Une salade fraîche et rapide, idéale en été.', '3 tomates\n1 cuillère à soupe d’huile d’olive\n1 cuillère à café de vinaigre\nSel\nBasilic (optionnel)', 'Laver et couper les tomates.\nLes placer dans un saladier.\nAjouter l’huile et le vinaigre.\nSaler et mélanger.\nAjouter le basilic avant de servir.', '/CookBook/public/uploads/recipe_1767794401_4879.jpg', '2026-01-07 14:00:01', NULL),
	(10, 23, 1, 'Tartines au fromage', 'Une recette simple et rapide pour un repas express.', '2 tranches de pain\n100 g de fromage râpé\nBeurre', 'Beurrer les tranches de pain.\nAjouter le fromage râpé.\nPasser au four à 180°C pendant 10 minutes.\nServir chaud.', '/CookBook/public/uploads/recipe_1767794543_4566.jpg', '2026-01-07 14:02:23', NULL),
	(11, 22, 3, 'Gâteau au yaourt', 'Un gâteau facile à réaliser, même pour les débutants.', '1 yaourt nature\n2 pots de sucre\n3 pots de farine\n3 œufs\n1 sachet de levure\n1/2 pot d’huile', 'Verser le yaourt dans un saladier.\nAjouter le sucre, la farine et la levure.\nIncorporer les œufs et l’huile.\nMélanger jusqu’à obtenir une pâte lisse.\nCuire au four à 180°C pendant 35 minutes.', '/CookBook/public/uploads/recipe_1767794695_3716.jpg', '2026-01-07 14:04:55', NULL),
	(12, 19, 2, 'Croque-monsieur', 'Un classique rapide et gourmand.', '2 tranches de pain de mie\n1 tranche de jambon\nFromage râpé\nBeurre', 'Beurrer les tranches de pain.\nAjouter le jambon et le fromage.\nRefermer le sandwich.\nCuire à la poêle ou au four jusqu’à ce qu’il soit doré.', '/CookBook/public/uploads/recipe_1768291707_6708.jpg', '2026-01-13 08:08:27', NULL),
	(13, 17, 4, 'Smoothie banane', 'Un smoothie rapide et énergisant.', '1 banane\n1 yaourt nature\n1 verre de lait', 'Couper la banane.\nMettre tous les ingrédients dans un mixeur.\nMixer jusqu’à obtenir une texture lisse.\nServir frais.', '/CookBook/public/uploads/recipe_1768291887_1693.jpg', '2026-01-13 08:11:27', NULL),
	(14, 18, 3, 'Compote de pommes', 'Une compote maison facile et saine.', '3 pommes\n1 verre d’eau\nSucre (optionnel)', 'Éplucher et couper les pommes.\nLes mettre dans une casserole avec l’eau.\nCuire à feu doux 15 minutes.\nMixer ou écraser selon la texture souhaitée.', '/CookBook/public/uploads/recipe_1768292297_9252.jpg', '2026-01-13 08:18:17', NULL),
	(15, 20, 7, 'Oeufs brouillés', 'Un petit-déjeuner ou repas express.', '3 œufs\n1 noisette de beurre\nSel\nPoivre', 'Battre les œufs.\nFaire fondre le beurre à feu doux.\nAjouter les œufs.\nRemuer doucement jusqu’à cuisson souhaitée.', '/CookBook/public/uploads/recipe_1768293072_9475.jpg', '2026-01-13 08:31:12', '2026-01-13 08:50:16');

-- Listage de la structure de table cook_book. users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pwd` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table cook_book.users : ~11 rows (environ)
INSERT INTO `users` (`id`, `name`, `email`, `pwd`, `role`, `created_at`, `active`) VALUES
	(1, 'MattP', 'matthieuperry94@gmail.com', '$2y$10$742iHc5FNGzSFS8OYxn5XOSDaTWr4dVbL70YVEdtVUvfj78j7HZQ2', 'admin', '2025-12-17 10:56:43', 1),
	(15, 'Julie Martin', 'julie.martin@gmail.com', '$2y$10$VTQW24QnMpSBCnz5FxlsdOZ80WTF.x4cQyTUemqknRCSxpcGPKbBC', 'user', '2026-01-07 13:16:27', 1),
	(16, 'Thomas Leroy', 'thomas.leroy@gmail.com', '$2y$10$6.s4fJ2XwFYqYhXrGaocge16Du1RznVEDPH3u.VKZ/XVS6o7Bfw4m', 'user', '2026-01-07 13:16:52', 1),
	(17, 'Camille Dubois', 'camille.dubois@gmail.com', '$2y$10$GCgYYkv9.0Fay.tIT3BRveBBA9HPHlIKgBhwiz7EJ04zfVbhOZu9S', 'user', '2026-01-07 13:21:05', 1),
	(18, 'Antoine Bernard', 'antoine.bernard@gmail.com', '$2y$10$o8X3iXRat6LzKyTY9hFvTOayifDqUgnwNM9cQ44oX1WqnqGI/59Qq', 'user', '2026-01-07 13:21:33', 1),
	(19, 'Sophie Laurent', 'sophie.laurent@gmail.com', '$2y$10$piPVlZpBVD/X2I7EnXzORu7oxj0OJ5biOJOuPVllZV7UmJNlb2qT.', 'user', '2026-01-07 13:22:10', 1),
	(20, 'Maxime Petit', 'maxime.petit@gmail.com', '$2y$10$x/cqO/yC.slrHyj0HJS/x.WMZ3hxw09ce8giC9obzlCwqjPJaluG6', 'user', '2026-01-07 13:22:33', 1),
	(22, 'Emilie Roux', 'emilie.roux@gmail.com', '$2y$10$bW246gechLOSE.j.Ifa.IugqXwPR12BGxK9EZJvQOvVGBDPoKs9F2', 'user', '2026-01-07 13:26:08', 1),
	(23, 'Manon Lefevre', 'manon.lefevre@gmail.com', '$2y$10$7SvL0Ht/8NDg96CvXidmw.FYpnaLL0tUChgIbx3VcinvGxsH6kNpm', 'user', '2026-01-07 13:26:32', 1),
	(24, 'Alexis Girard', 'alexis.girad@gmail.com', '$2y$10$G3hpuvkV7CI1hqzr/o3al.w/gYMwzvznfCxoNhf92yyZky4oTX6iy', 'user', '2026-01-07 13:28:04', 1),
	(25, 'Claire Robert', 'claire.robert@gmail.com', '$2y$10$4rHJrxAFqhqjsXHWRkkqM.UOhk034ksRfAb32k780NxoG6JUm6sGy', 'user', '2026-01-07 13:28:33', 1),
	(26, 'Romain Colin', 'romain.colin@gmail.com', '$2y$10$lQAu6yom/vJKCUxcmJl4seV9lQ09yWdYWdVIo/cLs/aT8Om32mdfW', 'user', '2026-01-07 13:29:00', 1),
	(27, 'perrym', 'matthieu@gmail.com', '$2y$10$7vz1csP/6bv.mJoOaZAKQ.7MUFf48CuAFlWvSMSsd0VOaT0lqrCVq', 'user', '2026-01-21 15:55:00', 1),
	(28, 'MattP', 'mattp@examplee.com', '$2y$10$K2IwplEPtBREhZHqUh5C4umMyHwRlIw7yXjfrurwmLKHqraLhVbhO', 'admin', '2026-03-11 07:30:19', 1),
	(29, 'MattP', 'mattp@perry.com', '$2y$10$ekmr.DQdDbX6C/NSlKAWUeSilY9r78SHN1ycr9Bk9fCssliuw6oNu', 'admin', '2026-03-11 07:33:26', 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
