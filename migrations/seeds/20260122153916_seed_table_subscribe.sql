-- Seed data for table: subscribe
-- Generated at: 2026-01-22 15:39:16
-- Rows: 1

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `subscribe`;

INSERT INTO `subscribe` (`id`, `date`, `user_id`, `email`, `active`, `ip`) VALUES
  (1, 1768222449, 1, 'test@test.ru', 1, '91.234.152.117');

SET FOREIGN_KEY_CHECKS = 1;
