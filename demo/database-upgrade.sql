USE `pinkcms`;

ALTER TABLE `posts`
  ADD COLUMN `code` VARCHAR(100) DEFAULT '' AFTER `id`,
  ADD COLUMN `actorEN` VARCHAR(150) DEFAULT '' AFTER `actorIds`,
  ADD COLUMN `category` VARCHAR(150) DEFAULT '' AFTER `tagIds`,
  ADD COLUMN `focusKeyphrase` VARCHAR(200) DEFAULT '' AFTER `category`;

ALTER TABLE `posts`
  ADD COLUMN `embedUrl` VARCHAR(1000) DEFAULT '' AFTER `videoUrl`;

ALTER TABLE `posts`
  ADD COLUMN `posterUrl` VARCHAR(1000) DEFAULT '' AFTER `cover`;

ALTER TABLE `posts`
  ADD COLUMN `author` VARCHAR(100) DEFAULT '' AFTER `date`,
  ADD COLUMN `createdAt` DATETIME DEFAULT NULL AFTER `author`;
