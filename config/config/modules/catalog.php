<?php
return [
    // Настройки модуля
    'module' => [
        'title' => 'Каталог товаров',            // Заголовок модуля
    ],

    // Действия
    'actions' => [
        'show' => true,                      // Показывать иконку "Показывать на сайте"
        'copy' => true,                      // Показывать иконку "Копирование"
        'open' => true,                      // Показывать иконку "Смотреть на сайте"
    ],

    // Настройки черновиков
    'drafts' => [
        'enabled' => true,                   // Включить функционал черновиков
    ],
    
    // Настройки полей контента
    'fields' => [
        'enabled' => true,
        'tab_name' => 'Основная информация',

        'category_id' => [
            'enabled' => true,
            'title' => 'Категория товара'
        ],
        'name' => [
            'enabled' => true,
            'title' => 'Название товара (H1)'
        ],
        'url' => [
            'enabled' => true,
            'title' => 'Ссылка (URL)'
        ],
        'manufacturer_id' => [
            'enabled' => true,
            'title' => 'Производитель'
        ],
        'image_preview' => [
            'enabled' => true,
            'title' => 'Изображение для превью',
            'width' => 300,
            'height' => 300
        ],
        'price' => [
            'enabled' => true,
            'title' => 'Стоимость (руб)'
        ],
        'price_old' => [
            'enabled' => true,
            'title' => 'Старая цена (руб)'
        ],
        'count' => [
            'enabled' => true,
            'title' => 'Доступное количество'
        ],
        'textshort' => [
            'enabled' => true,
            'title' => 'Краткое описание'
        ],
        'text' => [
            'enabled' => true,
            'title' => 'Полное описание товара'
        ],
        'text2' => [
            'enabled' => true,
            'title' => 'Дополнительное описание'
        ],
        'show' => [
            'enabled' => true,
            'title' => 'Показывать на сайте'
        ],
        'rate' => [
            'enabled' => true,
            'title' => 'Рейтинг для сортировки'
        ],
        'action' => [
            'enabled' => true,
            'title' => 'Шильдик "Акция"'
        ],
        'new' => [
            'enabled' => true,
            'title' => 'Шильдик "Новинка"'
        ],
        'popular' => [
            'enabled' => true,
            'title' => 'Шильдик "Популярное"'
        ]
    ],

    // Настройки вкладки "Стоимость по весу"
    'prices' => [
        'enabled' => true,
        'tab_name' => 'Стоимость по весу',
        'title' => 'Стоимость в зависимости от веса',
        
        // Поля для каждой строки цены
        'fields' => [
            'weight' => [
                'enabled' => true,
                'title' => 'Вес (кг)',
            ],
            'price' => [
                'enabled' => true,
                'title' => 'Стоимость (руб)',
            ],
            'count' => [
                'enabled' => true,
                'title' => 'Доступное количество',
            ],
            'unit' => [
                'enabled' => true,
                'title' => 'Единица измерения',
            ]
        ]
    ],

    // Настройки вкладки "Характеристики"
    'params' => [
        'enabled' => true,
        'tab_name' => 'Характеристики',
        'description' => 'Параметры подтягиваются из шаблона прикрепленного к категории'
    ],

    // Настройки вкладки "Готовая продукция"
    'finished_products' => [
        'enabled' => true,
        'tab_name' => 'Готовая продукция',
        'description' => 'Выберите готовую продукцию, в состав которой входит этот товар'
    ],

    // Фотогалерея
    'gallery' => [
        'enabled' => true,
        'tab_name' => 'Фотогалерея',
        'title' => 'Фотогалерея товара',
        'image_width' => 800,
        'image_height' => 600,
        'thumbnail_width' => 400,
        'thumbnail_height' => 300,

        'gallery_name' => [
            'enabled' => true,
            'title' => 'Заголовок фотогалереи'
        ],
        'gallery_text' => [
            'enabled' => true,
            'title' => 'Описание фотогалереи'
        ]
    ],
    
    // Файлы
    'files' => [
        'enabled' => true,
        'tab_name' => 'Файлы',
        'title' => 'Файлы',

        'files_name' => [
            'enabled' => true,
            'title' => 'Заголовок файлов'
        ],
        'files_text' => [
            'enabled' => true,
            'title' => 'Описание файлов'
        ]
    ],
    
    // SEO настройки
    'seo' => [
        'enabled' => true,
        'tab_name' => 'SEO',

        'title' => [
            'enabled' => true,
            'title' => 'Title (заголовок страницы)'
        ],
        'keywords' => [
            'enabled' => true,
            'title' => 'Keywords (ключевые слова)'
        ],
        'description' => [
            'enabled' => true,
            'title' => 'Description (описание)'
        ],
    ],
        
    // Настройки списка товаров
    'list' => [
        'image_preview' => [
            'enabled' => true,
            'title' => 'Превью'
        ],
        'info' => [
            'enabled' => true,
            'title' => 'Товар'
        ],
        'category' => [
            'enabled' => true,
            'title' => 'Категория'
        ],
        'edit_date' => [
            'enabled' => true,
            'title' => 'Изменение'
        ],
        'published_date' => [
            'enabled' => true,
            'title' => 'Публикация'
        ],
        'handler' => true, // Возможность сортировки перетаскиванием
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