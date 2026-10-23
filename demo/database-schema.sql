CREATE DATABASE IF NOT EXISTS `pinkcms_demo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pinkcms_demo`;

CREATE TABLE IF NOT EXISTS `settings` (
  `skey` VARCHAR(50) NOT NULL,
  `svalue` TEXT DEFAULT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `studios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `logo` VARCHAR(255) DEFAULT '',
  `desc` TEXT DEFAULT NULL,
  `metaTitle` VARCHAR(200) DEFAULT '',
  `metaDesc` VARCHAR(300) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `actors` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `photo` VARCHAR(255) DEFAULT '',
  `bio` TEXT DEFAULT NULL,
  `metaTitle` VARCHAR(200) DEFAULT '',
  `metaDesc` VARCHAR(300) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tags` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `metaTitle` VARCHAR(200) DEFAULT '',
  `metaDesc` VARCHAR(300) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) DEFAULT '',
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `content` TEXT DEFAULT NULL,
  `cover` VARCHAR(255) DEFAULT '',
  `posterUrl` VARCHAR(1000) DEFAULT '',
  `videoUrl` TEXT DEFAULT NULL,
  `embedUrl` VARCHAR(1000) DEFAULT '',
  `videoDuration` VARCHAR(20) DEFAULT '',
  `gallery` TEXT DEFAULT NULL,
  `studioId` INT DEFAULT 0,
  `actorIds` TEXT DEFAULT NULL,
  `actorEN` VARCHAR(150) DEFAULT '',
  `tagIds` TEXT DEFAULT NULL,
  `category` VARCHAR(150) DEFAULT '',
  `focusKeyphrase` VARCHAR(200) DEFAULT '',
  `metaTitle` VARCHAR(200) DEFAULT '',
  `metaDesc` VARCHAR(300) DEFAULT '',
  `keywords` VARCHAR(300) DEFAULT '',
  `status` VARCHAR(20) DEFAULT 'draft',
  `date` DATE DEFAULT NULL,
  `author` VARCHAR(100) DEFAULT '',
  `createdAt` DATETIME DEFAULT NULL,
  `views` INT DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `link` VARCHAR(255) DEFAULT '',
  `image` VARCHAR(255) DEFAULT '',
  `text` VARCHAR(255) DEFAULT '',
  `active` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `content` TEXT DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT 'publish',
  `date` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `postId` INT NOT NULL,
  `name` VARCHAR(100) DEFAULT 'ผู้เยี่ยมชม',
  `text` TEXT NOT NULL,
  `date` DATE DEFAULT NULL,
  `approved` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) DEFAULT 'ผู้ดูแลระบบ',
  `lastLogin` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(255) NOT NULL,
  `time` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
