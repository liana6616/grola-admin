<?php
return [
    // Действия
    'actions' => [
        'show' => true,                      // Показывать иконку "Показывать на сайте"
        'copy' => true,                      // Показывать иконку "Копирование"
        'open' => true,                      // Показывать иконку "Смотреть на сайте"
    ],

    // Настройки черновиков
    'drafts' => [
        'enabled' => true,                   // Включить функционал черновиков
    ],
    
    // Настройки полей контента
    'fields' => [
        'enabled' => true,
        'tab_name' => 'Контент',

        'name' => [
            'enabled' => true,
            'title' => 'Название страницы (H1)'
        ],
        'name_menu' => [
            'enabled' => true,
            'title' => 'Название для меню'
        ],
        'url' => [
            'enabled' => true,
            'title' => 'Ссылка (URL)'
        ],
        'parent' => [
            'enabled' => true,
            'title' => 'Родительская страница'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Основной текст'
        ],
        'show' => [
            'enabled' => true,
            'title' => 'Показывать на сайте'
        ],
        'menu' => [
            'enabled' => true,
            'title' => 'Показывать в меню'
        ],
        'menu_footer' => [
            'enabled' => true,
            'title' => 'Показывать в подвале'
        ],
        'rate' => [
            'enabled' => true,
            'title' => 'Рейтинг для сортировки'
        ],
        'image' => [
            'enabled' => true,
            'title' => 'Изображение',
            'width' => 2760,
            'height' => 830
        ],
        'image2' => [
            'enabled' => true,
            'title' => 'Дополнительное изображение',
            'width' => 500,
            'height' => 500
        ],
        'video' => [
            'enabled' => true,
            'title' => 'Видео файл'
        ],
    ],

    // Фотогалерея
    'gallery' => [
        'enabled' => true,
        'tab_name' => 'Фотогалерея',
        'title' => 'Фотогалерея',
        'image_width' => 800,
        'image_height' => 600,
        'thumbnail_width' => 400,
        'thumbnail_height' => 300
    ],
    
    // Файлы
    'files' => [
        'enabled' => true,
        'tab_name' => 'Файлы',
        'title' => 'Файлы',
    ],
    
    // SEO настройки
    'seo' => [
        'enabled' => true,
        'tab_name' => 'SEO',

        'title' => [
            'enabled' => true,
            'title' => 'Title'
        ],
        'keywords' => [
            'enabled' => true,
            'title' => 'Keywords'
        ],
        'description' => [
            'enabled' => true,
            'title' => 'Description'
        ],
    ],
        
    // Настройки списка
    'list' => [
        'info' => [
            'enabled' => true,
            'title' => 'Страница'
        ],
        'edit_date' => [
            'enabled' => true,
            'title' => 'Изменение'
        ],
        'published_date' => [
            'enabled' => true,
            'title' => 'Публикация'
        ],
        'handler' => true,
    ],
    
    // Пагинация
    'pagination' => [
        'default_per_page' => 20,
        'order_by' => 'ORDER BY rate DESC, id DESC'
    ],
    
    // Фильтры и поиск
    'filters' => [
        'search' => true,
    ]
];