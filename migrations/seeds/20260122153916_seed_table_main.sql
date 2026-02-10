-- Seed data for table: main
-- Generated at: 2026-01-22 15:39:16
-- Rows: 1

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `main`;

INSERT INTO `main` (`id`, `num1`, `txt1`, `num2`, `txt2`, `num3`, `txt3`, `about_text`, `about_name`, `about_position`, `about_image`, `about_btn`, `info_text`, `info_image`, `faq_name`, `faq_text`, `opt_name`, `opt_text`, `opt_image`) VALUES
  (1, '11', 'лет на рынке', '11 000+', 'довольных клиентов', '600+', 'позиций в каталоге', 'Мы верим в ценность долгосрочных партнерских отношений, что обеспечивает процветание для всех.\r\n\r\nЗа каждым продуктом, представленном на сайте LINDERA, живёт история успешного сотрудничества, многократные исследования и отзывы благодарных покупателей.', 'Наталья', 'директор компании', '/public/src/images/page/main/6964cdd3d5ef0.png', 'Подробнее о нас', 'Даже любовь —\r\nследствие химических реакций', '/public/src/images/page/main/6964cddee0457.jpg', 'Есть вопросы?', 'Мы сделали для вас удобный и понятный раздел с вопросами и ответами.\r\n\r\nЛибо заполните форму и мы вам поможем!', 'Оптовым клиентам', 'Для оптовых покупателей —выгодные цены.', '/public/src/images/page/main/6964cd866aab2.png');

SET FOREIGN_KEY_CHECKS = 1;
