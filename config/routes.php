<?php

return [
    'visualteam/ajax' => 'AdminAjax',
    'visualteam/([-a-zA-Z0-9_/]+)' => 'AdminController/$1',
    'visualteam' => 'Admin',
    'migrator' => 'MigratorController',
    'telegram/([-a-zA-Z0-9._/]+)' => 'TelegramController/$1',
    'helpers/([-a-zA-Z0-9._/]+)' => 'HelpersController/$1',
    'user/ajax' => 'userAjax',
    
    // 1. САМЫЙ КОНКРЕТНЫЙ - товар в категории (2 сегмента)
    'catalog/([a-zA-Z0-9-]+)/([a-zA-Z0-9-]+)' => 'CatalogCardController/$1/$2',
    
    // 2. КАТЕГОРИЯ (1 сегмент)
    'catalog/([a-zA-Z0-9-]+)' => 'CatalogController/$1',
    
    // 3. КОРЕНЬ КАТАЛОГА
    'catalog' => 'CatalogController',
    
    // Остальные маршруты
    '/' => 'PageController',
    '^([-a-zA-Z0-9._/]+)$' => 'PageController/$1',
    '(.*)' => 'error',
];