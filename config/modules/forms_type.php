<?php
return [
    // Действия
    'actions' => [
        'show' => false,                      // Показывать иконку "Показывать на сайте" (отсутствует поле show)
        'copy' => true,                       // Показывать иконку "Копирование"
    ],
    
    // Настройки полей контента
    'fields' => [
        'enabled' => true,
        'tab_name' => 'Контент',

        'name' => [
            'enabled' => true,
            'title' => 'Название формы'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Описание'
        ],
        'text2' => [
            'enabled' => true,
            'title' => 'Дополнительное описание'
        ],
        'image' => [
            'enabled' => true,
            'title' => 'Изображение',
            'width' => 862,
            'height' => 658
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
            'title' => 'Название формы'
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
        'order_by' => 'ORDER BY id DESC'
    ],
    
    // Фильтры и поиск
    'filters' => [
        'search' => true,
    ],
];