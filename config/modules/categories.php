<?php
return [
    // Действия
    'actions' => [
        'show' => true,                      // Показывать иконку "Показывать на сайте"
        'copy' => true,                      // Показывать иконку "Копирование"
        'open' => true,                      // Показывать иконку "Смотреть на сайте"
    ],

    // Настройки полей контента
    'fields' => [
        'enabled' => true,
        'tab_name' => 'Контент',

        'name' => [
            'enabled' => true,
            'title' => 'Название категории (H1)'
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
            'title' => 'Родительская категория'
        ],
        'template_id' => [
            'enabled' => true,
            'title' => 'Шаблон параметров'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Описание категории'
        ],
        'show' => [
            'enabled' => true,
            'title' => 'Показывать на сайте'
        ],
        'rate' => [
            'enabled' => true,
            'title' => 'Рейтинг для сортировки'
        ],
        'image' => [
            'enabled' => true,
            'title' => 'Изображение',
            'width' => 1200,
            'height' => 600
        ],
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
            'title' => 'Категория'
        ],
        'edit_date' => [
            'enabled' => true,
            'title' => 'Изменение'
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