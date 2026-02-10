-- Seed data for table: users_class
-- Generated at: 2026-01-22 15:39:16
-- Rows: 2

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `users_class`;

INSERT INTO `users_class` (`id`, `name`) VALUES
  (1, 'Админ'),
  (2, 'Клиент');

SET FOREIGN_KEY_CHECKS = 1;
