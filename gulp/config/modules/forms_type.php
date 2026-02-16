<?php
return [
    // Настройки модуля
    'module' => [
        'title' => 'Формы заявок',            // Заголовок модуля
    ],

    // Действия
    'actions' => [
        'copy' => true,                       // Показывать иконку "Копирование"
    ],
    
    // Настройки полей контента
    'fields' => [
        'name' => [
            'enabled' => true,
            'title' => 'Название формы'
        ],
        
        // Блок текста
        'text_block' => [
            'enabled' => true,                // Включить/выключить весь текстовый блок
            'title' => 'Текстовый блок',
            'title_field' => [                // Заголовок над описанием
                'enabled' => true,
                'title' => 'Заголовок над описанием'
            ],
            'text' => [                       // Основное описание
                'enabled' => true,
                'title' => 'Описание'
            ],
            'text2' => [                      // Дополнительное описание
                'enabled' => true,
                'title' => 'Дополнительное описание'
            ],
        ],
        
        // Блок кнопки
        'button_block' => [
            'enabled' => true,                // Включить/выключить весь блок кнопки
            'title' => 'Кнопка',
            'button_name' => [                // Текст на кнопке
                'enabled' => true,
                'title' => 'Текст на кнопке'
            ],
            'button_link' => [                // Ссылка с кнопки
                'enabled' => true,
                'title' => 'Ссылка с кнопки'
            ],
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
        'name' => [
            'enabled' => true,
            'title' => 'Название формы'
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
    ],
];