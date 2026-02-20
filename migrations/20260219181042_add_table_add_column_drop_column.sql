-- Auto-generated migration based on database changes
-- Generated at: 2026-02-19 18:10:42
-- Previous structure: 2026-02-12 09:56:59
-- Current structure: 2026-02-19 18:10:42

CREATE TABLE `catalog_sorts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название',
  `order_by` varchar(255) DEFAULT NULL COMMENT 'SQL выражение для сортировки',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `edit_admin_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='Типы сортировки каталога';

CREATE TABLE `page_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_spb_title` varchar(255) DEFAULT NULL COMMENT 'Доставка по СПБ. Заголовок',
  `delivery_spb_nm` varchar(255) DEFAULT NULL COMMENT 'Доставка по СПБ. Жирный текст',
  `delivery_spb_text` text COMMENT 'Доставка по СПБ. Текст',
  `delivery_spb_text2` text COMMENT 'Доставка по СПБ. Текст 2',
  `delivery_spb_text3` text COMMENT 'Доставка по СПБ. Текст на сером фоне',
  `delivery_russia_title` varchar(255) DEFAULT NULL COMMENT 'Доставка по России. Заголовок',
  `delivery_russia_text` text COMMENT 'Доставка по России. Текст',
  `delivery_sng_title` varchar(255) DEFAULT NULL COMMENT 'Доставка по СНГ. Заголовок',
  `delivery_sng_text` text COMMENT 'Доставка по СНГ. Текст',
  `block_docs_title` varchar(255) DEFAULT NULL COMMENT 'Блок про документы. Заголовок',
  `block_docs_nm` varchar(255) DEFAULT NULL COMMENT 'Блок про документы. Жирный текст',
  `block_docs_text` text COMMENT 'Блок про документы. Текст',
  `block_docs_nm2` varchar(255) DEFAULT NULL COMMENT 'Блок про документы. Жирный текст 2',
  `block_docs_text2` text COMMENT 'Блок про документы. Текст 2',
  `block_docs_text3` text COMMENT 'Блок про документы. Текст 3',
  `image` varchar(255) DEFAULT NULL COMMENT 'Изображение',
  `image2` varchar(255) DEFAULT NULL COMMENT 'Изображение 2',
  `calc_title` varchar(255) DEFAULT NULL COMMENT 'Калькулятор. Заголовок',
  `calc_text` text COMMENT 'Калькулятор. Текст',
  `calc_text2` text COMMENT 'Калькулятор. Текст 2',
  `delivery_title` varchar(255) DEFAULT NULL COMMENT 'Доставка. Заголовок',
  `delivery_text` text COMMENT 'Доставка. Текст',
  `is_draft` tinyint(1) DEFAULT NULL COMMENT 'Флаг черновика (1 - черновик, 0 - чистовик) ',
  `original_id` int(11) DEFAULT NULL COMMENT 'ID связанного чистовика',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='Страница "Оплата и доставка"';

CREATE TABLE `page_payment_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL COMMENT 'Привязка к черновику (чистовику)',
  `image` varchar(255) DEFAULT NULL COMMENT 'Изображение',
  `alt` varchar(255) DEFAULT NULL COMMENT 'Название (Alt) для SEO',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COMMENT='Службы доставки для страницы "Оплата и доставка"';

CREATE TABLE `page_wholesale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `info_image` varchar(255) DEFAULT NULL COMMENT 'Блок под основным баннером. Изображение',
  `info_title` varchar(255) DEFAULT NULL COMMENT 'Блок под основным баннером. Заголовок',
  `info_text` text COMMENT 'Блок под основным баннером. Текст',
  `title` varchar(255) DEFAULT NULL COMMENT 'Заголовок на сером фоне',
  `text` text COMMENT 'Текст на сером фоне',
  `image` varchar(255) DEFAULT NULL COMMENT 'Изображение справа',
  `banner` varchar(255) DEFAULT NULL COMMENT 'Баннер над формой',
  `banner_text` text COMMENT 'Текст на баннере',
  `is_draft` tinyint(1) DEFAULT NULL COMMENT 'Флаг черновика (1 - черновик, 0 - чистовик)	',
  `original_id` int(11) DEFAULT NULL COMMENT 'ID связанного чистовика',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='Страница "Оптовым покупателям"';

CREATE TABLE `pickup_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название',
  `address` varchar(255) DEFAULT NULL COMMENT 'Адрес',
  `coords` varchar(255) DEFAULT NULL COMMENT 'Координаты точки на карте',
  `text` varchar(1024) DEFAULT NULL COMMENT 'Описание',
  `image` varchar(255) DEFAULT NULL COMMENT 'Изображение',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `edit_admin_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='Пункты самовывоза';

CREATE TABLE `users_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID пользователя',
  `catalog_id` int(11) DEFAULT NULL COMMENT 'ID товара',
  `date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата добавления товара в избранное',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `catalog_id` (`catalog_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COMMENT='Избранные товары пользователей';

ALTER TABLE `catalog` ADD COLUMN `price_sorts` int(11);

ALTER TABLE `forms` ADD COLUMN `company` varchar(255);

ALTER TABLE `messengers` ADD COLUMN `header` tinyint(1);
ALTER TABLE `messengers` ADD COLUMN `footer` tinyint(1);

ALTER TABLE `page_about` ADD COLUMN `image4` varchar(255);
ALTER TABLE `page_about` ADD COLUMN `info_text` varchar(255);
ALTER TABLE `page_about` ADD COLUMN `info_image` varchar(255);

ALTER TABLE `page_main` DROP COLUMN `num1`;
ALTER TABLE `page_main` DROP COLUMN `txt1`;
ALTER TABLE `page_main` DROP COLUMN `num2`;
ALTER TABLE `page_main` DROP COLUMN `txt2`;
ALTER TABLE `page_main` DROP COLUMN `num3`;
ALTER TABLE `page_main` DROP COLUMN `txt3`;
ALTER TABLE `page_main` DROP COLUMN `about_text`;
ALTER TABLE `page_main` DROP COLUMN `about_name`;
ALTER TABLE `page_main` DROP COLUMN `about_position`;
ALTER TABLE `page_main` DROP COLUMN `about_image`;
ALTER TABLE `page_main` DROP COLUMN `about_btn`;
ALTER TABLE `page_main` DROP COLUMN `about_btn_link`;
ALTER TABLE `page_main` DROP COLUMN `faq_name`;
ALTER TABLE `page_main` DROP COLUMN `faq_text`;
ALTER TABLE `page_main` DROP COLUMN `faq_btn`;
ALTER TABLE `page_main` DROP COLUMN `faq_btn_link`;

ALTER TABLE `settings` ADD COLUMN `phone3` varchar(255);
ALTER TABLE `settings` ADD COLUMN `requisites2` text;
ALTER TABLE `settings` ADD COLUMN `image_text` varchar(255);

