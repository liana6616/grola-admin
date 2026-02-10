-- Seed data for table: users_ip
-- Generated at: 2026-01-22 15:39:16
-- Rows: 3

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `users_ip`;

INSERT INTO `users_ip` (`id`, `name`, `text`) VALUES
  (1, '127.0.0.1', 'Локальный'),
  (2, '91.234.152.117', 'Наш офисный'),
  (3, '*', 'Все IP адреса');

SET FOREIGN_KEY_CHECKS = 1;
