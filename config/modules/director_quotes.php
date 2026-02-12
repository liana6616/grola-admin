<?php
return [
    // Настройки модуля
    'module' => [
        'title' => 'Цитата директора',            // Заголовок модуля
    ],

    // Настройки полей контента
    'fields' => [
        'image' => [
            'enabled' => true,
            'title' => 'Фото директора',
            'width' => 400,
            'height' => 400,
        ],
        'name' => [
            'enabled' => true,
            'title' => 'ФИО директора'
        ],
        'position' => [
            'enabled' => true,
            'title' => 'Должность директора'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Цитата'
        ],
        
        // Кнопка
        'button' => [
            'enabled' => true,
            'button_name' => [
                'enabled' => true,
                'title' => 'Текст на кнопке'
            ],
            'button_link' => [
                'enabled' => true,
                'title' => 'Ссылка с кнопки'
            ],
        ],
    ],
];