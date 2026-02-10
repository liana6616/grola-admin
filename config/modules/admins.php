<?php
return [
    // Действия
    'actions' => [
        'copy' => false,                       // Показывать иконку "Копирование"
    ],
    
    // Настройки полей контента
    'fields' => [
        'enabled' => true,
        'tab_name' => 'Данные пользователя',

        'login' => [
            'enabled' => true,
            'title' => 'Логин',
            'type' => 'text'
        ],
        'password' => [
            'enabled' => true,
            'title' => 'Пароль',
            'type' => 'password'
        ],
        'name' => [
            'enabled' => true,
            'title' => 'Имя'
        ],
        'phone' => [
            'enabled' => true,
            'title' => 'Телефон',
            'type' => 'tel'
        ],
        'class_id' => [
            'enabled' => true,
            'title' => 'Класс пользователя'
        ],
        'image' => [
            'enabled' => true,
            'title' => 'Фото пользователя',
            'width' => 300,
            'height' => 300
        ],
        'date' => [
            'enabled' => true,
            'title' => 'Дата регистрации',
        ],
        'date_visit' => [
            'enabled' => true,
            'title' => 'Дата последнего визита',
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
            'title' => 'Пользователь'
        ],
        'edit_date' => [
            'enabled' => true,
            'title' => 'Изменение'
        ],
        'handler' => false, // Для админов сортировка не нужна
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
    
   
    // Системные настройки
    'system' => [
        'protected_user_id' => 1, // ID системного пользователя, которого нельзя удалить
    ]
];