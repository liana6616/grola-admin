<?php

return [
    'visualteam/ajax' => 'AdminAjax',
    'visualteam/([-a-zA-Z0-9_/]+)' => 'AdminController/$1',
    'visualteam' => 'Admin',

    'migrator' => 'MigratorController',

    'telegram/([-a-zA-Z0-9._/]+)' => 'TelegramController/$1',
    
    'helpers/([-a-zA-Z0-9._/]+)' => 'HelpersController/$1',

    'user/ajax' => 'userAjax',


    'catalog/([a-zA-Z0-9-]+)/([a-zA-Z0-9-]+)/([a-zA-Z0-9-]+)' => 'CatalogCardController/$1/$2/$3',
    'catalog/([a-zA-Z0-9-]+)/([a-zA-Z0-9-]+)' => 'CatalogController/$1/$2',
    'catalog/([a-zA-Z0-9-]+)' => 'CatalogController/$1',
    'catalog' => 'CatalogController',
    '/' => 'PageController',
    '^([-a-zA-Z0-9._/]+)$' => 'PageController/$1',
    
    '(.*)' => 'error',
];

?>
