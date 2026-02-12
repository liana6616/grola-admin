<?php
// Загружаем конфигурацию для городов
$config = require_once ROOT . '/config/modules/cities.php';

use app\Models\City;
use app\Models\Admins;
use app\Helpers;
use app\Pagination;
use app\Form;
use app\Db;

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new City();
    
    // Устанавливаем значения по умолчанию
    $obj->show = 1;
    $obj->default = 0;
    $obj->rate = 0; // Новое поле
    $obj->edit_date = date('Y-m-d H:i:s', time());
    
    $title = 'Добавление города';
    $id = false;
    
    if(!empty($_GET['edit'])) {
        $id = $_GET['edit'];
        $title = 'Редактирование города';
    }
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        $id = $_GET['copy'];
        $title = 'Копирование города';
    }
    
    if(!empty($id)) {
        $obj = City::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }

    // Убедимся, что boolean поля правильно обработаны
    $obj->show = (bool)($obj->show ?? 1);
    $obj->default = (bool)($obj->default ?? 0);

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
            
            <?php if (isset($_GET['copy']) && $config['actions']['copy']): ?>
                <!-- При копировании сохраняем ID оригинала как hidden поле -->
                <input type="hidden" name="copy_from" value="<?= $_GET['copy'] ?>">
            <?php endif; ?>

            <div class="flex3">
                <?php if ($config['fields']['show']['enabled'] ?? false): ?>
                    <?= Form::checkbox('show', (bool)$obj->show, $config['fields']['show']['title'] ?? 'Показывать на сайте', 1, null) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['default']['enabled'] ?? false): ?>
                    <?= Form::checkbox('default', (bool)$obj->default, $config['fields']['default']['title'] ?? 'Город по умолчанию', 1, null) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                <?php endif; ?>
            </div>

            <?php if ($config['fields']['code']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['code']['title'] ?? 'Код (префикс в URL)', 'code', $obj->code, false, 'text', '', '') ?>
            <?php endif; ?>

            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['name']['title'] ?? 'Название города', 'name', $obj->name, false, 'text', '', '') ?>
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
        $obj = City::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    } else {
        // Добавление новой записи
        $obj = new City();
    }
    
    $_SESSION['notice'] = 'Город добавлен';
    if(isset($_POST['edit'])) $_SESSION['notice'] = 'Город изменён';
    if(isset($_POST['copy_from'])) $_SESSION['notice'] = 'Город скопирован';

    // Заполняем данные из формы
    $obj->code = ($config['fields']['code']['enabled'] ?? false) ? trim($_POST['code'] ?? '') : '';
    $obj->name = ($config['fields']['name']['enabled'] ?? false) ? trim($_POST['name'] ?? '') : '';
    $obj->rate = ($config['fields']['rate']['enabled'] ?? false) ? (int)($_POST['rate'] ?? 0) : 0;
    $obj->default = ($config['fields']['default']['enabled'] ?? false) ? (int)($_POST['default'] ?? 0) : 0;
    $obj->show = ($config['fields']['show']['enabled'] ?? false) ? (int)($_POST['show'] ?? 0) : 1;
    
    // Если установлен город по умолчанию, снимаем эту отметку у других городов
    if ($obj->default == 1) {
        // Сначала снимаем default у всех городов
        $db = Db::getInstance();
        $db->execute("UPDATE " . City::TABLE . " SET `default` = 0 WHERE `default` = 1");
    }
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $obj = City::findById($_GET['delete']);
    
    // Проверяем, не удаляем ли город по умолчанию
    if ($obj->default == 1) {
        $_SESSION['error'] = 'Нельзя удалить город по умолчанию! Сначала назначьте другой город по умолчанию.';
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    $obj->delete();
    $_SESSION['notice'] = 'Город удалён';

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;

else :
    // Заголовок модуля из конфига
    $title = $config['module']['title'] ?? '';

    $filter = true;

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && $config['filters']['search']) {
        $searchFields = [];
        
        if ($config['fields']['code']['enabled'] ?? false) {
            $searchFields[] = "`code` like '%{$search}%'";
        }
        if ($config['fields']['name']['enabled'] ?? false) {
            $searchFields[] = "`name` like '%{$search}%'";
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

    $perPage = $_SESSION['city']['per_page'] ?? $config['pagination']['default_per_page'];
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY rate DESC, id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: City::class,
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
                <?php if ($config['list']['handler']): ?>
                    <div class="handler_block"></div>
                <?php endif; ?>
                
                <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                    <div class="info"><?= $config['list']['name']['title'] ?? 'Название' ?></div>
                <?php endif; ?>
                
                <?php if (($config['list']['code']['enabled'] ?? false) && ($config['fields']['code']['enabled'] ?? false)): ?>
                    <div class="category"><?= $config['list']['code']['title'] ?? 'Код' ?></div>
                <?php endif; ?>
                
                <?php if (($config['list']['default']['enabled'] ?? false) && ($config['fields']['default']['enabled'] ?? false)): ?>
                    <div class="category"><?= $config['list']['default']['title'] ?? 'По умолчанию' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body<?= ($totalCount <= $perPage && empty($_GET['search']) && $config['list']['handler']) ? ' sortbox-items' : '' ?>">
            <?php foreach ($objs as $obj): ?>
                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if ($config['list']['handler']): ?>
                        <div class="handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($_GET['search'])) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                        <div class="info">
                            <div class="name"><?= $obj->name ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['code']['enabled'] ?? false) && ($config['fields']['code']['enabled'] ?? false)): ?>
                        <div class="category">
                            <code><?= $obj->code ?></code>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['default']['enabled'] ?? false) && ($config['fields']['default']['enabled'] ?? false)): ?>
                        <div class="category">
                            <?php if ($obj->default): ?>
                                <span class="default_city">✓</span>
                            <?php else: ?>
                                <span class="not_default">—</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="modified_date">
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
        <div class='not_found'>Городы не найдены</div>
    <?php
    endif;

endif;