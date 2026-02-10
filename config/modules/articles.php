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
            'title' => 'Название статьи'
        ],
        'url' => [
            'enabled' => true,
            'title' => 'Ссылка (URL)'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Основной текст'
        ],
        'text2' => [
            'enabled' => true,
            'title' => 'Дополнительный текст'
        ],
        'show' => [
            'enabled' => true,
            'title' => 'Показывать на сайте'
        ],
        'rate' => [
            'enabled' => true,
            'title' => 'Рейтинг для сортировки'
        ],
        'date' => [
            'enabled' => true,
            'title' => 'Дата'
        ],
        'section_id' => [
            'enabled' => true,
            'title' => 'Категория'
        ],
        'preview' => [
            'enabled' => true,
            'block_name' => 'Превью',
            'image_preview' => [
                'enabled' => true,
                'title' => 'Изображение превью',
                'width' => 300,
                'height' => 300
            ],
            'textshort' => [
                'enabled' => true,
                'title' => 'Краткое описание'
            ],
        ],
        'image' => [
            'enabled' => true,
            'title' => 'Изображение',
            'width' => 1820,
            'height' => 1040
        ],
    ],

    // Фотогалерея
    'gallery' => [
        'enabled' => true,
        'title' => 'Фотогалерея',
        'tab_name' => 'Фотогалерея',
        'image_width' => 800,
        'image_height' => 600,
        'thumbnail_width' => 400,
        'thumbnail_height' => 300
    ],
    
    // Файлы
    'files' => [
        'enabled' => true,
        'title' => 'Файлы',
        'tab_name' => 'Файлы',
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
        'image_preview' => [
            'enabled' => true,
            'title' => 'Превью'
        ],
        'name' => [
            'enabled' => true,
            'title' => 'Название'
        ],
        'textshort' => true,
        'section' => [
            'enabled' => true,
            'title' => 'Раздел'
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
        'order_by' => 'ORDER BY rate DESC, date DESC, id DESC'
    ],
    
    // Фильтры и поиск
    'filters' => [
        'search' => true,
    ]
];