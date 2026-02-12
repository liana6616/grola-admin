<?php
return [
    // Настройки модуля
    'module' => [
        'title' => 'Фото выполненных работ',            // Заголовок модуля
    ],

    // Действия
    'actions' => [
        'show' => true,                      // Показывать иконку "Показывать на сайте"
        'copy' => true,                      // Показывать иконку "Копирование"
        'open' => true,                      // Показывать иконку "Открыть ссылку"
    ],
    
    // Настройки полей контента
    'fields' => [
        'enabled' => true,
        'tab_name' => 'Контент',

        'name' => [
            'enabled' => true,
            'title' => 'Заголовок'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Текст'
        ],
        'link' => [
            'enabled' => true,
            'title' => 'Ссылка'
        ],
        'image' => [
            'enabled' => true,
            'title' => 'Изображение обрезанное',
            'width' => 500,
            'height' => 500
        ],
        'image_origin' => [
            'enabled' => true,
            'title' => 'Изображение оригинальное',
            'width' => 1920,
            'height' => 1080
        ],
        'item1_name' => [
            'enabled' => true,
            'title' => 'Название параметра 1'
        ],
        'item1_text' => [
            'enabled' => true,
            'title' => 'Описание параметра 1'
        ],
        'item2_name' => [
            'enabled' => true,
            'title' => 'Название параметра 2'
        ],
        'item2_text' => [
            'enabled' => true,
            'title' => 'Описание параметра 2'
        ],
        'show' => [
            'enabled' => true,
            'title' => 'Показывать на сайте'
        ],
        'rate' => [
            'enabled' => true,
            'title' => 'Рейтинг для сортировки'
        ],
    ],
        
    // Настройки списка
    'list' => [
        'image' => [
            'enabled' => true,
            'title' => 'Изображение'
        ],
        'name' => [
            'enabled' => true,
            'title' => 'Заголовок'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Текст'
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
        'order_by' => 'ORDER BY rate DESC, id ASC'
    ],
    
    // Фильтры и поиск
    'filters' => [
        'search' => true,
    ],
];