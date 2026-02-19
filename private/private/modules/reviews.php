<?php
// Загружаем конфигурацию для отзывов
$config = require_once ROOT . '/config/modules/reviews.php';

use app\Models\Reviews;
use app\FileUpload;
use app\Helpers;
use app\Pagination;
use app\Form;

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new Reviews();
    
    // Устанавливаем значения по умолчанию
    $obj->show = 1;
    $obj->rate = 0;
    $obj->stars = 5;
    $obj->edit_date = date('Y-m-d H:i:s');
    
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
        $obj = Reviews::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }
    
    if(!empty($_GET['copy']) && $config['actions']['copy']) {
        // Очищаем файлы при копировании
        $obj->image = '';
        $obj->video = '';
        $id = false;
    }

    // Убедимся, что $obj->show всегда boolean
    $obj->show = (bool)($obj->show ?? 1);

    if(!empty($obj->date)) $obj->date = date('Y-m-d', strtotime($obj->date));

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

            <div class="flex3">
                <?php if ($config['fields']['show']['enabled'] ?? false): ?>
                    <?= Form::checkbox('show', (bool)$obj->show, $config['fields']['show']['title'] ?? 'Показывать на сайте', 1, null) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['date']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['date']['title'] ?? 'Дата отзыва', 'date', $obj->date, 0, 'date', '', '') ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                <?php endif; ?>
            </div>

            <?php if ($config['fields']['stars']['enabled'] ?? false): ?>
                <?= Form::radio(
                    $config['fields']['stars']['title'] ?? 'Количество звёзд',
                    'stars',
                    $config['fields']['stars']['options'] ?? [5 => '5 звёзд'],
                    $obj->stars,
                    'my-custom-class',
                    true,
                    'normal'
                ) ?>
            <?php endif; ?>

            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['name']['title'] ?? 'Имя клиента', 'name', $obj->name, 0, '', '', '') ?>
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
            
            <?php if ($config['fields']['video']['enabled'] ?? false): ?>
                <?= Form::file(
                    $config['fields']['video']['title'] ?? 'Видео отзыва', 
                    'video', 
                    $obj, 
                    '', 
                    'accept="video/*"'
                ) ?>
            <?php endif; ?>
            
            <?php if ($config['fields']['text']['enabled'] ?? false): ?>
                <?= Form::textarea(
                    $config['fields']['text']['title'] ?? 'Текст отзыва', 
                    'text', 
                    $obj->text, 
                    150, 
                    'maxlength="511" placeholder="Максимум 511 символов"'
                ) ?>
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
        $obj = Reviews::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        // Добавление новой записи или копирование
        $obj = new Reviews();
        
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
    
    if ($config['fields']['stars']['enabled'] ?? false) {
        $obj->stars = (int)($_POST['stars'] ?? 5);
    }
    
    if ($config['fields']['rate']['enabled'] ?? false) {
        $obj->rate = (int)($_POST['rate'] ?? 0);
    }
    
    if ($config['fields']['show']['enabled'] ?? false) {
        $obj->show = (int)($_POST['show'] ?? 0);
    }
    
    // Обработка даты
    if ($config['fields']['date']['enabled'] ?? false) {
        $obj->date = trim($_POST['date']) ?? '';
    }
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    // Загрузка изображения
    if ($config['fields']['image']['enabled'] ?? false) {
        $width = $config['fields']['image']['width'] ?? 300;
        $height = $config['fields']['image']['height'] ?? 300;
        
        FileUpload::uploadImage(
            'image', 
            get_class($obj), 
            'image', 
            $obj->id, 
            $width, 
            $height, 
            '/public/src/images/reviews/', 
            0
        );
    }
    
    // Загрузка видео файла
    if ($config['fields']['video']['enabled'] ?? false) {
        FileUpload::uploadFile(
            'video', 
            get_class($obj), 
            'video', 
            $obj->id, 
            '/public/src/videos/reviews/'
        );
    }

    header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $id = $_GET['delete'];
    $obj = Reviews::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    // Удаление изображения, если оно есть
    if (($config['fields']['image']['enabled'] ?? false) && !empty($obj->image) && file_exists(ROOT . $obj->image)) {
        unlink(ROOT . $obj->image);
    }
    
    // Удаление видео файла, если он есть
    if (($config['fields']['video']['enabled'] ?? false) && !empty($obj->video) && file_exists(ROOT . $obj->video)) {
        unlink(ROOT . $obj->video);
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

    $perPage = $_SESSION['reviews']['per_page'] ?? $config['pagination']['default_per_page'];
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY rate DESC, date DESC, id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: Reviews::class,
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
                    <div class="pole handler_block"></div>
                <?php endif; ?>
                
                <?php if (($config['list']['image']['enabled'] ?? false) && ($config['fields']['image']['enabled'] ?? false)): ?>
                    <div class="pole image_preview"><?= $config['list']['image']['title'] ?? 'Фото' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['info']['enabled'] ?? false): ?>
                    <div class="pole info"><?= $config['list']['info']['title'] ?? 'Отзыв' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['stars']['enabled'] ?? false): ?>
                    <div class="pole category"><?= $config['list']['stars']['title'] ?? 'Оценка' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="pole modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="pole actions"></div>
            </div>
            <div class="table_body<?= ($totalCount <= $perPage && empty($_GET['search']) && $config['list']['handler']) ? ' sortbox-items' : '' ?>">
            <?php foreach ($objs as $obj): ?>

                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if ($config['list']['handler']): ?>
                        <div class="pole handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($_GET['search'])) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['image']['enabled'] ?? false) && ($config['fields']['image']['enabled'] ?? false)): ?>
                        <div class="pole image_preview">
                            <?php if (!empty($obj->image)): ?>
                                <img src="<?= $obj->image ?>" alt="<?= htmlspecialchars($obj->name, ENT_QUOTES, 'UTF-8') ?>" width="50" height="50">
                            <?php else: ?>
                                <div class="no-image">Нет фото</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['info']['enabled'] ?? false): ?>
                        <div class="pole info">
                            <? if(!empty($obj->date)): ?>
                                <div class="date"><?= date('Y-m-d', strtotime($obj->date)) ?></div>
                            <? endif; ?>
                            <div class="name"><?= $obj->name ?></div>
                            <?php if ($config['fields']['text']['enabled'] ?? false && !empty($obj->text)): ?>
                                <div class="comment"><?= mb_substr($obj->text, 0, 100) . (mb_strlen($obj->text) > 100 ? '...' : '') ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['stars']['enabled'] ?? false): ?>
                        <div class="pole category">
                            <?= str_repeat('★', $obj->stars) . str_repeat('☆', 5 - $obj->stars) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="pole modified_date">
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