<?php
return [
    // Настройки модуля
    'module' => [
        'title' => 'Ключевые показатели',            // Заголовок модуля
    ],
    
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
            'enabled' => false,
            'title' => 'Заголовок'
        ],
        'value' => [
            'enabled' => true,
            'title' => 'Значение'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Описание'
        ],
        'image' => [
            'enabled' => false,
            'title' => 'Иконка',
            'width' => 100,
            'height' => 100
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
            'title' => 'Иконка'
        ],
        'name' => [
            'enabled' => true,
            'title' => 'Заголовок'
        ],
        'value' => [
            'enabled' => true,
            'title' => 'Значение'
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