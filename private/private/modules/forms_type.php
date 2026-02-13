<?php
// Загружаем конфигурацию для "Формы заявок"
$config = require_once ROOT . '/config/modules/forms_type.php';

use app\Models\FormsType;
use app\Models\Pages;
use app\FileUpload;
use app\Helpers;
use app\Pagination;
use app\Form;

$pageUrl = '';

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new FormsType();
    
    // Устанавливаем значения по умолчанию
    $obj->edit_date = date('Y-m-d H:i:s', time());
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
        $obj = FormsType::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }
    
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        // Очищаем файлы при копировании
        $obj->image = '';
        $id = false;
    }

    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться назад</a>
            <?php if (!empty($pageUrl) && $config['actions']['show']): ?>
                <a href='<?= $pageUrl ?>' class='btn btn_white' rel='external'>Смотреть на сайте</a>
            <?php endif; ?>
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
                <?= Form::input($config['fields']['name']['title'] ?? 'Название формы', 'name', $obj->name, 0, '', '', '') ?>
            <?php endif; ?>

            <?php if ($config['fields']['image']['enabled'] ?? false): ?>
                <?= Form::image(
                    $config['fields']['image']['title'] . ' (' . 
                    ($config['fields']['image']['width'] ?? 862) . '×' . 
                    ($config['fields']['image']['height'] ?? 658) . ')', 
                    'image', 
                    $obj, 
                    false, 
                    0
                ) ?>
            <?php endif; ?>

            <?php if (($config['fields']['text_block']['enabled'] ?? false) && 
                     (($config['fields']['text_block']['title_field']['enabled'] ?? false) || 
                      ($config['fields']['text_block']['text']['enabled'] ?? false) || 
                      ($config['fields']['text_block']['text2']['enabled'] ?? false))): ?>
            <fieldset class="input_block">
                <legend><?= $config['fields']['text_block']['title'] ?? 'Текстовый блок' ?></legend>
            
                <?php if ($config['fields']['text_block']['title_field']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['text_block']['title_field']['title'] ?? 'Заголовок над описанием', 
                        'title', 
                        $obj->title, 
                        0, 
                        '', 
                        '', 
                        ''
                    ) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['text_block']['text']['enabled'] ?? false): ?>
                    <?= Form::textarea(
                        $config['fields']['text_block']['text']['title'] ?? 'Описание', 
                        'text', 
                        $obj->text, 
                        100, 
                        ''
                    ) ?>
                <?php endif; ?>

                <?php if ($config['fields']['text_block']['text2']['enabled'] ?? false): ?>
                    <?= Form::textarea(
                        $config['fields']['text_block']['text2']['title'] ?? 'Дополнительное описание', 
                        'text2', 
                        $obj->text2, 
                        100, 
                        ''
                    ) ?>
                <?php endif; ?>

            </fieldset>
            <?php endif; ?>

            <?php if (($config['fields']['button_block']['enabled'] ?? false) && 
                     (($config['fields']['button_block']['button_name']['enabled'] ?? false) || 
                      ($config['fields']['button_block']['button_link']['enabled'] ?? false))): ?>
            <fieldset class="input_block">
                <legend><?= $config['fields']['button_block']['title'] ?? 'Кнопка' ?></legend>
                <?php if ($config['fields']['button_block']['button_name']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['button_block']['button_name']['title'] ?? 'Текст на кнопке', 
                        'button_name', 
                        $obj->button_name, 
                        0, 
                        '', 
                        '', 
                        ''
                    ) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['button_block']['button_link']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['button_block']['button_link']['title'] ?? 'Ссылка с кнопки', 
                        'button_link', 
                        $obj->button_link, 
                        0, 
                        '', 
                        '', 
                        ''
                    ) ?>
                <?php endif; ?>
            </fieldset>
            <?php endif; ?>

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
        $obj = FormsType::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        // Добавление новой записи или копирование
        $obj = new FormsType();
        
        // Если это копирование из другой записи
        if (isset($_POST['copy_from']) && $_POST['copy_from'] && $config['actions']['copy']) {
            $_SESSION['notice'] = 'Скопировано';
        } else {
            $_SESSION['notice'] = 'Добавлено';
        }
    }

    // Заполняем данные из формы с проверкой доступности полей
    if ($config['fields']['name']['enabled'] ?? false) {
        $obj->name = trim($_POST['name'] ?? '');
    }
    
    // Текстовый блок
    if (($config['fields']['text_block']['enabled'] ?? false) && 
        ($config['fields']['text_block']['title_field']['enabled'] ?? false)) {
        $obj->title = trim($_POST['title'] ?? '');
    }
    
    if (($config['fields']['text_block']['enabled'] ?? false) && 
        ($config['fields']['text_block']['text']['enabled'] ?? false)) {
        $obj->text = trim($_POST['text'] ?? '');
    }
    
    if (($config['fields']['text_block']['enabled'] ?? false) && 
        ($config['fields']['text_block']['text2']['enabled'] ?? false)) {
        $obj->text2 = trim($_POST['text2'] ?? '');
    }
    
    // Блок кнопки
    if (($config['fields']['button_block']['enabled'] ?? false) && 
        ($config['fields']['button_block']['button_name']['enabled'] ?? false)) {
        $obj->button_name = trim($_POST['button_name'] ?? '');
    }
    
    if (($config['fields']['button_block']['enabled'] ?? false) && 
        ($config['fields']['button_block']['button_link']['enabled'] ?? false)) {
        $obj->button_link = trim($_POST['button_link'] ?? '');
    }
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    // Загрузка изображения
    if ($config['fields']['image']['enabled'] ?? false) {
        $width = $config['fields']['image']['width'] ?? 862;
        $height = $config['fields']['image']['height'] ?? 658;
        $path = '/public/src/images/forms_type/';
        
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
    $obj = FormsType::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    // Удаление изображения, если оно есть
    if (($config['fields']['image']['enabled'] ?? false) && !empty($obj->image) && file_exists(ROOT . $obj->image)) {
        unlink(ROOT . $obj->image);
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
        
        // Поиск по полям текстового блока
        if (($config['fields']['text_block']['enabled'] ?? false)) {
            if (($config['fields']['text_block']['title_field']['enabled'] ?? false)) {
                $searchFields[] = "`title` like '%{$search}%'";
            }
            if (($config['fields']['text_block']['text']['enabled'] ?? false)) {
                $searchFields[] = "`text` like '%{$search}%'";
            }
            if (($config['fields']['text_block']['text2']['enabled'] ?? false)) {
                $searchFields[] = "`text2` like '%{$search}%'";
            }
        }
        
        // Поиск по полям блока кнопки
        if (($config['fields']['button_block']['enabled'] ?? false)) {
            if (($config['fields']['button_block']['button_name']['enabled'] ?? false)) {
                $searchFields[] = "`button_name` like '%{$search}%'";
            }
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

    $perPage = $_SESSION['forms_type']['per_page'] ?? $config['pagination']['default_per_page'];
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: FormsType::class,
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
                
                <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                    <div class="pole info"><?= $config['list']['name']['title'] ?? 'Название формы' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="pole modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="pole actions"></div>
            </div>
            <div class="table_body">
            <?php foreach ($objs as $obj): ?>

                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                                        
                    <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                        <div class="pole info">
                            <div class="title"><?= $config['list']['name']['title'] ?? 'Название формы' ?></div>
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