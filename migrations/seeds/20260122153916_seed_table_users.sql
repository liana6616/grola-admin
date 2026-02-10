-- Seed data for table: users
-- Generated at: 2026-01-22 15:39:16
-- Rows: 2

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `users`;

INSERT INTO `users` (`id`, `login`, `password`, `name`, `phone`, `class_id`, `date`, `date_visit`, `hash`) VALUES
  (1, 'support', '$2y$12$awpSEDAivmJFUyijZiIz8OAOqWvHsmVQDD/jY6GRJahSk7xi8J.va', 'Андрей', '+7', 1, 1748093726, 1769082568, '61fd3ff760da777ec25cec4001deec5e'),
  (12, 'test@test.ru', '$2y$12$cKzOPbXr6Vl2ISGwpRN4kOYkv1W8Irka6F7waKT45ihlCnJZGEpwm', NULL, NULL, 1, 1768381584, 1768388388, '26318671662fe27c64edd2cd4eeb4240');

SET FOREIGN_KEY_CHECKS = 1;
