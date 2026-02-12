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
            'emails' => [
                'email' => [
                    'enabled' => true,
                    'title' => 'Email для отображения на сайте'
                ],
                'email_sends' => [
                    'enabled' => true,
                    'title' => 'Email для сообщений с сайта'
                ],
            ],
            'phones' => [
                'phone' => [
                    'enabled' => true,
                    'title' => 'Телефон'
                ],
                'phone2' => [
                    'enabled' => true,
                    'title' => 'Телефон 2'
                ],
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
            'requisites' => [
                'enabled' => true,
                'text' => [
                    'enabled' => true,
                    'title' => 'Реквизиты'
                ],
                'file' => [
                    'enabled' => true,
                    'title' => 'Файл с реквизитами'
                ],
            ],
        ],
        
        // Вкладка "Организация"
        'organization' => [
            'enabled' => true,
            'company' => [
                'enabled' => true,
                'title' => 'Название организации'
            ],
            'address' => [
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
    ],
    
    // Настройки загрузки файлов
    'file_upload' => [
        'images' => [
            'contact_image' => [
                'enabled' => true,
                'title' => 'Фото для страницы контактов',
                'width' => 800,
                'height' => 600,
            ],
        ],
        'files' => [
            'requisites_file' => [
                'enabled' => true,
                'title' => 'Файл с реквизитами',
            ],
        ],
    ],
    
    // Микроразметка
    'microdata' => [
        'organization' => true, // Включить микроразметку Organization
    ]
];