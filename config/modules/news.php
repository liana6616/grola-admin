<?php
return [
    // Настройки модуля
    'module' => [
        'title' => 'Новости',            // Заголовок модуля
    ],

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
            'title' => 'Название новости'
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
            'enabled' => false,
            'title' => 'Рейтинг для сортировки'
        ],
        'date' => [
            'enabled' => true,
            'title' => 'Дата'
        ],
        'section_id' => [
            'enabled' => false,
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
            'width' => 1200,
            'height' => 800
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
        'thumbnail_height' => 300,

        'gallery_name' => [
            'enabled' => true,
            'title' => 'Заголовок фотогалереи'
        ],
        'gallery_text' => [
            'enabled' => true,
            'title' => 'Описание фотогалереи'
        ]
    ],
    
    // Файлы
    'files' => [
        'enabled' => true,
        'title' => 'Файлы',
        'tab_name' => 'Файлы',

        'files_name' => [
            'enabled' => true,
            'title' => 'Заголовок файлов'
        ],
        'files_text' => [
            'enabled' => true,
            'title' => 'Описание файлов'
        ]
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
            'enabled' => false,
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
        'handler' => false,
    ],
    
    // Пагинация
    'pagination' => [
        'default_per_page' => 20,
        'order_by' => 'ORDER BY date DESC, id DESC'
    ],
    
    // Фильтры и поиск
    'filters' => [
        'search' => true,
    ]
];