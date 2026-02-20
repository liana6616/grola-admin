-- Auto-generated migration based on database changes
-- Generated at: 2026-02-12 09:56:59
-- Previous structure: 2026-02-09 11:21:03
-- Current structure: 2026-02-12 09:56:59

CREATE TABLE `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL COMMENT 'Префикс в ссылке',
  `name` varchar(100) DEFAULT NULL COMMENT 'Название города',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT '1' COMMENT 'Вкл./выкл. вывод на сайт',
  `default` tinyint(1) DEFAULT '0' COMMENT 'Город по умолчанию (без префикса)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COMMENT='Города';

CREATE TABLE `director_quotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL COMMENT 'Фото директора',
  `name` varchar(255) DEFAULT NULL COMMENT 'ФИО директора',
  `position` varchar(255) DEFAULT NULL COMMENT 'Должность директора',
  `text` text COMMENT 'Цитата',
  `button_name` varchar(255) DEFAULT NULL COMMENT 'Текст на кнопке',
  `button_link` varchar(255) DEFAULT NULL COMMENT 'Ссылка с кнопки',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `edit_admin_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Цитата директора';

CREATE TABLE `gallery_works` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL COMMENT 'Изображение обрезанное',
  `image_origin` varchar(255) DEFAULT NULL COMMENT 'Изображение оригинальное',
  `link` varchar(255) DEFAULT NULL COMMENT 'Ссылка',
  `name` varchar(255) DEFAULT NULL COMMENT 'Заголовок',
  `text` varchar(511) DEFAULT NULL COMMENT 'Текст',
  `item1_name` varchar(255) DEFAULT NULL COMMENT 'Название параметра 1',
  `item1_text` varchar(255) DEFAULT NULL COMMENT 'Описание параметра 1',
  `item2_name` varchar(255) DEFAULT NULL COMMENT 'Название параметра 2',
  `item2_text` varchar(255) DEFAULT NULL COMMENT 'Описание параметра 2',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `edit_admin_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Галерея выполненных работ';

CREATE TABLE `key_indicators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Заголовок',
  `value` varchar(255) DEFAULT NULL COMMENT 'Значение',
  `text` varchar(511) DEFAULT NULL COMMENT 'Описание',
  `image` varchar(255) DEFAULT NULL COMMENT 'Иконка',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Ключевые показатели';

CREATE TABLE `page_about` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text COMMENT 'Текст возле ключевых показателей',
  `text2` text COMMENT 'Текст в рамке',
  `item_title` varchar(255) DEFAULT NULL COMMENT 'Заголовок над текстом возле двух фото',
  `item_text` varchar(511) DEFAULT NULL COMMENT 'Первая часть текста',
  `item_text2` varchar(511) DEFAULT NULL COMMENT 'Вторая часть текста',
  `image` varchar(255) DEFAULT NULL COMMENT 'Изображение',
  `image2` varchar(255) DEFAULT NULL COMMENT 'Изображение 2',
  `image3` varchar(255) DEFAULT NULL COMMENT 'Изображение с текстом',
  `image3_text` varchar(511) DEFAULT NULL COMMENT 'Текст на изображении',
  `is_draft` tinyint(1) DEFAULT '0' COMMENT 'Флаг черновика (1 - черновик, 0 - чистовик)	',
  `original_id` int(11) DEFAULT NULL COMMENT 'ID связанного чистовика',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `edit_admin_id` (`edit_admin_id`),
  KEY `is_draft` (`is_draft`),
  KEY `original_id` (`original_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='Страница о компании';

CREATE TABLE `page_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ключевой показатель 1 значение',
  `txt1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ключевой показатель 1 текст',
  `num2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ключевой показатель 2 значение',
  `txt2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ключевой показатель 2 текст',
  `num3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ключевой показатель 3 значение',
  `txt3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ключевой показатель 3 текст',
  `about_text` text COLLATE utf8_unicode_ci COMMENT 'Цитата директора',
  `about_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ФИО директора',
  `about_position` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Должность директора',
  `about_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Фото директора',
  `about_btn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Текст на кнопке',
  `about_btn_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ссылка с кнопки',
  `info_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Текст на баннере',
  `info_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Баннер',
  `faq_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Заголовок FAQ',
  `faq_text` text COLLATE utf8_unicode_ci COMMENT 'Текст FAQ',
  `faq_btn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Текст на кнопке',
  `faq_btn_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ссылка с кнопки',
  `opt_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Заголовок баннера',
  `opt_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Текст баннера',
  `opt_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Баннер',
  `opt_btn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Текст на кнопке',
  `opt_btn_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ссылка с кнопки',
  `is_draft` tinyint(1) DEFAULT NULL COMMENT 'Флаг черновика (1 - черновик, 0 - чистовик)	',
  `original_id` int(11) DEFAULT NULL COMMENT 'ID связанного чистовика',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`),
  KEY `original_id` (`original_id`),
  KEY `is_draft` (`is_draft`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Главная страница';

CREATE TABLE `scheme_work` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название',
  `text` varchar(511) DEFAULT NULL COMMENT 'Описание',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `edit_admin_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Схема работы с нами';

CREATE TABLE `why_choose_us` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название',
  `text` varchar(511) DEFAULT NULL COMMENT 'Описание',
  `image` varchar(255) DEFAULT NULL COMMENT 'Иконка',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `edit_admin_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Почему выбирают нас';

DROP TABLE IF EXISTS `main`;

ALTER TABLE `articles` ADD COLUMN `gallery_name` varchar(255);
ALTER TABLE `articles` ADD COLUMN `gallery_text` text;
ALTER TABLE `articles` ADD COLUMN `files_name` varchar(255);
ALTER TABLE `articles` ADD COLUMN `files_text` text;

ALTER TABLE `catalog` ADD COLUMN `gallery_name` varchar(255);
ALTER TABLE `catalog` ADD COLUMN `gallery_text` text;
ALTER TABLE `catalog` ADD COLUMN `files_name` varchar(255);
ALTER TABLE `catalog` ADD COLUMN `files_text` text;

ALTER TABLE `forms_type` ADD COLUMN `title` varchar(255);
ALTER TABLE `forms_type` ADD COLUMN `text` varchar(511);
ALTER TABLE `forms_type` ADD COLUMN `text2` varchar(511);
ALTER TABLE `forms_type` ADD COLUMN `button_name` varchar(255);
ALTER TABLE `forms_type` ADD COLUMN `button_link` varchar(255);
ALTER TABLE `forms_type` ADD COLUMN `image` varchar(255);

ALTER TABLE `news` ADD COLUMN `gallery_name` varchar(255);
ALTER TABLE `news` ADD COLUMN `gallery_text` text;
ALTER TABLE `news` ADD COLUMN `files_name` varchar(255);
ALTER TABLE `news` ADD COLUMN `files_text` text;

ALTER TABLE `pages` ADD COLUMN `image_text` varchar(511);
ALTER TABLE `pages` ADD COLUMN `image2` varchar(255);
ALTER TABLE `pages` ADD COLUMN `gallery_name` varchar(255);
ALTER TABLE `pages` ADD COLUMN `gallery_text` text;
ALTER TABLE `pages` ADD COLUMN `files_name` varchar(255);
ALTER TABLE `pages` ADD COLUMN `files_text` text;

ALTER TABLE `subscribe` ADD COLUMN `edit_date` datetime DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `subscribe` ADD COLUMN `edit_admin_id` int(11);

