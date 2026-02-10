<?php
// Загружаем конфигурацию для мессенджеров
$config = require_once ROOT . '/config/modules/messengers.php';

use app\Models\Messengers;
use app\FileUpload;
use app\Helpers;
use app\Pagination;
use app\Form;

$title = 'Мессенджеры и социальные сети';
$add = 'мессенджер/соцсеть';

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new Messengers();
    
    // Устанавливаем значения по умолчанию
    $obj->show = 1;
    $obj->rate = 0;
    
    $title_form = 'Добавление';
    $id = false;
    
    if(!empty($_GET['edit'])) {
        $id = $_GET['edit'];
        $title_form = 'Редактирование';
    }
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        $id = $_GET['copy'];
        $title_form = 'Копирование';
    }
    
    if(!empty($id)) {
        $obj = Messengers::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }
    
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        // Очищаем изображения при копировании
        $obj->image = '';
        $obj->image_hover = '';
        $obj->image2 = '';
        $obj->image2_hover = '';
        $id = false;
    }

    // Убедимся, что $obj->show всегда boolean
    $obj->show = (bool)($obj->show ?? 1);

    ?>
    <div class="editHead">
        <h1><?= $title_form . ' ' . $add ?></h1>
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

            <div class="flex2">
                <?php if ($config['fields']['show']['enabled'] ?? false): ?>
                    <?= Form::checkbox('show', (bool)$obj->show, $config['fields']['show']['title'] ?? 'Показывать на сайте', 1, null) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                <?php endif; ?>
            </div>

            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['name']['title'] ?? 'Название', 'name', $obj->name, 0, '', '', '') ?>
            <?php endif; ?>
            
            <?php if ($config['fields']['link']['enabled'] ?? false): ?>
                <?= Form::input(
                    $config['fields']['link']['title'] ?? 'Ссылка', 
                    'link', 
                    $obj->link, 
                    0, 
                    '', 
                    '', 
                    'placeholder="' . ($config['fields']['link']['placeholder'] ?? '') . '"'
                ) ?>
            <?php endif; ?>

            <?php if ($config['fields']['image']['enabled'] ?? false || $config['fields']['image_hover']['enabled'] ?? false): ?>
                <div class="flex2 top">
                    <?php if ($config['fields']['image']['enabled'] ?? false): ?>
                        <?= Form::image(
                            $config['fields']['image']['title'] . ' (' . 
                            ($config['fields']['image']['width'] ?? 50) . '×' . 
                            ($config['fields']['image']['height'] ?? 50) . ')', 
                            'image', 
                            $obj, 
                            '', 
                            0
                        ) ?>
                    <?php endif; ?>
                    
                    <?php if ($config['fields']['image_hover']['enabled'] ?? false): ?>
                        <?= Form::image(
                            $config['fields']['image_hover']['title'] . ' (' . 
                            ($config['fields']['image_hover']['width'] ?? 50) . '×' . 
                            ($config['fields']['image_hover']['height'] ?? 50) . ')', 
                            'image_hover', 
                            $obj, 
                            '', 
                            0
                        ) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($config['fields']['image2']['enabled'] ?? false || $config['fields']['image2_hover']['enabled'] ?? false): ?>
                <div class="flex2 top">
                    <?php if ($config['fields']['image2']['enabled'] ?? false): ?>
                        <?= Form::image(
                            $config['fields']['image2']['title'] . ' (' . 
                            ($config['fields']['image2']['width'] ?? 50) . '×' . 
                            ($config['fields']['image2']['height'] ?? 50) . ')', 
                            'image2', 
                            $obj, 
                            '', 
                            0
                        ) ?>
                    <?php endif; ?>
                    
                    <?php if ($config['fields']['image2_hover']['enabled'] ?? false): ?>
                        <?= Form::image(
                            $config['fields']['image2_hover']['title'] . ' (' . 
                            ($config['fields']['image2_hover']['width'] ?? 50) . '×' . 
                            ($config['fields']['image2_hover']['height'] ?? 50) . ')', 
                            'image2_hover', 
                            $obj, 
                            '', 
                            0
                        ) ?>
                    <?php endif; ?>
                </div>
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
        $obj = Messengers::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        // Добавление новой записи или копирование
        $obj = new Messengers();
        
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
    
    if ($config['fields']['link']['enabled'] ?? false) {
        $obj->link = trim($_POST['link'] ?? '');
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

    // Загрузка иконок
    $image_fields = ['image', 'image_hover', 'image2', 'image2_hover'];
    $upload_path = $config['uploads']['path'] ?? '/public/src/images/messengers/';
    
    foreach ($image_fields as $field) {
        if ($config['fields'][$field]['enabled'] ?? false) {
            $width = $config['fields'][$field]['width'] ?? 50;
            $height = $config['fields'][$field]['height'] ?? 50;
            
            FileUpload::uploadImage(
                $field, 
                get_class($obj), 
                $field, 
                $obj->id, 
                $width, 
                $height, 
                $upload_path, 
                0
            );
        }
    }

    header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $id = $_GET['delete'];
    $obj = Messengers::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    // Удаление всех иконок
    $image_fields = ['image', 'image_hover', 'image2', 'image2_hover'];
    
    foreach ($image_fields as $field) {
        if (($config['fields'][$field]['enabled'] ?? false) && !empty($obj->$field) && file_exists(ROOT . $obj->$field)) {
            unlink(ROOT . $obj->$field);
        }
    }
    
    $obj->delete();

    $_SESSION['notice'] = 'Удалено';

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;

else :
    $filter = true;

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && $config['filters']['search']) {
        $searchFields = [];
        
        if ($config['fields']['name']['enabled'] ?? false) {
            $searchFields[] = "`name` like '%{$search}%'";
        }
        if ($config['fields']['link']['enabled'] ?? false) {
            $searchFields[] = "`link` like '%{$search}%'";
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

    $perPage = $_SESSION['messengers']['per_page'] ?? $config['pagination']['default_per_page'];
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY rate DESC, id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: Messengers::class,
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
                    <div class="image_preview"><?= $config['list']['image']['title'] ?? 'Иконка' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['name']['enabled'] ?? false): ?>
                    <div class="info"><?= $config['list']['name']['title'] ?? 'Название' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body<?= ($totalCount <= $perPage && empty($_GET['search']) && $config['list']['handler']) ? ' sortbox-items' : '' ?>">
            <?php foreach ($objs as $obj): 
                $pageUrl = $obj->link;
                $linkTitle = 'Перейти по ссылке';
            ?>

                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if ($config['list']['handler']): ?>
                        <div class="handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($_GET['search'])) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['image']['enabled'] ?? false) && ($config['fields']['image']['enabled'] ?? false)): ?>
                        <div class="image_preview">
                            <?php if (!empty($obj->image)): ?>
                                <img src="<?= $obj->image ?>" alt="<?= htmlspecialchars($obj->name, ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <div class="no-image">Нет иконки</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['name']['enabled'] ?? false): ?>
                        <div class="info">
                            <div class="name"><?= $obj->name ?></div>
                            <?php if ($config['fields']['link']['enabled'] ?? false && !empty($obj->link)): ?>
                                <a href="<?= $obj->link ?>" target="_blank" rel="noopener noreferrer" class="link">
                                    <?= mb_substr($obj->link, 0, 40) . (mb_strlen($obj->link) > 40 ? '...' : '') ?>
                                </a>
                            <?php endif; ?>
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