-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 10.0.0.79
-- Время создания: Фев 09 2026 г., 13:19
-- Версия сервера: 5.7.37-40
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `a0644240_lindera_new2`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` varchar(255) DEFAULT NULL COMMENT 'Логин пользователя',
  `password` varchar(255) DEFAULT NULL COMMENT 'Пароль (зашифрован)',
  `name` varchar(255) DEFAULT NULL COMMENT 'Имя пользователя',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Телефон',
  `image` varchar(255) DEFAULT NULL COMMENT 'Фото пользователя',
  `class_id` int(11) DEFAULT NULL COMMENT 'Идентификатор класса пользователя',
  `date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата регистрации',
  `date_visit` datetime DEFAULT NULL COMMENT 'Дата последнего визита',
  `hash` varchar(255) DEFAULT NULL COMMENT 'Хэш',
  `hash_forgot` varchar(255) DEFAULT NULL COMMENT 'Хэш для восстановления пароля',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_user_id` int(11) DEFAULT NULL COMMENT 'Идентификатор пользователя',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `hash` (`hash`),
  KEY `class` (`class_id`),
  KEY `login` (`login`),
  KEY `phone` (`phone`),
  KEY `date_visit` (`date_visit`),
  KEY `edit_user_id` (`edit_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COMMENT='Администраторы';

--
-- Дамп данных таблицы `admins`
--

INSERT INTO `admins` (`id`, `login`, `password`, `name`, `phone`, `image`, `class_id`, `date`, `date_visit`, `hash`, `hash_forgot`, `edit_date`, `edit_user_id`) VALUES
(1, 'support', '$2y$12$awpSEDAivmJFUyijZiIz8OAOqWvHsmVQDD/jY6GRJahSk7xi8J.va', 'Андрей', '', '/public/src/images/users/6977352764229.jpg', 1, '2026-02-06 10:44:36', '0000-00-00 00:00:00', '61fd3ff760da777ec25cec4001deec5e', NULL, '2026-02-05 13:49:31', NULL),
(12, 'test@test.ru', '$2y$12$cKzOPbXr6Vl2ISGwpRN4kOYkv1W8Irka6F7waKT45ihlCnJZGEpwm', NULL, NULL, NULL, 1, '2026-02-06 10:44:41', '2026-02-06 10:44:46', '26318671662fe27c64edd2cd4eeb4240', NULL, '2026-01-28 11:33:51', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `admins_class`
--

CREATE TABLE IF NOT EXISTS `admins_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название класса',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='Классы администраторов';

--
-- Дамп данных таблицы `admins_class`
--

INSERT INTO `admins_class` (`id`, `name`) VALUES
(1, 'Администратор'),
(2, 'Модератор');

-- --------------------------------------------------------

--
-- Структура таблицы `admins_ip`
--

CREATE TABLE IF NOT EXISTS `admins_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'IP-адрес',
  `text` varchar(255) DEFAULT NULL COMMENT 'Комментарий',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_user_id` int(11) DEFAULT NULL COMMENT 'Идентификатор пользователя',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='Настройки доступа по IP';

--
-- Дамп данных таблицы `admins_ip`
--

INSERT INTO `admins_ip` (`id`, `name`, `text`, `edit_date`, `edit_user_id`) VALUES
(1, '127.0.0.1', 'Локальный', '2026-01-29 11:18:12', 1),
(2, '91.234.152.117', 'Наш офисный', '2026-01-29 11:18:04', 1),
(3, '*', 'Все IP адреса', '2026-02-06 10:45:46', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `advantages`
--

CREATE TABLE IF NOT EXISTS `advantages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Название преимущества',
  `text` varchar(511) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Описание',
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Изображение',
  `rate` tinyint(4) DEFAULT '0',
  `show` tinyint(1) DEFAULT '1',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Преимущества компании';

-- --------------------------------------------------------

--
-- Структура таблицы `articles`
--

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) DEFAULT NULL COMMENT 'Идентификатор раздела',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название статьи',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ссылка на статью',
  `date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата публикации',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Изображение',
  `image_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Изображение для превью',
  `text` mediumtext COLLATE utf8mb4_unicode_ci COMMENT 'Описание',
  `text2` text COLLATE utf8mb4_unicode_ci COMMENT 'Дополнительное описание',
  `textshort` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Краткое описание для превью',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  `title` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Заголовок для SEO',
  `keywords` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ключевые слова для SEO',
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Описание для SEO',
  `is_draft` tinyint(1) DEFAULT '0' COMMENT 'Флаг черновика (1 - черновик, 0 - чистовик)',
  `original_id` int(11) DEFAULT NULL COMMENT 'ID связанного чистовика',
  PRIMARY KEY (`id`),
  KEY `section_id` (`section_id`),
  KEY `date` (`date`,`show`),
  KEY `section_date_show_idx` (`section_id`,`date`,`show`),
  KEY `edit_user_id` (`edit_admin_id`),
  KEY `is_draft_idx` (`is_draft`),
  KEY `draft_id_idx` (`original_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Статьи' ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Структура таблицы `articles_sections`
--

CREATE TABLE IF NOT EXISTS `articles_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название раздела',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='Разделы статей';

-- --------------------------------------------------------

--
-- Структура таблицы `banners`
--

CREATE TABLE IF NOT EXISTS `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Изображение',
  `video` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Видео',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Название на баннере',
  `text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Текст на баннере',
  `price` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Блок с ценой на баннере',
  `button_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Текст на кнопке',
  `button_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ссылка с кнопки',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Баннеры';

-- --------------------------------------------------------

--
-- Структура таблицы `catalog`
--

CREATE TABLE IF NOT EXISTS `catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL COMMENT 'Идентификатор категории',
  `name` varchar(255) DEFAULT NULL COMMENT 'Название товара',
  `url` varchar(255) DEFAULT NULL COMMENT 'Ссылка на товар',
  `image_preview` varchar(255) DEFAULT NULL COMMENT 'Изображение для превью',
  `price` int(11) DEFAULT NULL COMMENT 'Стоимость',
  `price_old` int(11) DEFAULT NULL COMMENT 'Стоимость до скидки',
  `count` int(11) DEFAULT NULL COMMENT 'Доступное кол-во товара',
  `text` text COMMENT 'Описание',
  `text2` text COMMENT 'Дополнительное описание',
  `textshort` text COMMENT 'Краткое описание',
  `manufacturer_id` int(11) DEFAULT NULL COMMENT 'Идентификатор производителя',
  `action` tinyint(1) DEFAULT NULL COMMENT 'Шильдик "Акция"',
  `new` tinyint(1) DEFAULT NULL COMMENT 'Шильдик "Новинка"',
  `popular` tinyint(1) DEFAULT NULL COMMENT 'Шильдик "Популярное"',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  `title` varchar(1024) DEFAULT NULL COMMENT 'Заголовок для SEO',
  `keywords` varchar(1024) DEFAULT NULL COMMENT 'Ключевые слова для SEO',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Описание для SEO',
  `is_draft` tinyint(1) DEFAULT '0' COMMENT '	Флаг черновика (1 - черновик, 0 - чистовик)	',
  `original_id` int(11) DEFAULT NULL COMMENT 'ID связанного чистовика',
  PRIMARY KEY (`id`),
  KEY `new` (`new`,`popular`,`action`),
  KEY `status_rate_idx` (`show`,`rate`,`new`,`popular`,`action`),
  KEY `idx_category_show_rate` (`category_id`,`show`,`rate`) COMMENT 'Для списка товаров по категориям',
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `edit_user_id` (`edit_admin_id`),
  KEY `is_draft` (`is_draft`),
  KEY `original_id` (`original_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='Каталог товаров' ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Структура таблицы `catalog_params`
--

CREATE TABLE IF NOT EXISTS `catalog_params` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catalog_id` int(11) DEFAULT NULL COMMENT 'Идентификатор товара',
  `param_id` int(11) DEFAULT NULL COMMENT 'Идентификатор параметра',
  `value` varchar(1024) DEFAULT NULL COMMENT 'Значение параметра',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COMMENT='Значения параметров товаров';

-- --------------------------------------------------------

--
-- Структура таблицы `catalog_prices`
--

CREATE TABLE IF NOT EXISTS `catalog_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catalog_id` int(11) DEFAULT NULL COMMENT 'Идентификатор товара',
  `weight` double DEFAULT NULL COMMENT 'Вес товара',
  `price` int(11) DEFAULT NULL COMMENT 'Стоимость',
  `count` int(11) DEFAULT NULL COMMENT 'Доступное кол-во товара',
  `unit` varchar(50) DEFAULT NULL COMMENT 'Единица измерения',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `catalog_id` (`catalog_id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COMMENT='Стоимости товаров в зависимости от веса';

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) DEFAULT NULL COMMENT 'Родительская категория',
  `name` varchar(255) DEFAULT NULL COMMENT 'Название категории',
  `name_menu` varchar(255) DEFAULT NULL COMMENT 'Название категории в меню',
  `url` varchar(255) DEFAULT NULL COMMENT 'Ссылка на категорию',
  `image` varchar(255) DEFAULT NULL COMMENT 'Изображение',
  `text` text COMMENT 'Описание',
  `template_id` int(11) DEFAULT NULL COMMENT 'Идентификатор шаблона параметров',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `title` varchar(1024) DEFAULT NULL COMMENT 'Заголовок для SEO',
  `keywords` varchar(1024) DEFAULT NULL COMMENT 'Ключевые слова для SEO',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Описание для SEO',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `template_id` (`template_id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COMMENT='Категории товаров';

-- --------------------------------------------------------

--
-- Структура таблицы `changes_log`
--

CREATE TABLE IF NOT EXISTS `changes_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) NOT NULL COMMENT 'Название таблицы',
  `record_id` int(11) DEFAULT NULL COMMENT 'ID измененной записи',
  `field_name` varchar(255) DEFAULT NULL COMMENT 'Название измененного поля',
  `action` enum('INSERT','UPDATE','DELETE','PUBLICATION') NOT NULL COMMENT 'Тип действия',
  `old_value` longtext COMMENT 'Старое значение',
  `new_value` longtext COMMENT 'Новое значение',
  `admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора (если есть)',
  `admin_name` varchar(255) DEFAULT NULL COMMENT 'Имя администратора',
  `admin_ip` varchar(45) DEFAULT NULL COMMENT 'IP адрес администратора',
  `comment` text COMMENT 'Комментарий к изменению',
  `change_type` varchar(50) DEFAULT 'field_change' COMMENT 'Тип изменения (field_change, publication)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  PRIMARY KEY (`id`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_field` (`field_name`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_admin` (`admin_id`),
  KEY `idx_change_type` (`change_type`)
) ENGINE=InnoDB AUTO_INCREMENT=3153 DEFAULT CHARSET=utf8mb4 COMMENT='Лог изменений в базе данных';

-- --------------------------------------------------------

--
-- Структура таблицы `directories`
--

CREATE TABLE IF NOT EXISTS `directories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название справочника',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COMMENT='Справочники';

-- --------------------------------------------------------

--
-- Структура таблицы `directories_values`
--

CREATE TABLE IF NOT EXISTS `directories_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `directory_id` int(11) DEFAULT NULL COMMENT 'ID справочника',
  `value` varchar(255) DEFAULT NULL COMMENT 'Значение',
  `image` varchar(255) DEFAULT NULL COMMENT 'Изображение',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `directory_id` (`directory_id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 COMMENT='Наполнение справочников';

-- --------------------------------------------------------

--
-- Структура таблицы `faq`
--

CREATE TABLE IF NOT EXISTS `faq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) DEFAULT NULL COMMENT 'Идентификатор раздела',
  `quest` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Вопрос',
  `answer` text COLLATE utf8_unicode_ci COMMENT 'Ответ',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `section` (`section_id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Вопрос-ответ';

-- --------------------------------------------------------

--
-- Структура таблицы `faq_sections`
--

CREATE TABLE IF NOT EXISTS `faq_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Название раздела',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Разделы FAQ';

-- --------------------------------------------------------

--
-- Структура таблицы `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'К чему привязка (страница, товар, новость и т.д.)',
  `ids` int(11) DEFAULT NULL COMMENT 'Идентификатор для привязки',
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Файл',
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название файла',
  `extension` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Расширение файла',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `ids` (`ids`),
  KEY `edit_admin_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Файлы';

-- --------------------------------------------------------

--
-- Структура таблицы `finished_products`
--

CREATE TABLE IF NOT EXISTS `finished_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) DEFAULT NULL COMMENT 'Родительская категория',
  `name` varchar(255) DEFAULT NULL COMMENT 'Название',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`parent`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COMMENT='Готовая продукция';

-- --------------------------------------------------------

--
-- Структура таблицы `finished_products_catalog`
--

CREATE TABLE IF NOT EXISTS `finished_products_catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL COMMENT 'Идентификатор готовой продукции',
  `catalog_id` int(11) DEFAULT NULL COMMENT 'Идентификатор товара',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `Catalog_id` (`catalog_id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COMMENT='Привязка товаров к готовой продукции';

-- --------------------------------------------------------

--
-- Структура таблицы `forms`
--

CREATE TABLE IF NOT EXISTS `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT NULL COMMENT 'Идентификатор формы заявки',
  `user_id` int(11) DEFAULT NULL COMMENT 'Идентификатор пользователя',
  `name` varchar(255) DEFAULT NULL COMMENT 'ФИО клиента',
  `phone` varchar(255) DEFAULT NULL COMMENT 'Телефон клиента',
  `email` varchar(255) DEFAULT NULL COMMENT 'Эл. адрес клиента',
  `text` text COMMENT 'Сообщение от клиента',
  `date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата заявки',
  `link` varchar(1024) DEFAULT NULL COMMENT 'Ссылка на страницу с формой',
  `ip` varchar(255) DEFAULT NULL COMMENT 'IP-адрес клиента',
  `status` tinyint(3) DEFAULT '0' COMMENT 'Статус заявки',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `type` (`type_id`),
  KEY `user_id` (`user_id`),
  KEY `date` (`date`),
  KEY `status` (`status`),
  KEY `date_status_idx` (`date`,`status`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Заявки';

-- --------------------------------------------------------

--
-- Структура таблицы `forms_type`
--

CREATE TABLE IF NOT EXISTS `forms_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название формы',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Формы заявок';

-- --------------------------------------------------------

--
-- Структура таблицы `gallery`
--

CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL COMMENT 'К чему привязка (страница, товар, новость и т.д.)',
  `ids` int(11) DEFAULT NULL COMMENT 'Идентификатор для привязки',
  `image` varchar(255) DEFAULT NULL COMMENT 'Большое обрезанное изображение',
  `image_small` varchar(255) DEFAULT NULL COMMENT 'Маленькое обрезанное изображение',
  `image_origin` varchar(255) DEFAULT NULL COMMENT 'Оригинальное изображение',
  `alt` varchar(255) DEFAULT NULL COMMENT 'Название для SEO',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `ids` (`ids`),
  KEY `type` (`type`),
  KEY `edit_admin_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COMMENT='Фотогалерея';

-- --------------------------------------------------------

--
-- Структура таблицы `manufacturers`
--

CREATE TABLE IF NOT EXISTS `manufacturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название',
  `image` varchar(255) DEFAULT NULL COMMENT 'Изображение',
  `text` text COMMENT 'Описание',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайте',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COMMENT='Производители';

-- --------------------------------------------------------

--
-- Структура таблицы `messengers`
--

CREATE TABLE IF NOT EXISTS `messengers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название',
  `image` varchar(255) DEFAULT NULL COMMENT 'Иконка',
  `image_hover` varchar(255) DEFAULT NULL COMMENT 'Иконка при наведении',
  `image2` varchar(255) DEFAULT NULL COMMENT 'Дополнительная иконка',
  `image2_hover` varchar(255) DEFAULT NULL COMMENT 'Дополнительная иконка при наведении',
  `link` varchar(255) DEFAULT NULL COMMENT 'Ссылка',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Мессенджеры и социальные сети';

-- --------------------------------------------------------

--
-- Структура таблицы `migrations`
--

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_name` (`migration_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) DEFAULT NULL COMMENT 'Идентификатор раздела',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название новости',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ссылка на новость',
  `date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата новости',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Изображение',
  `image_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Изображение для превью',
  `text` mediumtext COLLATE utf8mb4_unicode_ci COMMENT 'Описание',
  `text2` text COLLATE utf8mb4_unicode_ci COMMENT 'Дополнительное описание',
  `textshort` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Краткое описание для превью',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `title` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Заголовок для SEO',
  `keywords` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ключевые слова для SEO',
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Описание для SEO',
  `is_draft` tinyint(1) DEFAULT NULL COMMENT 'Черновик (1) или чистовик (0)',
  `original_id` int(11) DEFAULT NULL COMMENT 'ID связанного чистовика для черновика',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `section_id` (`section_id`),
  KEY `date` (`date`,`show`),
  KEY `section_date_show_idx` (`section_id`,`date`,`show`),
  KEY `edit_user_id` (`edit_admin_id`),
  KEY `is_draft_idx` (`is_draft`),
  KEY `original_id_idx` (`original_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Новости' ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Структура таблицы `news_sections`
--

CREATE TABLE IF NOT EXISTS `news_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название раздела',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='Разделы новостей';

-- --------------------------------------------------------

--
-- Структура таблицы `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) DEFAULT NULL COMMENT 'Родительская страница',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название страницы (Н1)',
  `name_menu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название страницы в меню',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ссылка на страницу',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Изображение',
  `text` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Описание',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Вкл./выкл. вывод на сайт',
  `menu` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Вкл./выкл. вывод в меню',
  `menu_footer` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Вкл./выкл. вывод в подвал',
  `title` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Заголовок для SEO',
  `keywords` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ключевые слова для SEO',
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Описание для SEO',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  `video` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Видео файл',
  `is_draft` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Черновик (1 - черновик, 0 - опубликованная версия)',
  `original_id` int(11) DEFAULT NULL COMMENT 'ID оригинальной опубликованной версии (для черновиков)',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`) USING BTREE,
  KEY `edit_user_id` (`edit_admin_id`),
  KEY `is_draft` (`is_draft`),
  KEY `original_id` (`original_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Страницы' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Структура таблицы `params`
--

CREATE TABLE IF NOT EXISTS `params` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название параметра',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COMMENT='Параметры товаров';

-- --------------------------------------------------------

--
-- Структура таблицы `params_groups`
--

CREATE TABLE IF NOT EXISTS `params_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) DEFAULT NULL COMMENT 'Идентификатор шаблона параметров',
  `name` varchar(255) DEFAULT NULL COMMENT 'Название группы параметров',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='Группы параметров внутри шаблона';

-- --------------------------------------------------------

--
-- Структура таблицы `params_groups_items`
--

CREATE TABLE IF NOT EXISTS `params_groups_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL COMMENT 'Идентификатор группы параметров',
  `param_id` int(11) DEFAULT NULL COMMENT 'Идентификатор параметра',
  `type` tinyint(1) DEFAULT NULL COMMENT 'Тип параметра (1 - текст, 2 - справочник)',
  `directory_id` int(11) DEFAULT NULL COMMENT 'Идентификатор справочника',
  `filter` tinyint(1) DEFAULT NULL COMMENT 'Тип фильтра (0 - нет, 1 - список чекбоксов, 2 - выпадающий список, 3 - диапазон значений)',
  `filter_rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки в списке фильтров',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки в карточке товара',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `param_id` (`param_id`),
  KEY `directory_id` (`directory_id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COMMENT='Привязка параметров товаров к группе и их настройки';

-- --------------------------------------------------------

--
-- Структура таблицы `params_templates`
--

CREATE TABLE IF NOT EXISTS `params_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название шаблона параметров',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='Шаблоны параметров';

-- --------------------------------------------------------

--
-- Структура таблицы `partners`
--

CREATE TABLE IF NOT EXISTS `partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название',
  `image` varchar(255) DEFAULT NULL COMMENT 'Логотип',
  `link` varchar(255) DEFAULT NULL COMMENT 'Ссылка',
  `rate` tinyint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора',
  PRIMARY KEY (`id`),
  KEY `edit_admin_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Партнёры';

-- --------------------------------------------------------

--
-- Структура таблицы `publication_versions`
--

CREATE TABLE IF NOT EXISTS `publication_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Имя таблицы модуля',
  `record_id` int(11) NOT NULL COMMENT 'ID опубликованной записи',
  `version_data` json DEFAULT NULL COMMENT 'Данные версии в формате JSON',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания версии',
  `admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора, выполнившего публикацию',
  `admin_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP администратора',
  `comment` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Комментарий к версии',
  `metadata` json DEFAULT NULL COMMENT 'Дополнительные метаданные',
  PRIMARY KEY (`id`),
  KEY `table_record_idx` (`table_name`,`record_id`),
  KEY `created_at_idx` (`created_at`),
  KEY `admin_id_idx` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Версии опубликованных записей для отката';

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL COMMENT 'Дата отзыва',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ФИО клиента',
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Фото клиента',
  `text` varchar(511) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Текст отзыва',
  `video` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Видео отзыва',
  `stars` tinyint(1) DEFAULT NULL COMMENT 'Оценка (кол-во звёзд)',
  `rate` smallint(5) DEFAULT NULL COMMENT 'Рейтинг для сортировки',
  `show` tinyint(1) DEFAULT NULL COMMENT 'Вкл./выкл. вывод на сайт',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Отзывы';

-- --------------------------------------------------------

--
-- Структура таблицы `seo`
--

CREATE TABLE IF NOT EXISTS `seo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL COMMENT 'Ссылка на страницу',
  `title` varchar(1024) DEFAULT NULL COMMENT 'Заголовок для SEO',
  `keywords` varchar(1024) DEFAULT NULL COMMENT 'Ключевые слова для SEO',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Описание для SEO',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  PRIMARY KEY (`id`),
  KEY `edit_user_id` (`edit_admin_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='SEO';

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sitename` varchar(255) DEFAULT NULL COMMENT 'Название сайта для Title',
  `copyright` varchar(255) DEFAULT NULL COMMENT 'Подпись для копирайта',
  `email_sends` varchar(255) DEFAULT NULL COMMENT 'Эл. адрес для заявок',
  `email` varchar(255) DEFAULT NULL COMMENT 'Эл. адрес',
  `phone` varchar(255) DEFAULT NULL COMMENT 'Телефон',
  `phone2` varchar(255) DEFAULT NULL COMMENT 'Дополнительный телефон',
  `postcode` varchar(255) DEFAULT NULL COMMENT 'Индекс',
  `region` varchar(255) DEFAULT NULL COMMENT 'Регион',
  `city` varchar(255) DEFAULT NULL COMMENT 'Город',
  `address` varchar(255) DEFAULT NULL COMMENT 'Адрес',
  `coords` varchar(255) DEFAULT NULL COMMENT 'Координаты точки на карте',
  `company` varchar(255) DEFAULT NULL COMMENT 'Название компании',
  `time_job` varchar(511) DEFAULT NULL COMMENT 'Время работы',
  `requisites` text COMMENT 'Реквизиты',
  `image` varchar(255) DEFAULT NULL COMMENT 'Фото для контактов',
  `file` varchar(255) DEFAULT NULL COMMENT 'Файл с реквизитами',
  `edit_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор администратора',
  `is_draft` tinyint(1) DEFAULT '0' COMMENT 'Флаг черновика (1 - черновик, 0 - чистовик)',
  `draft_id` int(11) DEFAULT NULL COMMENT 'ID связанного черновика/чистовика',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `edit_user_id` (`edit_admin_id`),
  KEY `is_draft_idx` (`is_draft`),
  KEY `draft_id_idx` (`draft_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Настройки';

--
-- Дамп данных таблицы `settings`
--

INSERT INTO `settings` (`id`, `sitename`, `copyright`, `email_sends`, `email`, `phone`, `phone2`, `postcode`, `region`, `city`, `address`, `coords`, `company`, `time_job`, `requisites`, `image`, `file`, `edit_date`, `edit_admin_id`, `is_draft`, `draft_id`) VALUES
(1, 'Новый сайт', 'Копирайт', 'mailsend@site.ru', 'info@site.ru', '', '', '', '', '', '', '', '', '', '', '/public/src/images/settings/698470b72f38e.jpg', '/public/src/files/settings/6984709f27eec.jpg', '2026-02-09 13:17:29', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `subscribe`
--

CREATE TABLE IF NOT EXISTS `subscribe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата подписки',
  `user_id` int(11) DEFAULT '0' COMMENT 'Идентификатор пользователя',
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Эл. адрес подписчика',
  `active` tinyint(1) DEFAULT '1' COMMENT 'Активность подписки',
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'IP-адрес подписчика',
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Подписчики';

-- --------------------------------------------------------

--
-- Структура таблицы `telegram_errors`
--

CREATE TABLE IF NOT EXISTS `telegram_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Идентификатор чата',
  `error` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Текст ошибки',
  `date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата',
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'IP-адрес',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Ошибки телеграм-бота';

-- --------------------------------------------------------

--
-- Структура таблицы `telegram_message`
--

CREATE TABLE IF NOT EXISTS `telegram_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT 'Идентификатор чата в телеграм-бота',
  `mess_id` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Идентификатор сообщения в чате',
  `message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Сообщение',
  `date` datetime DEFAULT NULL COMMENT 'Дата отправки сообщения',
  `answer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Идентификатор ответа на сообщение',
  `answer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Текст ответа',
  `date_answer` datetime DEFAULT NULL COMMENT 'Дата ответа на сообщение',
  `img` tinyint(1) DEFAULT '0' COMMENT 'Наличие изображения в сообщении',
  `type_keyboard` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Тип клавиатуры в сообщении',
  `keyboard` tinyint(1) DEFAULT '0' COMMENT 'Наличие клавиатуры в сообщении',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='История сообщений в телеграм-боте';

-- --------------------------------------------------------

--
-- Структура таблицы `telegram_settings`
--

CREATE TABLE IF NOT EXISTS `telegram_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) DEFAULT NULL COMMENT 'Token телеграм-бота',
  `name` varchar(255) DEFAULT NULL COMMENT 'Название телеграм-бота',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Настройки Телеграм-бота';

-- --------------------------------------------------------

--
-- Структура таблицы `users_class`
--

CREATE TABLE IF NOT EXISTS `users_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название класса',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Классы пользователей';

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `catalog`
--
ALTER TABLE `catalog` ADD FULLTEXT KEY `ft_name_text` (`name`,`textshort`,`text`,`text2`,`title`,`keywords`,`description`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
