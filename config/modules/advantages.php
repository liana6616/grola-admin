<?php
return [
    // Действия
    'actions' => [
        'show' => true,                      // Показывать иконку "Показывать на сайте"
        'copy' => true,                      // Показывать иконку "Копирование"
    ],
    
    // Настройки полей контента
    'fields' => [
        'enabled' => true,
        'tab_name' => 'Контент',

        'name' => [
            'enabled' => true,
            'title' => 'Название преимущества'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Описание'
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
            'width' => 500,
            'height' => 500
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
            'title' => 'Описание'
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