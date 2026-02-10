-- Seed data for table: advantages
-- Generated at: 2026-01-22 15:39:16
-- Rows: 5

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `advantages`;

INSERT INTO `advantages` (`id`, `name`, `text`, `image`, `rate`, `show`) VALUES
  (1, 'Надёжно', 'Мировые производители сырья. Каждая позиция сопровождается сертификатом и паспортом.', '/public/src/images/advantages/6964dbeeb953d.svg', 0, 1),
  (2, 'Удобно', 'Все ингредиенты на одном сайте. Не переплачивайте за доставку сырья от разных поставщиков.', '/public/src/images/advantages/6964dc1c3757f.svg', 0, 1),
  (3, 'Легко', '24/7 заказ из on-line каталога в 3 шага. Все варианты оплаты.', '/public/src/images/advantages/6964dc3a5cd5e.svg', 0, 1),
  (4, 'Быстро', 'Обработка заказа в день обращения. Ежедневно доставка курьером или самовывоз.', '/public/src/images/advantages/6964dc54e7458.svg', 0, 1),
  (5, 'Системно', 'Мы заинтересованы в росте вашего бизнеса и ценим страсть к открытиям. Предоставляем образцы сырья для тестирования.', '/public/src/images/advantages/6964dc9b8f644.svg', 0, 1);

SET FOREIGN_KEY_CHECKS = 1;
