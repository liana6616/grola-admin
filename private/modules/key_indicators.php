<?php
// Загружаем конфигурацию для "Ключевые показатели"
$config = require_once ROOT . '/config/modules/key_indicators.php';

use app\Models\KeyIndicators;
use app\Models\Pages;
use app\FileUpload;
use app\Helpers;
use app\Pagination;
use app\Form;

$pageUrl = '';

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new KeyIndicators();
    
    // Устанавливаем значения по умолчанию
    $obj->show = 1;
    $obj->rate = 0;
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
        $obj = KeyIndicators::findById($id);
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

    // Убедимся, что $obj->show всегда boolean
    $obj->show = (bool)($obj->show ?? 1);

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

            <div class="flex3">
                <?php if ($config['fields']['show']['enabled'] ?? false): ?>
                    <?= Form::checkbox('show', (bool)$obj->show, $config['fields']['show']['title'] ?? 'Показывать на сайте', 1, null) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                <?php endif; ?>
            </div>

            <?php if ($config['fields']['image']['enabled'] ?? false): ?>
                <?= Form::image(
                    $config['fields']['image']['title'] . ' (' . 
                    ($config['fields']['image']['width'] ?? 100) . '×' . 
                    ($config['fields']['image']['height'] ?? 100) . ')', 
                    'image', 
                    $obj, 
                    false, 
                    0
                ) ?>
            <?php endif; ?>

            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['name']['title'] ?? 'Заголовок', 'name', $obj->name, 0, '', '', '') ?>
            <?php endif; ?>

            <?php if ($config['fields']['value']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['value']['title'] ?? 'Значение', 'value', $obj->value, 0, '', '', '') ?>
            <?php endif; ?>
            
            <?php if ($config['fields']['text']['enabled'] ?? false): ?>
                <?= Form::textarea($config['fields']['text']['title'] ?? 'Описание', 'text', $obj->text, 100, '') ?>
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
        $obj = KeyIndicators::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        // Добавление новой записи или копирование
        $obj = new KeyIndicators();
        
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
    
    if ($config['fields']['value']['enabled'] ?? false) {
        $obj->value = trim($_POST['value'] ?? '');
    }
    
    if ($config['fields']['text']['enabled'] ?? false) {
        $obj->text = trim($_POST['text'] ?? '');
    }
    
    if ($config['fields']['rate']['enabled'] ?? false) {
        $obj->rate = (int)($_POST['rate'] ?? 0);
    }
    
    if ($config['fields']['show']['enabled'] ?? false) {
        $obj->show = (int)($_POST['show'] ?? 0);
    }
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    // Загрузка изображения
    if ($config['fields']['image']['enabled'] ?? false) {
        $width = $config['fields']['image']['width'] ?? 100;
        $height = $config['fields']['image']['height'] ?? 100;
        $path = '/public/src/images/key_indicators/';
        
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
    $obj = KeyIndicators::findById($id);
    
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
    $title = 'Ключевые показатели';
    $add = 'показатель';

    $filter = false;

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && $config['filters']['search']) {
        $searchFields = [];
        
        if ($config['fields']['name']['enabled'] ?? false) {
            $searchFields[] = "`name` like '%{$search}%'";
        }
        if ($config['fields']['value']['enabled'] ?? false) {
            $searchFields[] = "`value` like '%{$search}%'";
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

    $perPage = $_SESSION['key_indicators']['per_page'] ?? $config['pagination']['default_per_page'];
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY rate DESC, id ASC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: KeyIndicators::class,
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
                
                <?php if (($config['list']['image']['enabled'] ?? false) && ($config['fields']['image']['enabled'] ?? false)): ?>
                    <div class="image"><?= $config['list']['image']['title'] ?? 'Иконка' ?></div>
                <?php endif; ?>
                
                <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                    <div class="info"><?= $config['list']['name']['title'] ?? 'Заголовок' ?></div>
                <?php endif; ?>
                
                <?php if (($config['list']['value']['enabled'] ?? false) && ($config['fields']['value']['enabled'] ?? false)): ?>
                    <div class="info"><?= $config['list']['value']['title'] ?? 'Значение' ?></div>
                <?php endif; ?>
                
                <?php if (($config['list']['text']['enabled'] ?? false) && ($config['fields']['text']['enabled'] ?? false)): ?>
                    <div class="description"><?= $config['list']['text']['title'] ?? 'Описание' ?></div>
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
                    
                    <?php if (($config['list']['image']['enabled'] ?? false) && ($config['fields']['image']['enabled'] ?? false)): ?>
                        <div class="image">
                            <?php if (!empty($obj->image)): ?>
                                <img src="<?= $obj->image ?>" alt="<?= htmlspecialchars($obj->name, ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <div class="no-image">Нет иконки</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                        <div class="info">
                            <div class="name"><?= $obj->name ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['value']['enabled'] ?? false) && ($config['fields']['value']['enabled'] ?? false)): ?>
                        <div class="info">
                            <div class="value-text"><?= $obj->value ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['text']['enabled'] ?? false) && ($config['fields']['text']['enabled'] ?? false)): ?>
                        <div class="description">
                            <div class="text"><?= $obj->text ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="modified_date"><?= $obj->edit_date ?></div>
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