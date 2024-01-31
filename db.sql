-- Active: 1702975947841@@localhost@3308@ocp5
SET
  time_zone = "+01:00";

SET
  default_storage_engine = InnoDB;

DROP DATABASE IF EXISTS `ocp5`;

CREATE DATABASE IF NOT EXISTS `ocp5` DEFAULT CHARACTER
SET
  utf8mb4 COLLATE utf8mb4_general_ci;

USE `ocp5`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255),
  `email` VARCHAR(255),
  `emailVerificationToken` CHAR(21) DEFAULT NULL,
  `emailVerified` TINYINT (1) DEFAULT 0,
  `password` VARCHAR(255),
  `passwordResetToken` CHAR(21) DEFAULT NULL,
  `admin` TINYINT (1) DEFAULT 0,
  `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE (`email`),
  KEY (`email`, `password`),
  KEY (`id`, `name`)
);

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50),
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255),
  `leadParagraph` VARCHAR(255),
  `body` TEXT,
  `author` INT,
  `category` INT,
  `published` TINYINT (1) DEFAULT 1,
  `commentsAllowed` TINYINT(1) DEFAULT 1,
  `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_posts_author` FOREIGN KEY (`author`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_posts_category` FOREIGN KEY (`category`) REFERENCES `categories` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  KEY (`published`)
);

CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `postId` INT NOT NULL,
  `title` VARCHAR(255),
  `body` TEXT,
  `author` INT,
  `approved` TINYINT (1) DEFAULT 0,
  `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_comments_postId` FOREIGN KEY (`postId`) REFERENCES `posts` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_comments_author` FOREIGN KEY (`author`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  KEY (`approved`)
);
