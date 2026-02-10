-- Seed data for table: settings
-- Generated at: 2026-01-22 15:39:16
-- Rows: 1

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `settings`;

INSERT INTO `settings` (`id`, `sitename`, `copyright`, `email_sends`, `email`, `phone`, `phone2`, `postcode`, `region`, `city`, `address`, `coords`, `company`, `requisites`, `image`) VALUES
  (1, 'LINDERA', 'LINDERA', '_best@mail.ru', 'info@lindera.ru', '8 (800) 777-50-70', '+7 (812) 242-86-88', '195269', 'Северо-Западный федеральный округ', 'Санкт-Петербург', 'ул. Учительская, д. 23а', 'whatsapp://send?phone=+79006339447', 'LINDERA', '', '');

SET FOREIGN_KEY_CHECKS = 1;
