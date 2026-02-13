<?php

use app\Models\Categories;
use app\Models\Admins;
use app\Models\Gallery;
use app\Models\Files;
use app\Models\ParamsTemplates;
use app\FileUpload;
use app\Helpers;
use app\Form;
use app\Pagination;

$configPath = '/config/modules/categories.php';

// Проверяем существование конфигурационного файла
if (!file_exists(ROOT.$configPath)):
    ?>
    <div class="alert alert-danger">
        Внимание: отсутствует конфигурационный файл: <strong><?= $configPath ?></strong>
    </div>
<?php else:

    $config = require_once ROOT.$configPath;

    if(!empty($_GET['parent'])) $parent = intval($_GET['parent']);
    else $parent = 0;

    if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

        $obj = new Categories();
        
        // Устанавливаем значения по умолчанию
        $obj->show = 1; // Явно устанавливаем boolean значение
        $obj->rate = 0;
        $obj->parent = $parent;
        $obj->edit_date = date('Y-m-d H:i:s', time());
        
        $title = 'Добавление категории';
        $id = false;
        
        if(!empty($_GET['edit'])) {
            $id = $_GET['edit'];
            $title = 'Редактирование категории';
            $action = 'edit';
        }
        if(!empty($_GET['copy']) && $config['actions']['copy']) {
            $id = $_GET['copy'];
            $title = 'Копирование категории';
            $action = 'copy';
        }
        
        if(!empty($id)) {
            $obj = Categories::findById($id);
            if (!$obj) {
                header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
                exit;
            }
        }
        
        if(!empty($_GET['copy']) && $config['actions']['copy']) {
            $obj->image = '';
            $id = false;
        }

        // Убедимся, что $obj->show всегда boolean
        $obj->show = (bool)($obj->show ?? 1);

        // Получаем хлебные крошки
        $bread = Categories::adminBreadCrumbs($parent);
        $breadcrumbs = Categories::adminBread($bread, 1);
        
        ?>
        <div class="editHead">
            <h1><?= $title ?></h1>
            <div class="button_block">
                <a href='<?= $_SERVER['REDIRECT_URL'] ?>?parent=<?= $parent ?>' class='btn btn_white btn_back'>Вернуться назад</a>
                
                <?php if($config['actions']['open'] && !empty($_GET['edit'])): ?>
                    <a href='<?= Categories::getUrl($id) ?>' class='btn btn_white' rel='external' target="_blank">Смотреть на сайте</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="edit_block">
            <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form' id='edit_form'
                  enctype='multipart/form-data'>
                
                <?php if (isset($_GET['copy']) && $config['actions']['copy']): ?>
                    <!-- При копировании сохраняем ID оригинала как hidden поле -->
                    <input type="hidden" name="copy_from" value="<?= $_GET['copy'] ?>">
                <?php endif; ?>

                <!-- Блок хлебных крошек -->
                <?php if (!empty($breadcrumbs)): ?>
                <div class="breadcrumbs_block">
                    <?= $breadcrumbs ?>
                </div>
                <?php endif; ?>
                
                <!-- Вкладки -->
                <div class="edit_tabs">
                    <div class="edit_tabs_nav">
                        <?php if ($config['fields']['enabled']): ?>
                            <button type="button" class="edit_tab_nav active" data-tab="content"><?= $config['fields']['tab_name'] ?? 'Контент' ?></button>
                        <?php endif; ?>
                        
                        <?php if ($config['seo']['enabled']): ?>
                            <button type="button" class="edit_tab_nav" data-tab="seo"><?= $config['seo']['tab_name'] ?? 'SEO' ?></button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="edit_tabs_content">
                        <!-- Вкладка "Контент" -->
                        <?php if ($config['fields']['enabled']): ?>
                        <div class="edit_tab_content active" id="tab_content">

                            <div class="flex3">
                                <?php if ($config['fields']['show']['enabled'] ?? false): ?>
                                    <?= Form::checkbox('show', (bool)$obj->show, $config['fields']['show']['title'] ?? 'Показывать на сайте', 1, null) ?>
                                <?php endif; ?>
                            </div>

                            <div class="flex2">
                                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['parent']['enabled'] ?? false): ?>
                                    <?php
                                    // Получаем список родительских категорий
                                    // Исключаем текущую категорию
                                    $excludeId = $id ?: 0;
                                    $parentCategories = Categories::getHierarchical($excludeId);
                                    ?>
                                    <?= Form::select($config['fields']['parent']['title'] ?? 'Родительская категория', 'parent', $parentCategories, $obj->parent, true, 'Корневой уровень', 'name', 0, '', 0, '') ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($config['fields']['template_id']['enabled'] ?? false): ?>
                                <?php
                                $templates = ParamsTemplates::where("ORDER BY name ASC");
                                ?>
                                <?= Form::select($config['fields']['template_id']['title'] ?? 'Шаблон параметров', 'template_id', $templates, $obj->template_id, true, 'Без шаблона', 'name', 0, '', 0, '') ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['name']['title'] ?? 'Название категории (H1)', 'name', $obj->name, 1, '', '', '') ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['name_menu']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['name_menu']['title'] ?? 'Название для меню', 'name_menu', $obj->name_menu, 0, '', '', '') ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['url']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['url']['title'] ?? 'Ссылка', 'url', $obj->url, 0, '', '', '') ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['image']['enabled'] ?? false): ?>
                                <?= Form::image(
                                    $config['fields']['image']['title'] . ' (' . 
                                    ($config['fields']['image']['width'] ?? 1200) . 'х' . 
                                    ($config['fields']['image']['height'] ?? 600) . ')', 
                                    'image', 
                                    $obj, 
                                    '', 
                                    0
                                ) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['text']['enabled'] ?? false): ?>
                                <?= Form::textbox($config['fields']['text']['title'] ?? 'Описание категории', 'text', $obj->text) ?>
                            <?php endif; ?>

                        </div>
                        <?php endif; ?>
                        

                        <!-- Вкладка "SEO" -->
                        <?php if ($config['seo']['enabled']): ?>
                        <div class="edit_tab_content" id="tab_seo">

                            <?php if ($config['seo']['title']['enabled'] ?? false): ?>
                                <?= Form::input($config['seo']['title']['title'] ?? 'Title (заголовок страницы)', 'title', $obj->title ?? '', 0, '', '', '') ?>
                                <small>Title: рекомендуется до 70 символов</small>
                            <?php endif; ?>
                            
                            <?php if ($config['seo']['keywords']['enabled'] ?? false): ?>
                                <?= Form::textarea($config['seo']['keywords']['title'] ?? 'Keywords (ключевые слова)', 'keywords', $obj->keywords ?? '', 140, '') ?>
                                <small>Keywords: через запятую, до 1024 символов</small>
                            <?php endif; ?>
                            
                            <?php if ($config['seo']['description']['enabled'] ?? false): ?>
                                <?= Form::textarea($config['seo']['description']['title'] ?? 'Description (описание)', 'description', $obj->description ?? '', 140, '') ?>
                                <small>Description: рекомендуется до 160 символов</small>
                            <?php endif; ?>

                        </div>
                        <?php endif; ?>
                        
                    </div>
                </div>

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
            FileUpload::deleteImageFile();
            $obj = Categories::findById($id);
            if (!$obj) {
                header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . ($_POST['parent'] ?? 0));
                exit;
            }
        } else {
            // Добавление новой записи
            $obj = new Categories();
        }
        
        $_SESSION['notice'] = 'Добавлено';
        if(isset($_POST['edit'])) $_SESSION['notice'] = 'Изменено';
        if(isset($_POST['copy_from'])) $_SESSION['notice'] = 'Скопировано';

        $url = trim($_POST['url'] ?? '');
        if (empty($url) && ($config['fields']['url']['enabled'] ?? false)) {
            $url = Helpers::str2url(trim($_POST['name'] ?? ''));
        }
        
        // Определяем родительскую категорию
        $parentId = 0;
        if ($config['fields']['parent']['enabled'] ?? false) {
            $parentId = (int)($_POST['parent'] ?? 0);
        }

        // Проверяем уникальность URL
        if ($url <> '' && ($config['fields']['url']['enabled'] ?? false)) {
            $i = 1;
            $u = $url;
            
            $existingId = $obj->id ?? 0;
            $cat = Categories::findWhere("WHERE parent='" . $parentId . "' AND url='" . $url . "' AND id<>'" . $existingId . "' LIMIT 1");
            while ($cat) {
                $url = $u . '-' . $i;
                $cat = Categories::findWhere("WHERE parent='" . $parentId . "' AND url='" . $url . "' AND id<>'" . $existingId . "' LIMIT 1");
                $i++;
            }
        }

        // Заполняем данные из формы
        $obj->url = ($config['fields']['url']['enabled'] ?? false) ? $url : '';
        $obj->name = ($config['fields']['name']['enabled'] ?? false) ? trim($_POST['name'] ?? '') : '';
        
        // Для name_menu: если поле включено и есть значение - берем его, иначе берем name
        if ($config['fields']['name_menu']['enabled'] ?? false) {
            $name_menu_input = trim($_POST['name_menu'] ?? '');
            $obj->name_menu = !empty($name_menu_input) ? $name_menu_input : $obj->name;
        } else {
            $obj->name_menu = $obj->name; // Если поле отключено, используем name
        }
        
        $obj->parent = $parentId;
        $obj->template_id = ($config['fields']['template_id']['enabled'] ?? false) ? (int)($_POST['template_id'] ?? 0) : 0;
        $obj->text = ($config['fields']['text']['enabled'] ?? false) ? trim($_POST['text'] ?? '') : '';
        $obj->show = ($config['fields']['show']['enabled'] ?? false) ? (int)($_POST['show'] ?? 0) : 1;
        $obj->rate = ($config['fields']['rate']['enabled'] ?? false) ? (int)($_POST['rate'] ?? 0) : 0;
        
        // SEO поля
        if ($config['seo']['enabled']) {
            $obj->title = ($config['seo']['title']['enabled'] ?? false) ? trim($_POST['title'] ?? '') : '';
            $obj->keywords = ($config['seo']['keywords']['enabled'] ?? false) ? trim($_POST['keywords'] ?? '') : '';
            $obj->description = ($config['seo']['description']['enabled'] ?? false) ? trim($_POST['description'] ?? '') : '';
        }
        
        // Системные поля
        $obj->edit_date = date("Y-m-d H:i:s");
        $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

        // Проверяем, не пытаемся ли сделать категорию родителем самой себе
        if (!empty($obj->parent) && $obj->parent == $obj->id) {
            $_SESSION['error'] = 'Категория не может быть родителем самой себе';
            header("Location: {$_SERVER['REQUEST_URI']}?parent=" . $parentId . "&edit=" . $id);
            exit;
        }

        // Сохраняем запись
        $obj->save();

        // Загрузка изображения
        if (($config['fields']['image']['enabled'] ?? false)) {
            $width = $config['fields']['image']['width'] ?? 1200;
            $height = $config['fields']['image']['height'] ?? 600;
            
            FileUpload::uploadImage(
                'image', 
                get_class($obj), 
                'image', 
                $obj->id, 
                $width, 
                $height, 
                '/public/src/images/categories/', 
                0
            );
        }

        header("Location: {$_SERVER['REQUEST_URI']}?parent=" . $parentId . "&edit=$obj->id");
        exit;
        
    elseif (isset($_GET['delete'])) :

        $id = $_GET['delete'];
        $obj = Categories::findById($id);
        
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
            exit;
        }
        
        // Проверяем, есть ли дочерние категории
        $childCategories = Categories::findWhere('WHERE parent="'.$obj->id.'"');
        if (!empty($childCategories)) {
            $_SESSION['error'] = 'Нельзя удалить категорию, у которой есть дочерние категории';
            header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
            exit;
        }
        
        // Удаление изображения, если оно есть
        if (($config['fields']['image']['enabled'] ?? false) && !empty($obj->image) && file_exists(ROOT . $obj->image)) {
            unlink(ROOT . $obj->image);
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
        $params = [];
        
        $search = trim($_GET['search'] ?? '');
        if (!empty($search) && $config['filters']['search']) {
            $whereConditions[] = "(`name` like '%{$search}%' OR `name_menu` like '%{$search}%' OR `text` like '%{$search}%')";
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

        $perPage = $_SESSION['categories']['per_page'] ?? $config['pagination']['default_per_page'];
        $order_by = $config['pagination']['order_by'] ?? 'ORDER BY rate DESC, id DESC';

        // Сохраняем дополнительные параметры для пагинации
        $additionalParams = ['parent' => $parent];

        // Вызов пагинации
        $result = Pagination::create(
            modelClass: Categories::class,
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
            $categories = Categories::findById($parent);
            if ($categories) {
                $bread = Categories::adminBreadCrumbs($parent);
                $breadcrumbs = Categories::adminBread($bread, 0);
                
                $title = 'Категория: ' . $categories->name;
            }
            $back = true;
        }

        include ROOT . '/private/views/components/head.php';

        if (!empty($objs)): ?>
            <div class="table_container">
                <div class="table_header">
                    <?php if ($config['list']['handler']): ?>
                        <div class="pole handler_block"></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['info']['enabled'] ?? false)): ?>
                        <div class="pole info"><?= $config['list']['info']['title'] ?? 'Категория' ?></div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="pole modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                    <?php endif; ?>
                                        
                    <div class="pole actions"></div>
                </div>
                <div class="table_body<?= ($totalCount <= $perPage && empty($_GET['search']) && $config['list']['handler']) ? ' sortbox-items' : '' ?>">
                <?php foreach ($objs as $obj):
                    // Получаем количество дочерних категорий
                    $childCount = Categories::count("WHERE parent = " . $obj->id);
                ?>

                    <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                        <?php if ($config['list']['handler']): ?>
                            <div class="pole handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($_GET['search'])) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                        <?php endif; ?>
                        
                        <?php if (($config['list']['info']['enabled'] ?? false)): ?>
                            <div class="pole info">
                                <div class="title"><?= $config['list']['info']['title'] ?? 'Категория' ?></div>
                                <div class="name">
                                    <?php if(empty($_GET['search']) && empty($filter)): ?>
                                        <a href="?parent=<?= $obj->id ?>" class="categoryLink"><?= $obj->name_menu ?></a>
                                    <?php else: ?>
                                        <?= $obj->name_menu ?>
                                    <?php endif; ?>
                                </div>
                                <?php if($childCount > 0 && empty($_GET['search'])): ?>
                                    <div class="comment">Подкатегорий: <?= $childCount ?></div>
                                <?php endif; ?>
                                
                                <?php if(!empty($_GET['search'])): 
                                    // Для поиска показываем хлебные крошки
                                    $breadForSearch = Categories::adminBreadCrumbs($obj->id);
                                    if(!empty($breadForSearch)):
                                        $c = count($breadForSearch) - 1;
                                ?>
                                        <div class="comment breadcrumbs">
                                            <a href="/<?= ADMIN_LINK ?>/categories" class="path">Главная</a>
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
endif;
?>