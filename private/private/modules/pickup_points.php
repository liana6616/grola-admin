<?php
// Загружаем конфигурацию для пунктов самовывоза
$config = require_once ROOT . '/config/modules/pickup_points.php';

use app\Models\PickupPoints;
use app\FileUpload;
use app\Form;
use app\Pagination;

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new PickupPoints();
    
    // Устанавливаем значения по умолчанию
    $obj->show = 1;
    $obj->rate = 0;
    $obj->edit_date = date('Y-m-d H:i:s', time());
    
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
        $obj = PickupPoints::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }

    // Убедимся, что $obj->show всегда boolean
    $obj->show = (bool)($obj->show ?? 1);

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
                
                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг (сортировка)', 'rate', $obj->rate, 0, 'number', '', '') ?>
                <?php endif; ?>
            </div>

            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['name']['title'] ?? 'Название пункта', 'name', $obj->name, 1, '', '', '') ?>
            <?php endif; ?>
            
            <?php if ($config['fields']['address']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['address']['title'] ?? 'Адрес', 'address', $obj->address, 1, '', '', '') ?>
            <?php endif; ?>
            
            <?php if ($config['fields']['coords']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['coords']['title'] ?? 'Координаты на карте', 'coords', $obj->coords, 0, 'hidden', '', '') ?>
                
                <?
                    if(empty($obj->coords)) $obj->coords = '59.938784, 30.314997';
                    $c = explode(',', $obj->coords);
                ?>
                <div class='map' id='YMapsID' data-lat='<?= $c[0] ?>' data-lng='<?= $c[1] ?>'></div>
            <?php endif; ?>
            
            <?php if ($config['fields']['text']['enabled'] ?? false): ?>
                <?= Form::textarea($config['fields']['text']['title'] ?? 'Описание', 'text', $obj->text, 140, '') ?>
            <?php endif; ?>
            
            <?php if ($config['fields']['image']['enabled'] ?? false): ?>
                <?= Form::image(
                    $config['fields']['image']['title'] . ' (' . $config['fields']['image']['width'] . 'x' . $config['fields']['image']['height'] . ')', 
                    'image', 
                    $obj, 
                    0, 
                    0
                ) ?>
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
        $obj = PickupPoints::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    } else {
        // Добавление новой записи
        $obj = new PickupPoints();
    }
    
    $_SESSION['notice'] = 'Добавлено';
    if(isset($_POST['edit'])) $_SESSION['notice'] = 'Изменено';
    if(isset($_POST['copy_from'])) $_SESSION['notice'] = 'Скопировано';

    // Заполняем данные из формы
    $obj->name = ($config['fields']['name']['enabled'] ?? false) ? trim($_POST['name'] ?? '') : '';
    $obj->address = ($config['fields']['address']['enabled'] ?? false) ? trim($_POST['address'] ?? '') : '';
    $obj->coords = ($config['fields']['coords']['enabled'] ?? false) ? trim($_POST['coords'] ?? '') : '';
    $obj->text = ($config['fields']['text']['enabled'] ?? false) ? trim($_POST['text'] ?? '') : '';
    $obj->rate = ($config['fields']['rate']['enabled'] ?? false) ? (int)($_POST['rate'] ?? 0) : 0;
    $obj->show = ($config['fields']['show']['enabled'] ?? false) ? (int)($_POST['show'] ?? 0) : 1;
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();
    
    // Загрузка изображения
    if ($config['fields']['image']['enabled'] ?? false) {
        FileUpload::uploadImage(
            'image', 
            get_class($obj), 
            'image', 
            $obj->id, 
            $config['fields']['image']['width'] ?? 1350, 
            $config['fields']['image']['height'] ?? 600, 
            '/public/src/images/pickup_points/', 
            0
        );
    }

    header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $obj = PickupPoints::findById($_GET['delete']);
    
    // Удаляем изображение
    if ($config['fields']['image']['enabled'] ?? false) {
        FileUpload::deleteImage($obj->image, '/public/src/images/pickup_points/');
    }
    
    $obj->delete();
    $_SESSION['notice'] = 'Удалено';

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
        
        if ($config['fields']['name']['enabled'] ?? false) {
            $searchFields[] = "`name` like '%{$search}%'";
        }
        if ($config['fields']['address']['enabled'] ?? false) {
            $searchFields[] = "`address` like '%{$search}%'";
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

    $perPage = $_SESSION['pickup_points']['per_page'] ?? $config['pagination']['default_per_page'];
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY rate DESC, id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: PickupPoints::class,
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
                
                <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                    <div class="pole info"><?= $config['list']['name']['title'] ?? 'Название' ?></div>
                <?php endif; ?>
                
                <?php if (($config['list']['address']['enabled'] ?? false) && ($config['fields']['address']['enabled'] ?? false)): ?>
                    <div class="pole info"><?= $config['list']['address']['title'] ?? 'Адрес' ?></div>
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
                    
                    <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                        <div class="pole info">
                            <div class="title"><?= $config['list']['name']['title'] ?? 'Название' ?></div>
                            <div class="name"><?= $obj->name ?></div>
                            <?php if (!empty($obj->image) && ($config['fields']['image']['enabled'] ?? false)): ?>
                                <div class="mini-photo">
                                    <img src="/public/src/images/pickup_points/50x50_<?= $obj->image ?>" alt="">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['address']['enabled'] ?? false) && ($config['fields']['address']['enabled'] ?? false)): ?>
                        <div class="pole info">
                            <div class="title"><?= $config['list']['address']['title'] ?? 'Адрес' ?></div>
                            <div class="name"><?= $obj->address ?></div>
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