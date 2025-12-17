-- Schéma de base de données pour CookBook (niveau BTS SIO)

CREATE DATABASE IF NOT EXISTS cook_book
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cook_book;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) DEFAULT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  pwd VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des catégories (optionnel mais simple)
CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Table des recettes
CREATE TABLE IF NOT EXISTS recipes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  ingredients TEXT NOT NULL,
  steps TEXT NOT NULL,
  image_path VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_recipes_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_recipes_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;


