<?php
// Загружаем конфигурацию для IP адресов
$config = require_once ROOT . '/config/modules/admins_ip.php';

use app\Models\Admins_ip;
use app\Helpers;
use app\Pagination;
use app\Form;

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new Admins_ip();
    
    // Устанавливаем значения по умолчанию
    $obj->show = 1;
    
    $title = 'Добавление';
    $id = false;
    
    if(!empty($_GET['edit'])) {
        $id = $_GET['edit'];
        $title = 'Редактирование';
    }
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        $id = $_GET['copy'];
        $title = 'Копирование';
    }
    
    if(!empty($id)) {
        $obj = Admins_ip::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }
    
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        $id = false;
    }

    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться назад</a>
        </div>
    </div>
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form'
              enctype='multipart/form-data'>
            
            <?php if (isset($_GET['copy']) && $config['actions']['copy']): ?>
                <!-- При копировании сохраняем ID оригинала как hidden поле -->
                <input type="hidden" name="copy_from" value="<?= $_GET['copy'] ?>">
            <?php endif; ?>

            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                <?= Form::input(
                    $config['fields']['name']['title'] ?? 'IP адрес', 
                    'name', 
                    $obj->name, 
                    0, 
                    '', 
                    '', 
                    'placeholder="' . ($config['fields']['name']['placeholder'] ?? '') . '"'
                ) ?>
            <?php endif; ?>
            
            <?php if ($config['fields']['text']['enabled'] ?? false): ?>
                <?php 
                $textarea_attrs = '';
                if (isset($config['fields']['text']['maxlength'])) {
                    $textarea_attrs .= 'maxlength="' . $config['fields']['text']['maxlength'] . '" ';
                }
                if (isset($config['fields']['text']['placeholder'])) {
                    $textarea_attrs .= 'placeholder="' . $config['fields']['text']['placeholder'] . '"';
                }
                ?>
                <?= Form::textarea(
                    $config['fields']['text']['title'] ?? 'Комментарий', 
                    'text', 
                    $obj->text, 
                    150, 
                    $textarea_attrs
                ) ?>
            <?php endif; ?>

            <?= Form::submit($id, $obj->id, 'Сохранить','') ?>

        </form>
    </div>
<?php
elseif (isset($_POST['add']) || isset($_POST['edit'])) :

    $id = $_POST['edit'] ?? 0;
    
    // Инициализируем объект $obj в зависимости от ситуации
    if (isset($_POST['edit']) && $id > 0) {
        // Редактирование существующей записи
        $obj = Admins_ip::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        // Добавление новой записи или копирование
        $obj = new Admins_ip();
        
        // Если это копирование из другой записи
        if (isset($_POST['copy_from']) && $_POST['copy_from'] && $config['actions']['copy']) {
            $_SESSION['notice'] = 'Скопировано';
        } else {
            $_SESSION['notice'] = 'Добавлено';
        }
    }

    // Заполняем данные из формы
    if ($config['fields']['name']['enabled'] ?? false) {
        $obj->name = trim($_POST['name'] ?? '');
    }
    
    if ($config['fields']['text']['enabled'] ?? false) {
        $obj->text = trim($_POST['text'] ?? '');
    }
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $id = $_GET['delete'];
    $obj = Admins_ip::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    $obj->delete();

    $_SESSION['notice'] = 'Удалено';

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;

else :
    // Заголовок модуля из конфига
    $title = $config['module']['title'] ?? '';

    $filter = false;

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && $config['filters']['search']) {
        $searchFields = [];
        
        if ($config['fields']['name']['enabled'] ?? false) {
            $searchFields[] = "`name` like '%{$search}%'";
        }
        if ($config['fields']['text']['enabled'] ?? false) {
            $searchFields[] = "`text` like '%{$search}%'";
        }
        
        if (!empty($searchFields)) {
            $whereConditions[] = "(" . implode(' OR ', $searchFields) . ")";
        }
    }

    // Формируем полное WHERE условие
    $where = '';
    if (!empty($whereConditions)) {
        $where = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    $perPage = $_SESSION['admins_ip']['per_page'] ?? $config['pagination']['default_per_page'];
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: Admins_ip::class,
        where: $where,
        order: $order_by,
        defaultItemsPerPage: $perPage
    );
    
    $objs = $result['items'];
    $pagination = $result['pagination'];
    $totalCount = $result['totalCount'];

    include ROOT . '/private/views/components/head.php';
    
    if (!empty($objs)): ?>
        <div class="table_container">
            <div class="table_header">
                <?php if ($config['list']['info']['enabled'] ?? false): ?>
                    <div class="pole info"><?= $config['list']['info']['title'] ?? 'IP адрес' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['description']['enabled'] ?? false): ?>
                    <div class="pole description"><?= $config['list']['description']['title'] ?? 'Комментарий' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="pole modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="pole actions"></div>
            </div>
            <div class="table_body">
            <?php foreach ($objs as $obj): ?>

                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if ($config['list']['info']['enabled'] ?? false): ?>
                        <div class="pole info">
                            <div class="title"><?= $config['list']['info']['title'] ?? 'IP адрес' ?></div>
                            <div class="name"><?= $obj->name ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['description']['enabled'] ?? false): ?>
                        <div class="pole description">
                            <div class="title"><?= $config['list']['description']['title'] ?? 'Комментарий' ?></div>
                            <?= $obj->text ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="pole modified_date">
                            <div class="title"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                            <?= $obj->edit_date ?>
                        </div>
                    <?php endif; ?>
                    
                    <? include ROOT.'/private/views/components/actions.php' ?>
                    
                </div>

            <? endforeach; ?>
            </div>
        </div>

        <?= !empty($pagination) ? $pagination : '' ?>

    <? else: ?>
        <div class='not_found'>Ничего не найдено</div>
    <?php
    endif;

endif;