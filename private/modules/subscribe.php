<?php
// Загружаем конфигурацию для подписчиков
$config = require_once ROOT . '/config/modules/subscribe.php';

use app\Models\Subscribe;
use app\Models\Admins;
use app\Helpers;
use app\Pagination;
use app\Form;

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new Subscribe();
    
    // Устанавливаем значения по умолчанию
    $obj->active = 1;
    $obj->date = date('Y-m-d H:i:s');
    $obj->edit_date = date('Y-m-d H:i:s');
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
    
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
        $obj = Subscribe::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }
    
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        // При копировании сбрасываем дату и IP
        $obj->date = date('Y-m-d H:i:s');
        $obj->ip = '';
        $id = false;
    }

    // Убедимся, что $obj->active всегда boolean
    $obj->active = (bool)($obj->active ?? 1);

    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться назад</a>
        </div>
    </div>
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form'>

            <?php if (isset($_GET['copy']) && $config['actions']['copy']): ?>
                <!-- При копировании сохраняем ID оригинала как hidden поле -->
                <input type="hidden" name="copy_from" value="<?= $_GET['copy'] ?>">
            <?php endif; ?>

            <div class="flex2">
                <?php if ($config['fields']['email']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['email']['title'] ?? 'Email', 'email', $obj->email, 1, 'email', '', '') ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['active']['enabled'] ?? false): ?>
                    <?= Form::checkbox('active', (bool)$obj->active, $config['fields']['active']['title'] ?? 'Активность подписки', 1, null) ?>
                <?php endif; ?>
            </div>

            <!-- IP не выводится в редактировании, только при добавлении автоматически -->

            <?= Form::submit($id, $obj->id, 'Сохранить', '') ?>

        </form>
    </div>
<?php
elseif (isset($_POST['add']) || isset($_POST['edit'])) :

    $id = $_POST['edit'] ?? 0;
    
    // Инициализируем объект $obj в зависимости от ситуации
    if (isset($_POST['edit']) && $id > 0) {
        // Редактирование существующей записи
        $obj = Subscribe::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        // Добавление новой записи или копирование
        $obj = new Subscribe();
        
        // Устанавливаем дату подписки и IP только при добавлении
        $obj->date = date('Y-m-d H:i:s');
        $obj->ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Если это копирование из другой записи
        if (isset($_POST['copy_from']) && $_POST['copy_from'] && $config['actions']['copy']) {
            $_SESSION['notice'] = 'Скопировано';
            // При копировании IP обновляем на текущий
            $obj->ip = $_SERVER['REMOTE_ADDR'] ?? '';
        } else {
            $_SESSION['notice'] = 'Добавлено';
        }
    }

    // Заполняем данные из формы с проверкой доступности полей
    if ($config['fields']['email']['enabled'] ?? false) {
        $obj->email = trim($_POST['email'] ?? '');
    }
    
    if ($config['fields']['active']['enabled'] ?? false) {
        $obj->active = (int)($_POST['active'] ?? 0);
    }
    
    // IP обновляем ТОЛЬКО при создании новой записи
    // При редактировании IP не меняем
    
    // Системные поля - всегда сохраняются
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $id = $_GET['delete'];
    $obj = Subscribe::findById($id);
    
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
    $title = $config['module']['title'] ?? 'Подписчики';

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && ($config['filters']['search'] ?? false)) {
        $searchFields = [];
        
        if (($config['fields']['email']['enabled'] ?? false)) {
            $searchFields[] = "`email` like '%{$search}%'";
        }
        if (($config['fields']['ip']['enabled'] ?? false)) {
            $searchFields[] = "`ip` like '%{$search}%'";
        }
        
        if (!empty($searchFields)) {
            $whereConditions[] = "(" . implode(' OR ', $searchFields) . ")";
        }
    }

    // Фильтр по активности
    $active = trim($_GET['active'] ?? '');
    if ($active !== '' && ($config['filters']['active'] ?? false)) {
        if ($active === '1' || $active === '0') {
            $whereConditions[] = "`active` = {$active}";
        }
    }

    // Формируем полное WHERE условие
    $where = '';
    if (!empty($whereConditions)) {
        $where = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    $perPage = $_SESSION['subscribe']['per_page'] ?? ($config['pagination']['default_per_page'] ?? 20);
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY date DESC, id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: Subscribe::class,
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
                <?php if (($config['list']['email']['enabled'] ?? false) && ($config['fields']['email']['enabled'] ?? false)): ?>
                    <div class="pole info"><?= $config['list']['email']['title'] ?? 'Email' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['date']['enabled'] ?? false): ?>
                    <div class="pole modified_date"><?= $config['list']['date']['title'] ?? 'Дата подписки' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['active']['enabled'] ?? false): ?>
                    <div class="pole category"><?= $config['list']['active']['title'] ?? 'Статус' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="pole modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body">
            <?php foreach ($objs as $obj): ?>
                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if (($config['list']['email']['enabled'] ?? false) && ($config['fields']['email']['enabled'] ?? false)): ?>
                        <div class="pole info">
                            <div class="title"><?= $config['list']['email']['title'] ?? 'E-mail' ?></div>
                            <div class="name"><?= htmlspecialchars($obj->email ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                            <?php if (($config['fields']['ip']['enabled'] ?? false) && !empty($obj->ip)): ?>
                                <div class="comment">
                                    IP: <?= $obj->ip ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['date']['enabled'] ?? false): ?>
                        <div class="pole modified_date">
                            <div class="title"><?= $config['list']['date']['title'] ?? 'Дата подписки' ?></div>
                            <?= !empty($obj->date) ? date('d.m.Y H:i', strtotime($obj->date)) : '—' ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['active']['enabled'] ?? false): ?>
                        <div class="pole category">
                            <div class="title"><?= $config['list']['active']['title'] ?? 'Статус' ?></div>
                            <?php if ($obj->active == 1): ?>
                                <span class="status_active">Активен</span>
                            <?php else: ?>
                                <span class="status_inactive">Неактивен</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="pole modified_date">
                            <div class="title"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                            <?= !empty($obj->edit_date) ? date('d.m.Y H:i', strtotime($obj->edit_date)) : '—' ?>
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