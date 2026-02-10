<?php
return [
    // Действия
    'actions' => [
        'copy' => true,                       // Показывать иконку "Копирование"
    ],
    
    // Настройки полей контента
    'fields' => [
        'enabled' => true,
        'tab_name' => 'Данные IP',

        'name' => [
            'enabled' => true,
            'title' => 'IP адрес',
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Комментарий',
        ],
    ],
        
    // Настройки списка
    'list' => [
        'info' => [
            'enabled' => true,
            'title' => 'IP адрес'
        ],
        'description' => [
            'enabled' => true,
            'title' => 'Комментарий'
        ],
        'edit_date' => [
            'enabled' => true,
            'title' => 'Изменение'
        ],
    ],
    
    // Пагинация
    'pagination' => [
        'default_per_page' => 20,
        'order_by' => 'ORDER BY id DESC'
    ],
    
    // Фильтры и поиск
    'filters' => [
        'search' => true,
    ]
];