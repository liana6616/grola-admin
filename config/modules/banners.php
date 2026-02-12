<?php
return [
    // Настройки модуля
    'module' => [
        'title' => 'Баннеры',            // Заголовок модуля
    ],

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
            'title' => 'Заголовок баннера'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Текст баннера'
        ],
        'price' => [
            'enabled' => true,
            'title' => 'Цена'
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
            'title' => 'Изображение баннера',
            'width' => 1380,
            'height' => 550
        ],
        'video' => [
            'enabled' => true,
            'title' => 'Видео файл'
        ],
        'button' => [
            'enabled' => true,
            'block_name' => 'Кнопка',
            'name' => [
                'enabled' => true,
                'title' => 'Текст на кнопке'
            ],
            'link' => [
                'enabled' => true,
                'title' => 'Ссылка с кнопки'
            ],
        ],
    ],
        
    // Настройки списка
    'list' => [
        'image' => [
            'enabled' => true,
            'title' => 'Баннер'
        ],
        'name' => [
            'enabled' => false,
            'title' => 'Заголовок'
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