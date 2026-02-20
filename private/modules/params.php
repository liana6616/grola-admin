<?php
// Загружаем конфигурацию для параметров товаров
$config = require_once ROOT . '/config/modules/params.php';

use app\Models\Params;
use app\Models\Admins;
use app\Helpers;
use app\Pagination;
use app\Form;

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new Params();
    
    // Устанавливаем значения по умолчанию
    $obj->edit_date = date('Y-m-d H:i:s', time());
    
    $title = 'Добавление';
    $id = false;
    
    if(!empty($_GET['edit'])) {
        $id = $_GET['edit'];
        $title = 'Редактирование';
    }
    if(!empty($_GET['copy']) && ($config['actions']['copy'] ?? false)) {
        $id = $_GET['copy'];
        $title = 'Копирование';
    }
    
    if(!empty($id)) {
        $obj = Params::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }

    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться назад</a>
        </div>
    </div>
    
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form' id='edit_form'
              enctype='multipart/form-data'>
            
            <?php if (isset($_GET['copy']) && ($config['actions']['copy'] ?? false)): ?>
                <!-- При копировании сохраняем ID оригинала как hidden поле -->
                <input type="hidden" name="copy_from" value="<?= $_GET['copy'] ?>">
            <?php endif; ?>

            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['name']['title'] ?? 'Название параметра', 'name', $obj->name, 1, '', '', '') ?>
            <?php endif; ?>

            <div class="button_block">
                <!-- Кнопка Сохранить -->
                <?= Form::submit($id, $obj->id, 'Сохранить', '') ?>
            </div>

        </form>
    </div>
<?php
elseif (isset($_POST['add']) || isset($_POST['edit'])) :

    $id = $_POST['edit'] ?? 0;
    
    // Инициализируем объект $obj в зависимости от ситуации
    if (isset($_POST['edit']) && $id > 0) {
        // Редактирование существующей записи
        $obj = Params::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    } else {
        // Добавление новой записи
        $obj = new Params();
    }
    
    $_SESSION['notice'] = 'Добавлено';
    if(isset($_POST['edit'])) $_SESSION['notice'] = 'Изменено';
    if(isset($_POST['copy_from'])) $_SESSION['notice'] = 'Скопировано';

    // Заполняем данные из формы
    $obj->name = ($config['fields']['name']['enabled'] ?? false) ? trim($_POST['name'] ?? '') : '';
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $obj = Params::findById($_GET['delete']);
    
    $obj->delete();
    $_SESSION['notice'] = 'Удалено';

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;

else :
    // Заголовок модуля из конфига
    $title = $config['module']['title'] ?? '';

    $filter = true;

    // Получаем базовый путь без параметров для редиректов
    $basePath = strtok($_SERVER['REQUEST_URI'], '?') ?: $_SERVER['REQUEST_URI'];

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && ($config['filters']['search'] ?? false)) {
        $whereConditions[] = "(`name` like '%{$search}%')";
    }

    // Формируем полное WHERE условие
    $where = '';
    if (!empty($whereConditions)) {
        $where = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    $perPage = $_SESSION['params']['per_page'] ?? ($config['pagination']['default_per_page'] ?? 20);
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY name ASC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: Params::class,
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
                <?php if ($config['list']['handler'] ?? false): ?>
                    <div class="pole handler_block"></div>
                <?php endif; ?>
                
                <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                    <div class="pole info"><?= $config['list']['name']['title'] ?? 'Название' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="pole modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="pole actions"></div>
            </div>
            <div class="table_body<?= ($totalCount <= $perPage && empty($_GET['search']) && ($config['list']['handler'] ?? false)) ? ' sortbox-items' : '' ?>">
            <?php foreach ($objs as $obj): ?>
                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if ($config['list']['handler'] ?? false): ?>
                        <div class="pole handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($_GET['search'])) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                        <div class="pole info">
                            <div class="title"><?= $config['list']['name']['title'] ?? 'Название' ?></div>
                            <div class="name"><?= $obj->name ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="pole modified_date">
                            <div class="title"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                            <?= $obj->edit_date ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php include ROOT.'/private/views/components/actions.php' ?>
                    
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <?= !empty($pagination) ? $pagination : '' ?>

    <?php else: ?>
        <div class='not_found'>Ничего не найдено</div>
    <?php
    endif;

endif;