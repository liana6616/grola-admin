-- Seed data for table: faq_sections
-- Generated at: 2026-01-22 15:39:16
-- Rows: 4

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `faq_sections`;

INSERT INTO `faq_sections` (`id`, `name`, `rate`, `show`) VALUES
  (1, 'Ингредиенты', 0, 1),
  (2, 'Продукция', 0, 1),
  (3, 'Доставка, оплата и возврат', 0, 1),
  (4, 'Акции и лояльность', 0, 1);

SET FOREIGN_KEY_CHECKS = 1;
