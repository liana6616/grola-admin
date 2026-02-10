<?php
// Загружаем конфигурацию для справочников
$config = require_once ROOT . '/config/modules/directories.php';

use app\Models\Directories;
use app\Models\DirectoriesValues;
use app\Helpers;
use app\Pagination;
use app\FileUpload;
use app\Form;

$ids = $_GET['ids'] ?? null;

// Режим добавления/редактирования/копирования значений справочника
if (isset($_GET['addItem']) || isset($_GET['editItem']) || isset($_GET['copyItem'])) :

    $obj = new DirectoriesValues();
    $obj->show = 1;
    $obj->rate = 0;

    $title = 'Добавление значения';
    $id = false;
    if(!empty($_GET['editItem'])) {
        $id = $_GET['editItem'];
        $title = 'Редактирование значения';
    }
    if(!empty($_GET['copyItem']) && $config['values_actions']['copy']) {
        $id = $_GET['copyItem'];
        $title = 'Копирование значения';
    }

    if ($id) {
        $obj = DirectoriesValues::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . ($ids ?? ''));
            exit;
        }
    }

    if(!empty($_GET['copyItem']) && $config['values_actions']['copy']) {
        $id = false;
        // Очищаем изображение при копировании
        $obj->image = '';
    }

    $directory = Directories::findById($ids);
    if (!$directory) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>?ids=<?= $ids ?>' class='btn btn_white btn_back'>Вернуться назад</a>
        </div>
    </div>
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form'
              enctype='multipart/form-data'>
            <input type='hidden' name='ids' value='<?= $ids ?>'>
            
            <?php if (isset($_GET['copyItem']) && $config['values_actions']['copy']): ?>
                <!-- При копировании сохраняем ID оригинала как hidden поле -->
                <input type="hidden" name="copy_from" value="<?= $_GET['copyItem'] ?>">
            <?php endif; ?>

            <div class="flex2">
                <?php if ($config['values_fields']['show']['enabled'] ?? false): ?>
                    <?= Form::checkbox('show', (bool)$obj->show, $config['values_fields']['show']['title'] ?? 'Показывать', 1, null) ?>
                <?php endif; ?>
                
                <?php if ($config['values_fields']['rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['values_fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                <?php endif; ?>
            </div>

            <?php if ($config['values_fields']['value']['enabled'] ?? false): ?>
                <?= Form::input($config['values_fields']['value']['title'] ?? 'Название значения', 'value', $obj->value, 0, '', '', '') ?>
            <?php endif; ?>

            <?php if ($config['values_fields']['image']['enabled'] ?? false): ?>
                <?= Form::image(
                    $config['values_fields']['image']['title'] . ' (' . 
                    ($config['values_fields']['image']['width'] ?? 100) . '×' . 
                    ($config['values_fields']['image']['height'] ?? 100) . ')', 
                    'image', 
                    $obj, 
                    '', 
                    0
                ) ?>
            <?php endif; ?>

            <?= Form::submit($id, $obj->id, 'Сохранить', $ids) ?>

        </form>
    </div>

<?php 
// Сохранение значений справочника
elseif (isset($_POST['addItem']) || isset($_POST['editItem'])) :

    $id = $_POST['editItem'] ?? 0;
    
    // Инициализируем объект $obj в зависимости от ситуации
    if (isset($_POST['editItem']) && $id > 0) {
        // Редактирование существующей записи
        FileUpload::deleteImageFile();
        $obj = DirectoriesValues::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . ($_POST['ids'] ?? ''));
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        // Добавление новой записи или копирование
        $obj = new DirectoriesValues();
        
        // Если это копирование из другой записи
        if (isset($_POST['copy_from']) && $_POST['copy_from'] && $config['values_actions']['copy']) {
            $_SESSION['notice'] = 'Скопировано';
        } else {
            $_SESSION['notice'] = 'Добавлено';
        }
    }

    // Заполняем данные из формы
    if ($config['values_fields']['value']['enabled'] ?? false) {
        $obj->value = trim($_POST['value'] ?? '');
    }
    
    if ($config['values_fields']['rate']['enabled'] ?? false) {
        $obj->rate = (int)trim($_POST['rate'] ?? 0);
    }
    
    if ($config['values_fields']['show']['enabled'] ?? false) {
        $obj->show = (int)($_POST['show'] ?? 0);
    }
    
    $obj->directory_id = (int)trim($_POST['ids'] ?? 0);
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    // Загрузка изображения
    if ($config['values_fields']['image']['enabled'] ?? false) {
        $width = $config['values_fields']['image']['width'] ?? 100;
        $height = $config['values_fields']['image']['height'] ?? 100;
        
        FileUpload::uploadImage(
            'image', 
            get_class($obj), 
            'image', 
            $obj->id, 
            $width, 
            $height, 
            '/public/src/images/directories/', 
            0
        );
    }

    // Исправленный редирект после сохранения значения справочника
    $redirectUrl = $_SERVER['REDIRECT_URL'] . '?ids=' . ($_POST['ids'] ?? '') . '&editItem=' . $obj->id;
    header("Location: " . $redirectUrl);
    exit;

// Удаление значения справочника
elseif (isset($_GET['deleteItem'])) :

    $id = $_GET['deleteItem'];
    $obj = DirectoriesValues::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . ($ids ?? ''));
        exit;
    }
    
    // Удаление изображения
    if (($config['values_fields']['image']['enabled'] ?? false) && !empty($obj->image) && file_exists(ROOT . $obj->image)) {
        unlink(ROOT . $obj->image);
    }
    
    $obj->delete();

    $_SESSION['notice'] = 'Удалено';

    header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . ($ids ?? ''));
    exit;

// Режим добавления/редактирования/копирования справочника
elseif (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new Directories();
    
    $title = 'Добавление справочника';
    $id = false;
    if(!empty($_GET['edit'])) {
        $id = $_GET['edit'];
        $title = 'Редактирование справочника';
    }
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        $id = $_GET['copy'];
        $title = 'Копирование справочника';
    }

    if ($id) {
        $obj = Directories::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }

    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться к списку</a>
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
                    $config['fields']['name']['title'] ?? 'Название справочника', 
                    'name', 
                    $obj->name, 
                    1, 
                    '', 
                    '', 
                    'placeholder="' . ($config['fields']['name']['placeholder'] ?? '') . '"'
                ) ?>
            <?php endif; ?>

            <?= Form::submit($id, $obj->id, 'Сохранить', '') ?>

        </form>
    </div>

<?php
// Сохранение справочника
elseif (isset($_POST['add']) || isset($_POST['edit'])) :

    $id = $_POST['edit'] ?? 0;
    
    // Инициализируем объект $obj в зависимости от ситуации
    if (isset($_POST['edit']) && $id > 0) {
        // Редактирование существующей записи
        $obj = Directories::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        // Добавление новой записи или копирование
        $obj = new Directories();
        
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
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    // Исправленный редирект после сохранения справочника
    $redirectUrl = $_SERVER['REDIRECT_URL'] . '?edit=' . $obj->id;
    header("Location: " . $redirectUrl);
    exit;

// Удаление справочника
elseif (isset($_GET['delete'])) :

    $id = $_GET['delete'];
    $obj = Directories::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    // Получаем все значения справочника для удаления изображений
    $items = DirectoriesValues::findWhere("WHERE directory_id=" . $id);
    if (!empty($items)) {
        foreach ($items as $item) {
            // Удаляем изображение значения
            if (($config['values_fields']['image']['enabled'] ?? false) && !empty($item->image) && file_exists(ROOT . $item->image)) {
                unlink(ROOT . $item->image);
            }
            $item->delete();
        }
    }
    
    $obj->delete();

    $_SESSION['notice'] = 'Удалено';

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;

// Основной режим - список справочников или значений
else :
    $title = 'Справочники';
    $add = 'справочник';

    // Если не выбран конкретный справочник - показываем список справочников
    if (empty($ids)):

        include ROOT . '/private/views/components/head.php';

        $objs = Directories::findAll('name ASC, id ASC');

        if (!empty($objs)): ?>
            <div class="table_container">
                <div class="table_header">
                    <?php if ($config['list']['info']['enabled'] ?? false): ?>
                        <div class="info"><?= $config['list']['info']['title'] ?? 'Название справочника' ?></div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                    <?php endif; ?>
                    
                    <div class="actions"></div>
                </div>
                <div class="table_body">
                <?php foreach ($objs as $obj): 
                    // Получаем количество значений в справочнике
                    $count = DirectoriesValues::count("WHERE directory_id = " . $obj->id);
                ?>
                    <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                        <?php if ($config['list']['info']['enabled'] ?? false): ?>
                            <div class="info">
                                <div class="name">
                                    <a href='?ids=<?= $obj->id ?>'>
                                        <?= $obj->name ?>
                                    </a>
                                </div>
                                <div class="comment">Значений: <?= $count ?></div>
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
        <?php else: ?>
            <div class='not_found'>Ничего не найдено</div>
        <?php endif;

    // Если выбран конкретный справочник - показываем его значения
    else:
        $directory = Directories::findById($ids); 
        if (!$directory) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        
        $title = 'Справочник: ' . $directory->name;
        $back = true;

        // Добавляем пагинацию для значений справочника
        $where = "WHERE directory_id = " . $directory->id;
        
        // Добавляем поиск, если он есть
        $search = trim($_GET['search'] ?? '');
        if (!empty($search) && ($config['values_filters']['search'] ?? false)) {
            $where .= " AND (`value` like '%{$search}%')";
        }
        
        // Вызов пагинации с сохранением параметров
        $perPage = $_SESSION['directoriesvalues']['per_page'] ?? ($config['values_pagination']['default_per_page'] ?? 20);
        $order_by = $config['values_pagination']['order_by'] ?? 'ORDER BY rate DESC, id ASC';
        
        // Сохраняем дополнительные параметры для пагинации
        $additionalParams = ['ids' => $ids];

        $result = Pagination::create(
            modelClass: DirectoriesValues::class,
            where: $where,
            order: $order_by,
            defaultItemsPerPage: $perPage,
            additionalParams: $additionalParams
        );
        
        $objs = $result['items'];
        $pagination = $result['pagination'];
        $totalCount = $result['totalCount'];

        include ROOT . '/private/views/components/head.php'; 
    ?>

        <div class="table_container">
            <div class="table_header">
                <?php if ($config['values_list']['handler'] ?? false): ?>
                    <div class="handler_block"></div>
                <?php endif; ?>
                
                <?php if ($config['values_list']['info']['enabled'] ?? false): ?>
                    <div class="info"><?= $config['values_list']['info']['title'] ?? 'Значение' ?></div>
                <?php endif; ?>
                
                <?php if ($config['values_list']['edit_date']['enabled'] ?? false): ?>
                    <div class="modified_date"><?= $config['values_list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body<?= ($totalCount <= $perPage && empty($search) && ($config['values_list']['handler'] ?? false)) ? ' sortbox-items' : '' ?>">
            <?php if (!empty($objs)): 
                foreach ($objs as $obj): ?>
                    <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                        <?php if ($config['values_list']['handler'] ?? false): ?>
                            <div class="handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($search)) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                        <?php endif; ?>
                        
                        <?php if ($config['values_list']['info']['enabled'] ?? false): ?>
                            <div class="info">
                                <div class="name"><?= $obj->value ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($config['values_list']['edit_date']['enabled'] ?? false): ?>
                            <div class="modified_date">
                                <?= $obj->edit_date ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php include ROOT.'/private/views/components/actions.php' ?>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="table_empty">
                    <div class="not_found">Значений не найдено</div>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <?= !empty($pagination) ? $pagination : '' ?>

    <?php endif; ?>

<?php endif; ?>