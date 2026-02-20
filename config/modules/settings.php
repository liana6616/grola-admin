<?php
return [
    // Настройки модуля
    'module' => [
        'title' => 'Настройки',            // Заголовок модуля
    ],

    // Настройки полей контента
    'fields' => [
        // Вкладка "Основные"
        'site' => [
            'enabled' => true,
            'sitename' => [
                'enabled' => true,
                'title' => 'Название сайта'
            ],
            'copyright' => [
                'enabled' => true,
                'title' => 'Копирайт'
            ],
        ],
        
        // Вкладка "Контакты"
        'contacts' => [
            'enabled' => true,

            'email' => [
                'enabled' => true,
                'title' => 'Email для отображения на сайте'
            ],
            'email_sends' => [
                'enabled' => true,
                'title' => 'Email для сообщений с сайта'
            ],

            'phone' => [
                'enabled' => true,
                'title' => 'Телефон'
            ],
            'phone2' => [
                'enabled' => true,
                'title' => 'Телефон 2'
            ],
            'phone3' => [
                'enabled' => true,
                'title' => 'Телефон 3'
            ],

            'time_job' => [
                'enabled' => true,
                'title' => 'Время работы'
            ],
            'map' => [
                'enabled' => true,
                'coords' => [
                    'enabled' => true,
                    'title' => 'Координаты на карте'
                ],
            ],
            'image' => [
                'enabled' => true,
                'title' => 'Фото для страницы контактов',
                'width' => 1350,
                'height' => 600,
            ],
            'image_text' => [
                'enabled' => true,
                'title' => 'Текст на изображении'
            ],
            'requisites' => [
                'enabled' => true,
                'title' => 'Реквизиты'
            ],
            'requisites2' => [
                'enabled' => true,
                'title' => 'Реквизиты. Дополнительное поле'
            ],
            'file' => [
                'enabled' => true,
                'title' => 'Файл с реквизитами'
            ],
        ],
        
        // Вкладка "Организация"
        'organization' => [
            'enabled' => true,
            'company' => [
                'enabled' => true,
                'title' => 'Название организации'
            ],
            'postcode' => [
                'enabled' => true,
                'title' => 'Почтовый индекс'
            ],
            'region' => [
                'enabled' => true,
                'title' => 'Регион'
            ],
            'city' => [
                'enabled' => true,
                'title' => 'Город'
            ],
            'address' => [
                'enabled' => true,
                'title' => 'Адрес'
            ],
        ],
    ],
    
    // Микроразметка
    'microdata' => [
        'organization' => true, // Включить микроразметку Organization
    ]
];