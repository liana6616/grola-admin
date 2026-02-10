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
            'title' => 'Имя клиента'
        ],
        'date' => [
            'enabled' => true,
            'title' => 'Дата отзыва'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Текст отзыва'
        ],
        'stars' => [
            'enabled' => true,
            'title' => 'Количество звёзд',
            'options' => [
                1 => '1 звезда',
                2 => '2 звезды',
                3 => '3 звезды',
                4 => '4 звезды',
                5 => '5 звёзд'
            ]
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
            'title' => 'Фото клиента',
            'width' => 300,
            'height' => 300
        ],
        'video' => [
            'enabled' => true,
            'title' => 'Видео отзыва'
        ],
    ],
        
    // Настройки списка
    'list' => [
        'image' => [
            'enabled' => true,
            'title' => 'Фото'
        ],
        'info' => [
            'enabled' => true,
            'title' => 'Отзыв'
        ],
        'stars' => [
            'enabled' => true,
            'title' => 'Оценка'
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
        'order_by' => 'ORDER BY rate DESC, date DESC, id DESC'
    ],
    
    // Фильтры и поиск
    'filters' => [
        'search' => true,
    ]
];