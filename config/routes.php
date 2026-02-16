<?php

return [
    'visualteam/ajax' => 'AdminAjax',
    'visualteam/([-a-zA-Z0-9_/]+)' => 'AdminController/$1',
    'visualteam' => 'Admin',

    'migrator' => 'MigratorController',

    'telegram/([-a-zA-Z0-9._/]+)' => 'TelegramController/$1',
    
    'helpers/([-a-zA-Z0-9._/]+)' => 'HelpersController/$1',


    'user/ajax' => 'userAjax',
    'catalog' => 'CatalogController',
    'catalog/([a-zA-Z0-9-/]+)' => 'CatalogController/$1',
    '/' => 'PageController',
    '^([-a-zA-Z0-9._/]+)$' => 'PageController/$1',
    
    '(.*)' => 'error',
];

?>
