<?php

return [
    // Разрешения по умолчанию для каждого класса
    'permissions' => [
        'create' => [1,2],       // Кто может создавать
        'edit' => [1,2],         // Кто может редактировать
        'copy' => [1,2],          // Кто может копировать
        'delete' => [1,2],        // Кто может удалять
        'publish' => [1,2],        // Кто может публиковать
    ],
    
    // Меню с разделением по классам
    'menu' => [
        'settings' => [
            'title' => 'Настройки',
            'icon' => 'settings',
            'class' => [1]  // Только администраторы
        ],
        
        'banners' => [
            'title' => 'Баннеры',
            'icon' => 'image',
            'class' => [1, 2]  // Администраторы и модераторы
        ],
        
        /*
        'news_block' => [
            'title' => 'Новости',
            'icon' => 'news',
            'class' => [1, 2],
            'children' => [
                'news' => [
                    'title' => 'Новости',
                    'icon' => 'news',
                    'class' => [1, 2]
                ],
                'news_sections' => [
                    'title' => 'Разделы новостей',
                    'icon' => 'folder',
                    'class' => [1,2]  // Только администраторы
                ]
            ]
        ]
        */

        'news' => [
            'title' => 'Новости',
            'icon' => 'news',
            'class' => [1, 2]
        ],
        
        'reviews' => [
            'title' => 'Отзывы',
            'icon' => 'file',
            'class' => [1, 2]
        ],

        'modules_block' => [
            'title' => 'Модули',
            'icon' => 'folder',
            'class' => [1, 2],
            'children' => [
                'advantages' => [
                    'title' => 'Плюсы работы с нами',
                    'icon' => 'like',
                    'class' => [1,2]
                ],
                'gallery_works' => [
                    'title' => 'Фото выполненных работ',
                    'icon' => 'file',
                    'class' => [1,2]
                ],
                'scheme_work' => [
                    'title' => 'Схема работы с нами',
                    'icon' => 'file',
                    'class' => [1,2]
                ],
                'why_choose_us' => [
                    'title' => 'Почему выбирают нас',
                    'icon' => 'file',
                    'class' => [1,2]
                ],
                'key_indicators' => [
                    'title' => 'Ключевые показатели',
                    'icon' => 'file',
                    'class' => [1,2]
                ],
                'director_quotes' => [
                    'title' => 'Цитата директора',
                    'icon' => 'file',
                    'class' => [1,2]
                ],
                'partners' => [
                    'title' => 'Партнёры',
                    'icon' => 'file',
                    'class' => [1, 2]
                ],
                'pickup_points' => [
                    'title' => 'Пункты самовывоза',
                    'icon' => 'file',
                    'class' => [1, 2]
                ],
            ]
        ],

        'cities' => [
            'title' => 'Города',
            'icon' => 'file',
            'class' => [1, 2]
        ],
        

            
        
        'admins_block' => [
            'title' => 'Администраторы',
            'icon' => 'users',
            'class' => [1],  // Только администраторы
            'children' => [
                'admins' => [
                    'title' => 'Администраторы',
                    'icon' => 'users',
                    'class' => [1]
                ],
                'admins_ip' => [
                    'title' => 'Разрешённые IP адреса',
                    'icon' => 'shield',
                    'class' => [1]
                ]
            ]
        ],
        
        'messengers' => [
            'title' => 'Соц. сети и мессенджеры',
            'icon' => 'messengers',
            'class' => [1, 2]
        ],
        
        'directories' => [
            'title' => 'Справочники',
            'icon' => 'file',
            'class' => [1, 2]
        ],
        
        'articles_block' => [
            'title' => 'Статьи',
            'icon' => 'news',
            'class' => [1, 2],
            'children' => [
                'articles' => [
                    'title' => 'Статьи',
                    'icon' => 'news',
                    'class' => [1,2]
                ],
                'articles_sections' => [
                    'title' => 'Разделы статей',
                    'icon' => 'folder',
                    'class' => [1,2]
                ]
            ]
        ],
        
        'pages' => [
            'title' => 'Страницы',
            'icon' => 'pages',
            'class' => [1,2]  // Только администраторы
        ],
        
        'FAQ_block' => [
            'title' => 'FAQ',
            'icon' => 'file',
            'class' => [1, 2],
            'children' => [
                'faq_sections' => [
                    'title' => 'Категории',
                    'icon' => 'folder',
                    'class' => [1, 2]
                ],
                'faq' => [
                    'title' => 'FAQ',
                    'icon' => 'file',
                    'class' => [1, 2]
                ]
            ]
        ],
        
        'seo' => [
            'title' => 'SEO',
            'icon' => 'seo',
            'class' => [1, 2]  // Только администраторы
        ],

        'forms_block' => [
            'title' => 'Заявки',
            'icon' => 'file',
            'class' => [1, 2],
            'children' => [
                'forms_type' => [
                    'title' => 'Формы заявок',
                    'icon' => 'file',
                    'class' => [1, 2]
                ],
                'forms' => [
                    'title' => 'Заявки',
                    'icon' => 'file',
                    'class' => [1, 2]
                ]
            ]
        ],

        'subscribe' => [
            'title' => 'Подписчики',
            'icon' => 'list',
            'class' => [1, 2]
        ],

        'catalog_block' => [
            'title' => 'Каталог',
            'icon' => 'file',
            'class' => [1, 2],
            'children' => [
                'manufacturers' => [
                    'title' => 'Производители',
                    'icon' => 'folder',
                    'class' => [1, 2]
                ],
                'finished_products' => [
                    'title' => 'Готовая продукция',
                    'icon' => 'file',
                    'class' => [1, 2]
                ],
                'params' => [
                    'title' => 'Параметры товаров',
                    'icon' => 'file',
                    'class' => [1, 2]
                ],
                'params_templates' => [
                    'title' => 'Шаблоны параметров товаров',
                    'icon' => 'file',
                    'class' => [1, 2]
                ],
                'categories' => [
                    'title' => 'Категории товаров',
                    'icon' => 'file',
                    'class' => [1, 2]
                ],
                'catalog' => [
                    'title' => 'Каталог товаров',
                    'icon' => 'file',
                    'class' => [1, 2]
                ]
            ]
        ],
    ]
];