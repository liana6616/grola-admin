<?php
// Загружаем конфигурацию для админов
$config = require_once ROOT . '/config/modules/admins.php';

use app\Models\Admins;
use app\Models\Admins_class;
use app\Helpers;
use app\Pagination;
use app\Form;
use app\FileUpload;

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new Admins();
    
    // Устанавливаем значения по умолчанию
    $obj->date = time();
    
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
        $obj = Admins::findById($id);

        $protected_user_id = $config['system']['protected_user_id'] ?? 1;
        $is_system_user = ($obj->id == $protected_user_id);

        if (!$obj || $is_system_user && $_SESSION['admin']['id'] != $obj->id) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }
    
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        // Очищаем пароль и изображение при копировании
        $obj->password = '';
        $obj->image = '';
        $obj->hash = Helpers::hash();
        $obj->date = time();
        $id = false;
    }

    ?>
    <div class="editHead">
        <h1><?= $title ?> пользователя<?= !empty($_GET['edit']) ? ": {$obj->login}" : '' ?></h1>
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

            <?php if ($id): ?>
                <?= Form::label('', 'ID: '.$obj->id, '') ?>
                
                <?php if ($config['fields']['date']['enabled'] ?? false): ?>
                    <?= Form::label(
                        $config['fields']['date']['title'] ?? 'Дата регистрации', 
                        !empty($obj->date) ? date('d.m.Y H:i', strtotime($obj->date)) : '', 
                        ''
                    ) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['date_visit']['enabled'] ?? false): ?>
                    <?= Form::label(
                        $config['fields']['date_visit']['title'] ?? 'Дата последнего визита', 
                        ($obj->date_visit !== '0000-00-00 00:00:00') ? date('d.m.Y H:i', strtotime($obj->date_visit)) : '-', 
                        ''
                    ) ?>
                <?php endif; ?>
            <?php endif; ?>

            <div class="flex2">
                <?php if ($config['fields']['login']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['login']['title'] ?? 'Логин (Email)', 
                        'login', 
                        $obj->login, 
                        0, 
                        $config['fields']['login']['type'] ?? 'email', 
                        '', 
                        'required'
                    ) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['password']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['password']['title'] ?? 'Пароль', 
                        'password', 
                        '', 
                        0, 
                        $config['fields']['password']['type'] ?? 'password', 
                        '', 
                        ''
                    ) ?>
                <?php endif; ?>
            </div>

            <div class="flex2">
                <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['name']['title'] ?? 'Имя', 'name', $obj->name, 0, '', '', '') ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['phone']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['phone']['title'] ?? 'Телефон', 
                        'phone', 
                        $obj->phone, 
                        0, 
                        $config['fields']['phone']['type'] ?? 'tel', 
                        '', 
                        ''
                    ) ?>
                <?php endif; ?>
            </div>

            <div class="flex2 top">
                <?php if ($config['fields']['class_id']['enabled'] ?? false): ?>
                    <?= Form::select(
                        $config['fields']['class_id']['title'] ?? 'Класс пользователя', 
                        'class_id', 
                        Admins_class::where('ORDER BY name ASC'), 
                        $obj->class_id, 
                        false, 
                        'Не выбрано', 
                        'name', 
                        0, 
                        '', 
                        0, 
                        ''
                    ) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['image']['enabled'] ?? false): ?>
                    <?= Form::image(
                        $config['fields']['image']['title'] . ' (' . 
                        ($config['fields']['image']['width'] ?? 300) . '×' . 
                        ($config['fields']['image']['height'] ?? 300) . ')', 
                        'image', 
                        $obj, 
                        '', 
                        0
                    ) ?>
                <?php endif; ?>
            </div>

            <?= Form::submit($id, $obj->id, 'Сохранить', '') ?>

        </form>
    </div>
<?php
elseif (isset($_POST['add']) || isset($_POST['edit'])) :

    $id = $_POST['edit'] ?? 0;
    
    // Инициализируем объект $obj в зависимости от ситуации
    if (isset($_POST['edit']) && $id > 0) {
        // Редактирование существующей записи
        FileUpload::deleteImageFile();
        $obj = Admins::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        // Добавление новой записи или копирование
        $obj = new Admins();
        $obj->date = time();
        
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
    
    if ($config['fields']['class_id']['enabled'] ?? false) {
        $obj->class_id = (int)trim($_POST['class_id'] ?? 0);
    }
    
    if ($config['fields']['phone']['enabled'] ?? false) {
        $obj->phone = trim($_POST['phone'] ?? '');
    }

    // Проверка уникальности логина
    if ($config['fields']['login']['enabled'] ?? false) {
        $login = trim($_POST['login'] ?? '');
        $other = Admins::findWhere("WHERE `login` = '{$login}' AND `id` <> '{$obj->id}' LIMIT 1");
        if (!empty($other[0])) {
            $_SESSION['error'] = 'Пользователь с таким Логином (E-mail) уже существует';
            $login = null;
        }
        if (!empty($login)) $obj->login = $login;
    }

    $hash = Helpers::hash();
    if (empty($obj->hash)) $obj->hash = $hash;

    // Обновление пароля, если указан
    if (($config['fields']['password']['enabled'] ?? false) && !empty($_POST['password'])) {
        $obj->password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $obj->hash = $hash;
    }

    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    // Загрузка изображения
    if ($config['fields']['image']['enabled'] ?? false) {
        $width = $config['fields']['image']['width'] ?? 300;
        $height = $config['fields']['image']['height'] ?? 300;
        $path = '/public/src/images/admins/';
        
        FileUpload::uploadImage(
            'image', 
            get_class($obj), 
            'image', 
            $obj->id, 
            $width, 
            $height, 
            $path, 
            0
        );
    }

    header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $id = $_GET['delete'];
    $protected_user_id = $config['system']['protected_user_id'] ?? 1;
    
    if ($id != $protected_user_id) {
        $obj = Admins::findById($id);
        
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }

        // Если удаляем текущего пользователя - выходим из системы
        if ($obj->id == $_SESSION['admin']['id']) {
            unset($_SESSION['admin']);
        }
        
        // Удаление фото пользователя
        if (($config['fields']['image']['enabled'] ?? false) && !empty($obj->image) && file_exists(ROOT . $obj->image)) {
            unlink(ROOT . $obj->image);
        }
        
        $obj->delete();
        $_SESSION['notice'] = 'Удалено';
    } else {
        $_SESSION['error'] = 'Нельзя удалить системного пользователя';
    }

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;

else :
    $title = 'Пользователи';
    $add = 'пользователя';

    $filter = false;

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && $config['filters']['search']) {
        $searchFields = [];
        
        if ($config['fields']['login']['enabled'] ?? false) {
            $searchFields[] = "`login` like '%{$search}%'";
        }
        if ($config['fields']['name']['enabled'] ?? false) {
            $searchFields[] = "`name` like '%{$search}%'";
        }
        if ($config['fields']['phone']['enabled'] ?? false) {
            $searchFields[] = "`phone` like '%{$search}%'";
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

    $perPage = $_SESSION['admins']['per_page'] ?? $config['pagination']['default_per_page'];
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: Admins::class,
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
                <?php if (($config['list']['image']['enabled'] ?? false) && ($config['fields']['image']['enabled'] ?? false)): ?>
                    <div class="image_preview"><?= $config['list']['image']['title'] ?? 'Фото' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['info']['enabled'] ?? false): ?>
                    <div class="info"><?= $config['list']['info']['title'] ?? 'Пользователь' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body">
            <?php foreach ($objs as $obj): 
                $class_info = Admins_class::findById($obj->class_id);
                $protected_user_id = $config['system']['protected_user_id'] ?? 1;
                $is_system_user = ($obj->id == $protected_user_id);
            ?>

                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if (($config['list']['image']['enabled'] ?? false) && ($config['fields']['image']['enabled'] ?? false)): ?>
                        <div class="image_preview">
                            <?php if (!empty($obj->image)): ?>
                                <img src="<?= $obj->image ?>" alt="<?= htmlspecialchars($obj->name, ENT_QUOTES, 'UTF-8') ?>" width="50" height="50">
                            <?php else: ?>
                                <div class="no-image">Нет фото</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['info']['enabled'] ?? false): ?>
                        <div class="info">
                            <div class="name"><?= $obj->login ?></div>
                            <div class="comment breadcrumbs">
                                <span class="path">ID: <?= $obj->id ?></span> 
                                <span class="path_arr">|</span>
                                <span class="path">Рег.: <?= !empty($obj->date) ? date('d.m.Y H:i', strtotime($obj->date)) : '—' ?></span>
                                <?php if (!empty($obj->date_visit)): ?>
                                    <span class="path_arr">|</span>
                                    <span class="path">Визит: <?= date('d.m.Y H:i', strtotime($obj->date_visit)) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="modified_date">
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