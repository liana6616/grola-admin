<?php
// Загружаем конфигурацию для готовой продукции
$config = require_once ROOT . '/config/modules/finished_products.php';

use app\Models\FinishedProducts;
use app\Models\Admins;
use app\Helpers;
use app\Pagination;
use app\Form;

if(!empty($_GET['parent'])) $parent = intval($_GET['parent']);
else $parent = 0;

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new FinishedProducts();
    
    // Устанавливаем значения по умолчанию
    $obj->show = 1;
    $obj->rate = 0;
    $obj->parent = $parent;
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
        $obj = FinishedProducts::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
            exit;
        }
    }

    // Убедимся, что $obj->show всегда boolean
    $obj->show = (bool)($obj->show ?? 1);

    // Получаем хлебные крошки
    $bread = FinishedProducts::adminBreadCrumbs($parent);
    $breadcrumbs = FinishedProducts::adminBread($bread, 1);
    
    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>?parent=<?= $parent ?>' class='btn btn_white btn_back'>Вернуться назад</a>
        </div>
    </div>
    
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form' id='edit_form'
              enctype='multipart/form-data'>
            
            <?php if (isset($_GET['copy']) && ($config['actions']['copy'] ?? false)): ?>
                <!-- При копировании сохраняем ID оригинала как hidden поле -->
                <input type="hidden" name="copy_from" value="<?= $_GET['copy'] ?>">
            <?php endif; ?>

            <!-- Блок хлебных крошек -->
            <?php if (!empty($breadcrumbs)): ?>
            <div class="breadcrumbs_block">
                <?= $breadcrumbs ?>
            </div>
            <?php endif; ?>

            <div class="flex3">
                <?php if ($config['fields']['show']['enabled'] ?? false): ?>
                    <?= Form::checkbox('show', (bool)$obj->show, $config['fields']['show']['title'] ?? 'Показывать на сайте', 1, null) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг (сортировка)', 'rate', $obj->rate, 0, 'number', '', '') ?>
                <?php endif; ?>
            </div>

            <?php if ($config['fields']['parent']['enabled'] ?? false): ?>
                <?php
                // Получаем список родительских категорий
                // Исключаем текущую категорию и её дочерние
                $excludeId = $id ?: 0;
                
                $parentCategories = FinishedProducts::where("WHERE id != ? ORDER BY name ASC", 
                                                           [$excludeId]);
                
                $currentParentId = $obj->parent;
                ?>
                <?= Form::select($config['fields']['parent']['title'] ?? 'Родительская категория', 'parent', $parentCategories, $currentParentId, true, 'Корневой уровень', 'name', 0, '', 0, '') ?>
            <?php endif; ?>

            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['name']['title'] ?? 'Название', 'name', $obj->name, 1, '', '', '') ?>
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
    $parent = intval($_POST['parent'] ?? 0);
    
    // Инициализируем объект $obj в зависимости от ситуации
    if (isset($_POST['edit']) && $id > 0) {
        // Редактирование существующей записи
        $obj = FinishedProducts::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
            exit;
        }
    } else {
        // Добавление новой записи
        $obj = new FinishedProducts();
    }
    
    $_SESSION['notice'] = 'Добавлено';
    if(isset($_POST['edit'])) $_SESSION['notice'] = 'Изменено';
    if(isset($_POST['copy_from'])) $_SESSION['notice'] = 'Скопировано';

    // Заполняем данные из формы
    $obj->name = ($config['fields']['name']['enabled'] ?? false) ? trim($_POST['name'] ?? '') : '';
    $obj->parent = ($config['fields']['parent']['enabled'] ?? false) ? (int)$_POST['parent'] : 0;
    $obj->rate = ($config['fields']['rate']['enabled'] ?? false) ? (int)($_POST['rate'] ?? 0) : 0;
    $obj->show = ($config['fields']['show']['enabled'] ?? false) ? (int)($_POST['show'] ?? 0) : 1;
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    // Проверяем, не пытаемся ли сделать категорию родителем самой себе
    if (!empty($obj->parent) && $obj->parent == $obj->id) {
        $_SESSION['error'] = 'Категория не может быть родителем самой себе';
        header("Location: {$_SERVER['REQUEST_URI']}?parent=" . $parent . "&edit=" . $id);
        exit;
    }

    $obj->save();

    header("Location: {$_SERVER['REQUEST_URI']}?parent=" . $parent . "&edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $id = $_GET['delete'];
    $obj = FinishedProducts::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
        exit;
    }
    
    // Проверяем, есть ли дочерние категории
    $childCategories = FinishedProducts::findWhere('WHERE parent="'.$obj->id.'"');
    if (!empty($childCategories)) {
        $_SESSION['error'] = 'Нельзя удалить категорию, у которой есть дочерние категории';
        header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
        exit;
    }
    
    $obj->delete();

    $_SESSION['notice'] = 'Удалено';

    header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
    exit;

else :
    // Заголовок модуля из конфига
    $title = $config['module']['title'] ?? '';

    $filter = false;

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && ($config['filters']['search'] ?? false)) {
        $whereConditions[] = "(`name` like '%{$search}%')";
        $filter = true;
        // При поиске игнорируем parent
        $parent = 0;
    } else {
        if(!empty($parent)) {
            $whereConditions[] = "parent = " . intval($parent);
        } else {
            $whereConditions[] = "(parent = 0 OR parent IS NULL)";
        }
    }

    // Формируем полное WHERE условие
    $where = '';
    if (!empty($whereConditions)) {
        $where = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    $perPage = $_SESSION['finished_products']['per_page'] ?? ($config['pagination']['default_per_page'] ?? 20);
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY rate DESC, id DESC';

    // Сохраняем дополнительные параметры для пагинации
    $additionalParams = ['parent' => $parent];

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: FinishedProducts::class,
        where: $where,
        order: $order_by,
        defaultItemsPerPage: $perPage,
        additionalParams: $additionalParams
    );
    
    $objs = $result['items'];
    $pagination = $result['pagination'];
    $totalCount = $result['totalCount'];

    // Хлебные крошки для навигации (только если не поиск)
    if(!empty($parent) && empty($search) && $parent != 0) {
        $category = FinishedProducts::findById($parent);
        if ($category) {
            $bread = FinishedProducts::adminBreadCrumbs($parent);
            $breadcrumbs = FinishedProducts::adminBread($bread, 0);
            
            $title = 'Категория: ' . $category->name;
        }
        $back = true;
    }

    include ROOT . '/private/views/components/head.php';

    if (!empty($objs)): ?>
        <div class="table_container">
            <div class="table_header">
                <?php if ($config['list']['handler'] ?? false): ?>
                    <div class="pole handler_block"></div>
                <?php endif; ?>
                
                <?php if (($config['list']['name']['enabled'] ?? false)): ?>
                    <div class="pole info"><?= $config['list']['name']['title'] ?? 'Название' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="pole modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                                    
                <div class="pole actions"></div>
            </div>
            <div class="table_body<?= ($totalCount <= $perPage && empty($_GET['search']) && ($config['list']['handler'] ?? false)) ? ' sortbox-items' : '' ?>">
            <?php foreach ($objs as $obj):
                // Получаем количество дочерних категорий
                $childCount = FinishedProducts::count("WHERE parent = " . $obj->id);
                
                // Определяем ссылку для перехода к дочерним категориям
                $childLinkId = $obj->id;
            ?>
                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if ($config['list']['handler'] ?? false): ?>
                        <div class="pole handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($_GET['search'])) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['name']['enabled'] ?? false)): ?>
                        <div class="pole info">
                            <div class="title"><?= $config['list']['name']['title'] ?? 'Название' ?></div>
                            <div class="name">
                                <?php if(empty($_GET['search']) && empty($filter) && !empty($childLinkId)): ?>
                                    <a href="?parent=<?= $childLinkId ?>" class="pageLink"><?= $obj->name ?></a>
                                <?php else: ?>
                                    <?= $obj->name ?>
                                <?php endif; ?>
                            </div>
                            <?php if($childCount > 0 && empty($_GET['search'])): ?>
                                <div class="comment">Подкатегорий: <?= $childCount ?></div>
                            <?php endif; ?>
                            
                            <?php if(!empty($_GET['search'])): 
                                // Для поиска показываем хлебные крошки
                                $breadForSearch = FinishedProducts::adminBreadCrumbs($obj->parent);
                                if(!empty($breadForSearch)):
                                    $c = count($breadForSearch) - 1;
                            ?>
                                    <div class="comment breadcrumbs">
                                        <a href="/<?= ADMIN_LINK ?>/finished_products" class="path">Главная</a>
                                        <?php foreach($breadForSearch AS $key=>$item): ?>
                                            <?php if($key < $c): ?>
                                                <span class="path_arr">/</span><a href="<?= $item['url'] ?>" class="path"><?= $item['name'] ?></a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
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