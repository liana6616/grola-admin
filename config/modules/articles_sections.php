<?php
return [
    // Настройки модуля
    'module' => [
        'title' => 'Разделы статей',            // Заголовок модуля
    ],
    
    // Действия
    'actions' => [
        'show' => true,                      // Показывать иконку "Показывать на сайте"
        'copy' => true,                      // Показывать иконку "Копирование"
        'open' => true,                      // Показывать иконку "Смотреть на сайте"
    ],
        
    // Настройки полей контента
    'fields' => [
        'name' => [
            'enabled' => true,
            'title' => 'Название раздела'
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
        'name' => [
            'enabled' => true,
            'title' => 'Название'
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