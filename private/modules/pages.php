<?php

use app\Models\Pages;
use app\Models\Admins;
use app\Models\Gallery;
use app\Models\Files;
use app\FileUpload;
use app\Helpers;
use app\Form;
use app\Pagination;

$configPath = '/config/modules/pages.php';

// Проверяем существование конфигурационного файла
if (!file_exists(ROOT.$configPath)):
    ?>
    <div class="alert alert-danger">
        Внимание: отсутствует конфигурационный файл: <strong><?= $configPath ?></strong>
    </div>
<?php else: 

    $config = require_once ROOT.$configPath;

    // Проверяем, поддерживает ли таблица черновики
    $supportsDrafts = Pages::supportsDrafts();
    $useDrafts = $supportsDrafts && ($config['drafts']['enabled'] ?? false);

    if(!empty($_GET['parent'])) $parent = intval($_GET['parent']);
    else $parent = 0;

    if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

        $obj = new Pages();
        $original = $obj;
        
        // Устанавливаем значения по умолчанию
        $obj->show = 1; // Явно устанавливаем boolean значение
        $obj->menu = 0;
        $obj->menu_footer = 0;
        $obj->rate = 0;
        $obj->parent = $parent;
        $obj->edit_date = date('Y-m-d H:i:s', time());
        
        $title = 'Добавление';
        $id = false;
        
        if(!empty($_GET['edit'])) {
            $id = $_GET['edit'];
            $title = 'Редактирование';
            $action = 'edit';
        }
        if(!empty($_GET['copy']) && $config['actions']['copy']) {
            $id = $_GET['copy'];
            $title = 'Копирование';
            $action = 'copy';
        }
        
        if(!empty($id)) {
            $obj = Pages::findById($id);
            if (!$obj) {
                header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
                exit;
            }
            elseif($obj->is_draft == 0 && $useDrafts) {
                $original = Pages::where('WHERE original_id= ? ',[$obj->id],true);
                header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent . "&" . $action . "=" . $original->id);
                exit;
            }
 
            if($useDrafts) {
                $original = Pages::where('WHERE id= ? ',[$obj->original_id],true);
            }
        }
        
        if(!empty($_GET['copy']) && $config['actions']['copy']) {
            
            $obj->image = '';
            $obj->image2 = '';
            $obj->video = '';
            $obj->is_draft = 1;
            $obj->original_id = 0;

            $id = false;
        }

        if(!empty($obj->url)) $obj->url = str_replace('draft-','',$obj->url);

        // Убедимся, что $obj->show всегда boolean
        $obj->show = (bool)($obj->show ?? 1);
        $obj->menu = (bool)($obj->menu ?? 0);
        $obj->menu_footer = (bool)($obj->menu_footer ?? 0);

        // Проверяем наличие изменений для кнопки "Опубликовать"
        $has_changes = false;

        if (isset($_GET['edit'])) {
            // Для редактирования сравниваем с оригиналом
            if(empty($original)) {
                $has_changes = true;
            }
            else if ($original->edit_date != $obj->edit_date) {
                $has_changes = true;
            }
        } else {
            // Для добавления или копирования всегда есть изменения
            $has_changes = true;
        }

        if(!isset($_GET['edit'])) $has_changes = true;

        // Определяем ID родителя для отображения и хлебных крошек
        $display_parent_id = $parent;
        if ($useDrafts && !empty($obj->parent) && $obj->is_draft == 1) {
            // Для черновика находим чистовик родителя
            $draft_parent = Pages::findById($obj->parent);
            if ($draft_parent && $draft_parent->is_draft == 1 && !empty($draft_parent->original_id)) {
                // Родитель - тоже черновик, находим его чистовик
                $published_parent = Pages::findById($draft_parent->original_id);
                if ($published_parent) {
                    $display_parent_id = $published_parent->id;
                }
            } elseif ($draft_parent && $draft_parent->is_draft == 0) {
                // Родитель уже чистовик
                $display_parent_id = $draft_parent->id;
            }
        }
        
        // Получаем хлебные крошки на основе чистовиков
        $bread = Pages::adminBreadCrumbs($display_parent_id);
        $breadcrumbs = Pages::adminBread($bread, 1);
        
        ?>
        <div class="editHead">
            <h1>
                <?= $title ?> 
                <?php if(isset($_GET['edit'])): ?>
                    <?php if ($useDrafts && $has_changes): ?>
                        <span class="draft-badge">Есть неопубликованные изменения</span>
                    <?php elseif ($useDrafts && !$has_changes): ?>
                        <span class="published-badge">Все изменения опубликованы</span>
                    <?php endif; ?>
                <?php endif; ?>
            </h1>
            <div class="button_block">
                <a href='<?= $_SERVER['REDIRECT_URL'] ?>?parent=<?= $display_parent_id ?>' class='btn btn_white btn_back'>Вернуться назад</a>
                
                <?php if($config['actions']['open'] && !empty($_GET['edit']) && !empty($original)): ?>
                    <a href='<?= Pages::getUrl($original->id) ?>' class='btn btn_white' rel='external' target="_blank">Смотреть на сайте</a>
                <?php endif; ?>

                <?php if ($config['actions']['open'] && !empty($_GET['edit']) && $useDrafts): ?>
                    <a href='<?= Pages::getUrl($id) ?>' class='btn btn_white' rel='external' target="_blank">Предпросмотр черновика</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="edit_block">
            <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form' id='edit_form'
                  enctype='multipart/form-data'>
                
                <input type="hidden" name="display_parent" value="<?= $display_parent_id ?>">
                
                <?php if (isset($_GET['copy']) && $config['actions']['copy']): ?>
                    <!-- При копировании сохраняем ID оригинала как hidden поле -->
                    <input type="hidden" name="copy_from" value="<?= $_GET['copy'] ?>">
                <?php endif; ?>
                                
                <?php if ($useDrafts && $obj->original_id): ?>
                    <!-- Сохраняем ID связанного чистовика -->
                    <input type="hidden" name="original_id" value="<?= $obj->original_id ?>">
                <?php endif; ?>

                <!-- Поле для публикации -->
                <input type="hidden" name="publish" id="publish" value="0">

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
                        
                        <?php if ($config['gallery']['enabled']): ?>
                            <button type="button" class="edit_tab_nav" data-tab="gallery"><?= $config['gallery']['tab_name'] ?? 'Фотогалерея' ?></button>
                        <?php endif; ?>

                        <?php if ($config['files']['enabled']): ?>
                            <button type="button" class="edit_tab_nav" data-tab="files"><?= $config['files']['tab_name'] ?? 'Файлы' ?></button>
                        <?php endif; ?>

                        <?php if ($config['seo']['enabled']): ?>
                            <button type="button" class="edit_tab_nav" data-tab="seo"><?= $config['seo']['tab_name'] ?? 'SEO' ?></button>
                        <?php endif; ?>

                        <?php if (!empty($config['personal_page'][$id])): ?>
                            <button type="button" class="edit_tab_nav" data-tab="personal">Индивидуальный контент</button>
                        <?php endif; ?>

                    </div>
                    
                    <div class="edit_tabs_content">

                        <?php if (!empty($config['personal_page'][$id])): ?>

                            <div class="edit_tab_content" id="tab_personal">
                                <?php include ROOT.'/private/modules/pages/'.$config['personal_page'][$id].'.php'; ?>
                            </div>

                        <?php endif; ?>

                        <!-- Вкладка "Контент" -->
                        <?php if ($config['fields']['enabled']): ?>
                        <div class="edit_tab_content active" id="tab_content">

                            <div class="flex3">
                                <?php if ($config['fields']['show']['enabled'] ?? false): ?>
                                    <?= Form::checkbox('show', (bool)$obj->show, $config['fields']['show']['title'] ?? 'Показывать на сайте', 1, null) ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['menu']['enabled'] ?? false): ?>
                                    <?= Form::checkbox('menu', (bool)$obj->menu, $config['fields']['menu']['title'] ?? 'Показывать в меню', 1, null) ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['menu_footer']['enabled'] ?? false): ?>
                                    <?= Form::checkbox('menu_footer', (bool)$obj->menu_footer, $config['fields']['menu_footer']['title'] ?? 'Показывать в подвале', 1, null) ?>
                                <?php endif; ?>
                            </div>

                            <div class="flex2">
                                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['parent']['enabled'] ?? false): ?>
                                    <?php
                                    // Получаем список родительских страниц (только чистовики)
                                    // Исключаем текущую страницу и её черновики
                                    $excludeId = $id ?: 0;
                                    $excludeOriginalId = !empty($original) ? $original->id : 0;
                                    
                                    // Для выбора родителя показываем только чистовики
                                    $parentPages = Pages::where("WHERE is_draft = 0 AND id != ? AND id != ? ORDER BY name ASC", [$excludeId, $excludeOriginalId]);
                                    
                                    // Определяем текущее значение для select
                                    $currentParentId = 0;
                                    if (!empty($obj->parent)) {
                                        if ($obj->is_draft == 1) {
                                            // Для черновика находим чистовик родителя
                                            $draftParent = Pages::findById($obj->parent);
                                            if ($draftParent) {
                                                if ($draftParent->is_draft == 1 && !empty($draftParent->original_id)) {
                                                    $currentParentId = $draftParent->original_id;
                                                } elseif ($draftParent->is_draft == 0) {
                                                    $currentParentId = $draftParent->id;
                                                }
                                            }
                                        } else {
                                            // Для чистовика используем его родителя
                                            $currentParentId = $obj->parent;
                                        }
                                    }
                                    ?>
                                    <?= Form::select($config['fields']['parent']['title'] ?? 'Родительская страница', 'parent', $parentPages, $currentParentId, true, 'Корневой уровень', 'name', 0, '', 0, '') ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['name']['title'] ?? 'Название страницы (H1)', 'name', $obj->name, 1, '', '', '') ?>
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
                                    ($config['fields']['image']['width'] ?? 2760) . 'х' . 
                                    ($config['fields']['image']['height'] ?? 830) . ')', 
                                    'image', 
                                    $obj, 
                                    '', 
                                    0
                                ) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['image_text']['enabled'] ?? false): ?>
                                <?= Form::textarea(
                                    $config['fields']['image_text']['title'] ?? 'Текст на баннере', 
                                    'image_text', 
                                    $obj->image_text ?? '', 
                                    80,  
                                    ''
                                ) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['image2']['enabled'] ?? false): ?>
                                <?= Form::image(
                                    $config['fields']['image2']['title'] . ' (' . 
                                    ($config['fields']['image2']['width'] ?? 500) . 'х' . 
                                    ($config['fields']['image2']['height'] ?? 500) . ')', 
                                    'image2', 
                                    $obj, 
                                    '', 
                                    0
                                ) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['video']['enabled'] ?? false): ?>
                                <?= Form::file($config['fields']['video']['title'] ?? 'Видео файл', 'video', $obj,'') ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['text']['enabled'] ?? false): ?>
                                <?= Form::textbox($config['fields']['text']['title'] ?? 'Основной текст', 'text', $obj->text) ?>
                            <?php endif; ?>

                        </div>
                        <?php endif; ?>
                        
                        <!-- Вкладка "Фотогалерея" -->
                        <?php if ($config['gallery']['enabled']): ?>
                        <div class="edit_tab_content" id="tab_gallery">
                            
                            <!-- Заголовок и описание фотогалереи -->
                            <?php if ($config['gallery']['gallery_name']['enabled'] ?? false): ?>
                                <?= Form::input(
                                    $config['gallery']['gallery_name']['title'] ?? 'Заголовок фотогалереи', 
                                    'gallery_name', 
                                    $obj->gallery_name ?? '', 
                                    0, 
                                    '', 
                                    '', 
                                    ''
                                ) ?>
                            <?php endif; ?>
                            
                            <?php if ($config['gallery']['gallery_text']['enabled'] ?? false): ?>
                                <?= Form::textarea(
                                    $config['gallery']['gallery_text']['title'] ?? 'Описание фотогалереи', 
                                    'gallery_text', 
                                    $obj->gallery_text ?? '', 
                                    80, 
                                    ''
                                ) ?>
                            <?php endif; ?>
                            
                            <?= Form::gallery($config['gallery']['title'] ?? 'Фотогалерея', 'gallery', Gallery::findGallery('pages',$obj->id)) ?>
                        </div>
                        <?php endif; ?>

                        <!-- Вкладка "Файлы" -->
                        <?php if ($config['files']['enabled']): ?>
                        <div class="edit_tab_content" id="tab_files">
                            
                            <!-- Заголовок и описание файлов -->
                            <?php if ($config['files']['files_name']['enabled'] ?? false): ?>
                                <?= Form::input(
                                    $config['files']['files_name']['title'] ?? 'Заголовок файлов', 
                                    'files_name', 
                                    $obj->files_name ?? '', 
                                    0, 
                                    '', 
                                    '', 
                                    ''
                                ) ?>
                            <?php endif; ?>
                            
                            <?php if ($config['files']['files_text']['enabled'] ?? false): ?>
                                <?= Form::textarea(
                                    $config['files']['files_text']['title'] ?? 'Описание файлов', 
                                    'files_text', 
                                    $obj->files_text ?? '', 
                                    80, 
                                    ''
                                ) ?>
                            <?php endif; ?>
                            
                            <?= Form::files($config['files']['title'] ?? 'Файлы', 'files', Files::findFiles('pages',$obj->id)) ?>
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

                    <?php if ($useDrafts && Admins::canPublish()): ?>
                        <button type="button" name="publish" class="btn btn_green btn_publish active">
                            Опубликовать
                        </button>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    <?php
    elseif (isset($_POST['add']) || isset($_POST['edit'])) :

        $publish = $_POST['publish'] ?? 0;
        $id = $_POST['edit'] ?? 0;
        $display_parent = $_POST['display_parent'] ?? 0;
        
        // Инициализируем объект $obj в зависимости от ситуации
        if (isset($_POST['edit']) && $id > 0) {
            FileUpload::deleteImageFile();
            // Редактирование существующей записи
            $obj = Pages::findById($id);
            if (!$obj) {
                header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $display_parent);
                exit;
            }
        } else {
            // Добавление новой записи
            $obj = new Pages();
        }
        
        $_SESSION['notice'] = 'Добавлено';
        if(isset($_POST['edit'])) $_SESSION['notice'] = 'Изменено';
        if(isset($_POST['copy_from'])) $_SESSION['notice'] = 'Скопировано';
        if(!empty($_POST['publish']) && $useDrafts) $_SESSION['notice'] = 'Опубликовано';

        $url = trim($_POST['url'] ?? '');
        if (empty($url) && ($config['fields']['url']['enabled'] ?? false)) {
            $url = Helpers::str2url(trim($_POST['name'] ?? ''));
        }
        
        // Определяем родительскую страницу
        $parentId = 0;
        if ($config['fields']['parent']['enabled'] ?? false) {
            $selectedParentId = (int)($_POST['parent'] ?? 0);
            
            if ($selectedParentId > 0) {
                if ($useDrafts) {
                    // Находим черновик для выбранного чистовика
                    $publishedParent = Pages::findById($selectedParentId);
                    if ($publishedParent && $publishedParent->is_draft == 0) {
                        // Ищем черновик для этого чистовика
                        $draftParent = Pages::where('WHERE original_id = ? AND is_draft = 1', [$publishedParent->id], true);
                        if ($draftParent) {
                            $parentId = $draftParent->id;
                        } else {
                            // Если черновика нет, используем чистовик
                            $parentId = $publishedParent->id;
                        }
                    } else {
                        $parentId = $selectedParentId;
                    }
                } else {
                    $parentId = $selectedParentId;
                }
            }
        }

        if ($url <> '' && ($config['fields']['url']['enabled'] ?? false)) {
            $i = 1;
            $u = $url;
            
            // Ищем совпадения среди черновиков
            $existingId = $obj->id ?? 0;
            $pg = Pages::findWhere("WHERE parent='" . $parentId . "' AND url='" . $url . "' AND id<>'" . $existingId . "' AND is_draft=1 LIMIT 1");
            while ($pg) {
                $url = $u . '-' . $i;
                $pg = Pages::findWhere("WHERE parent='" . $parentId . "' AND url='" . $url . "' AND id<>'" . $existingId . "' AND is_draft=1 LIMIT 1");
                $i++;
            }
        }

        $url = str_replace('draft-','',$url);
        if($id == 1) $url = '';
        $url = 'draft-'.$url;

        // Заполняем данные из формы В ЧЕРНОВИК
        $obj->url = ($config['fields']['url']['enabled'] ?? false) ? $url : '';
        $obj->name = ($config['fields']['name']['enabled'] ?? false) ? trim($_POST['name'] ?? '') : '';
        $obj->image_text = ($config['fields']['image_text']['enabled'] ?? false) ? trim($_POST['image_text'] ?? '') : '';
        
        // Для name_menu: если поле включено и есть значение - берем его, иначе берем name
        if ($config['fields']['name_menu']['enabled'] ?? false) {
            $name_menu_input = trim($_POST['name_menu'] ?? '');
            $obj->name_menu = !empty($name_menu_input) ? $name_menu_input : $obj->name;
        } else {
            $obj->name_menu = $obj->name; // Если поле отключено, используем name
        }
        
        $obj->parent = $parentId;
        $obj->text = ($config['fields']['text']['enabled'] ?? false) ? trim($_POST['text'] ?? '') : '';
        $obj->show = ($config['fields']['show']['enabled'] ?? false) ? (int)($_POST['show'] ?? 0) : 1;
        $obj->menu = ($config['fields']['menu']['enabled'] ?? false) ? (int)($_POST['menu'] ?? 0) : 0;
        $obj->menu_footer = ($config['fields']['menu_footer']['enabled'] ?? false) ? (int)($_POST['menu_footer'] ?? 0) : 0;
        $obj->rate = ($config['fields']['rate']['enabled'] ?? false) ? (int)($_POST['rate'] ?? 0) : 0;
        
        // Поля галереи и файлов
        if ($config['gallery']['enabled']) {
            $gallery_name = $_POST['gallery_name'] ?? '';
            $gallery_text = $_POST['gallery_text'] ?? '';
            
            $obj->gallery_name = ($config['gallery']['gallery_name']['enabled'] ?? false) ? (is_array($gallery_name) ? '' : trim($gallery_name)) : '';
            $obj->gallery_text = ($config['gallery']['gallery_text']['enabled'] ?? false) ? (is_array($gallery_text) ? '' : trim($gallery_text)) : '';
        }
        
        if ($config['files']['enabled']) {
            $files_name = $_POST['files_name'] ?? '';
            $files_text = $_POST['files_text'] ?? '';
            
            $obj->files_name = ($config['files']['files_name']['enabled'] ?? false) ? (is_array($files_name) ? '' : trim($files_name)) : '';
            $obj->files_text = ($config['files']['files_text']['enabled'] ?? false) ? (is_array($files_text) ? '' : trim($files_text)) : '';
        }
        
        // SEO поля
        if ($config['seo']['enabled']) {
            $obj->title = ($config['seo']['title']['enabled'] ?? false) ? trim($_POST['title'] ?? '') : '';
            $obj->keywords = ($config['seo']['keywords']['enabled'] ?? false) ? trim($_POST['keywords'] ?? '') : '';
            $obj->description = ($config['seo']['description']['enabled'] ?? false) ? trim($_POST['description'] ?? '') : '';
        }
        
        // Системные поля
        $obj->edit_date = date("Y-m-d H:i:s");
        $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
        $obj->is_draft = 1; // Признак черновика

        // Проверяем, не пытаемся ли сделать страницу родителем самой себе
        if (!empty($obj->parent) && $obj->parent == $obj->id) {
            $_SESSION['error'] = 'Страница не может быть родителем самой себе';
            header("Location: {$_SERVER['REQUEST_URI']}?parent=" . $display_parent . "&edit=" . $id);
            exit;
        }

        // Сохраняем запись
        $obj->save();

        // Загрузка изображений в ЧЕРНОВИК
        if (($config['fields']['image']['enabled'] ?? false)) {
            $width = $config['fields']['image']['width'] ?? 2760;
            $height = $config['fields']['image']['height'] ?? 830;
            
            FileUpload::uploadImage(
                'image', 
                get_class($obj), 
                'image', 
                $obj->id, 
                $width, 
                $height, 
                '/public/src/images/pages/', 
                0
            );
        }

        if (($config['fields']['image2']['enabled'] ?? false)) {
            $width = $config['fields']['image2']['width'] ?? 500;
            $height = $config['fields']['image2']['height'] ?? 500;
            
            FileUpload::uploadImage(
                'image2', 
                get_class($obj), 
                'image2', 
                $obj->id, 
                $width, 
                $height, 
                '/public/src/images/pages/', 
                0
            );
        }
        
        // Загрузка видео файла в ЧЕРНОВИК
        if (($config['fields']['video']['enabled'] ?? false)) {
            FileUpload::uploadFile(
                'video', 
                get_class($obj), 
                'video', 
                $obj->id, 
                '/public/src/video/'
            );
        }

        // --- Фотогалерея --- //
        FileUpload::updateGallery();
        FileUpload::uploadGallery('gallery', 'pages', $obj->id, 800, 600, '/public/src/images/pages/', 400, 300, 1);
        // --- // --- //

        // --- Файлы --- //
        FileUpload::updateFiles();
        FileUpload::uploadFiles('files', 'pages', $obj->id, '/public/src/files/pages/');
        // --- // --- //

        if ($publish || !$useDrafts) {

            $obj = Pages::findById($obj->id);

            if($obj->original_id) {
                $published = Pages::findById($obj->original_id);
            } else {
                // Создаем пустой чистовик для нового черновика
                $published = new Pages();
            }
            
            // Копируем данные из черновика в опубликованную версию
            $published = Pages::copyData($obj, $published, ['id', 'is_draft', 'original_id','image','image2','video']);

            // Определяем родителя для чистовика
            $publishedParentId = 0;
            if (!empty($obj->parent)) {
                $draftParent = Pages::findById($obj->parent);
                if ($draftParent) {
                    if ($draftParent->is_draft == 1 && !empty($draftParent->original_id)) {
                        // Родитель черновика - используем его чистовик
                        $publishedParentId = $draftParent->original_id;
                    } elseif ($draftParent->is_draft == 0) {
                        // Родитель уже чистовик
                        $publishedParentId = $draftParent->id;
                    }
                }
            }
            
            $published->parent = $publishedParentId;
            $published->url = str_replace('draft-','',$published->url);
            $published->is_draft = 0;
            $published->original_id = 0;
            $published->edit_date = date("Y-m-d H:i:s");
            $published->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

            // Удаляем опубликованные изображения, если они существуют
            if(!empty($published->image) && file_exists(ROOT . $published->image)) {
                unlink(ROOT . $published->image);
                $published->image = '';
            }

            if(!empty($published->image2) && file_exists(ROOT . $published->image2)) {
                unlink(ROOT . $published->image2);
                $published->image2 = '';
            }
            
            // Удаляем опубликованное видео, если оно существует
            if(!empty($published->video) && file_exists(ROOT . $published->video)) {
                unlink(ROOT . $published->video);
                $published->video = '';
            }
            
            // Копируем новые изображения
            if(!empty($obj->image) && file_exists(ROOT . $obj->image)) {
                $extension = pathinfo(ROOT.$obj->image, PATHINFO_EXTENSION);
                $image = '/public/src/images/pages/'.uniqid().'.'.$extension;
                copy(ROOT.$obj->image,ROOT.$image);
                $published->image = $image;
            }

            if(!empty($obj->image2) && file_exists(ROOT . $obj->image2)) {
                $extension = pathinfo(ROOT.$obj->image2, PATHINFO_EXTENSION);
                $image2 = '/public/src/images/pages/'.uniqid().'.'.$extension;
                copy(ROOT.$obj->image2,ROOT.$image2);
                $published->image2 = $image2;
            }

            // Копируем видео файл
            if(!empty($obj->video) && file_exists(ROOT . $obj->video)) {
                $extension = pathinfo(ROOT.$obj->video, PATHINFO_EXTENSION);
                $video = '/public/src/video/'.uniqid().'.'.$extension;
                copy(ROOT.$obj->video,ROOT.$video);
                $published->video = $video;
            }

            $published->save();

            // Копируем галерею
            $draftGallery = Gallery::where("WHERE type = 'pages' AND ids = ?", [$obj->id]);
            $publishedGallery = Gallery::where("WHERE type = 'pages' AND ids = ?", [$published->id]);

            // Удаляем старую галерею опубликованной версии
            foreach ($publishedGallery as $item) {
                if (!empty($item->image) && file_exists(ROOT . $item->image)) {
                    unlink(ROOT . $item->image);
                }
                if (!empty($item->image_small) && file_exists(ROOT . $item->image_small)) {
                    unlink(ROOT . $item->image_small);
                }
                if (!empty($item->image_origin) && file_exists(ROOT . $item->image_origin)) {
                    unlink(ROOT . $item->image_origin);
                }
                $item->delete();
            }

            // Копируем галерею из черновика в опубликованную версию
            foreach ($draftGallery as $item) {
                $newGallery = new Gallery();
                $newGallery->type = 'pages';
                $newGallery->ids = $published->id;
                $newGallery->alt = $item->alt;
                $newGallery->rate = $item->rate;
                $newGallery->show = $item->show;
                $newGallery->edit_date = date("Y-m-d H:i:s");
                $newGallery->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
                
                // Копируем файлы
                if (!empty($item->image) && file_exists(ROOT . $item->image)) {
                    $extension = pathinfo(ROOT . $item->image, PATHINFO_EXTENSION);
                    $newImage = '/public/src/images/pages/gallery_' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->image, ROOT . $newImage);
                    $newGallery->image = $newImage;
                }
                
                if (!empty($item->image_small) && file_exists(ROOT . $item->image_small)) {
                    $extension = pathinfo(ROOT . $item->image_small, PATHINFO_EXTENSION);
                    $newImageSmall = '/public/src/images/pages/gallery_small_' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->image_small, ROOT . $newImageSmall);
                    $newGallery->image_small = $newImageSmall;
                }
                
                if (!empty($item->image_origin) && file_exists(ROOT . $item->image_origin)) {
                    $extension = pathinfo(ROOT . $item->image_origin, PATHINFO_EXTENSION);
                    $newImageOrigin = '/public/src/images/pages/gallery_origin_' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->image_origin, ROOT . $newImageOrigin);
                    $newGallery->image_origin = $newImageOrigin;
                }
                
                $newGallery->save();
            }

            // Копируем файлы
            $draftFiles = Files::where("WHERE type = 'pages' AND ids = ?", [$obj->id]);
            $publishedFiles = Files::where("WHERE type = 'pages' AND ids = ?", [$published->id]);

            // Удаляем старые файлы опубликованной версии
            foreach ($publishedFiles as $item) {
                if (!empty($item->file) && file_exists(ROOT . $item->file)) {
                    unlink(ROOT . $item->file);
                }
                $item->delete();
            }

            // Копируем файлы из черновика в опубликованную версию
            foreach ($draftFiles as $item) {
                $newFile = new Files();
                $newFile->type = 'pages';
                $newFile->ids = $published->id;
                $newFile->filename = $item->filename;
                $newFile->extension = $item->extension;
                $newFile->rate = $item->rate;
                $newFile->show = $item->show;
                $newFile->edit_date = date("Y-m-d H:i:s");
                $newFile->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
                
                // Копируем файл
                if (!empty($item->file) && file_exists(ROOT . $item->file)) {
                    $extension = pathinfo(ROOT . $item->file, PATHINFO_EXTENSION);
                    $newFilePath = '/public/src/files/pages/' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->file, ROOT . $newFilePath);
                    $newFile->file = $newFilePath;
                }
                
                $newFile->save();
            }

            // Обновляем связь черновика
            $obj->original_id = $published->id;
            $obj->save();
        }

        if (!empty($config['personal_page'][$obj->id])) {
            include ROOT.'/private/modules/pages/'.$config['personal_page'][$obj->id].'.php';
        }

        header("Location: {$_SERVER['REQUEST_URI']}?parent=" . $display_parent . "&edit=$obj->id");
        exit;
        
    elseif (isset($_GET['delete'])) :

        $id = $_GET['delete'];
        $obj = Pages::findById($id);
        
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $parent);
            exit;
        }
        
        // Определяем родителя для редиректа
        $redirectParent = $parent;
        if ($obj->is_draft == 1 && !empty($obj->parent)) {
            // Находим чистовик родителя для редиректа
            $draftParent = Pages::findById($obj->parent);
            if ($draftParent) {
                if ($draftParent->is_draft == 1 && !empty($draftParent->original_id)) {
                    $redirectParent = $draftParent->original_id;
                } elseif ($draftParent->is_draft == 0) {
                    $redirectParent = $draftParent->id;
                }
            }
        }
        
        // Проверяем, есть ли дочерние страницы (учитывая черновики)
        $childPages = Pages::findWhere('WHERE parent="'.$obj->id.'"');
        if (!empty($childPages)) {
            $_SESSION['error'] = 'Нельзя удалить страницу, у которой есть дочерние страницы';
            header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $redirectParent);
            exit;
        }
        
        $published = null;
        if ($obj->original_id) {
            $published = Pages::findById($obj->original_id);
        }
        
        // Удаление изображений, если они есть
        if (($config['fields']['image']['enabled'] ?? false) && !empty($obj->image) && file_exists(ROOT . $obj->image)) {
            unlink(ROOT . $obj->image);
        }

        if (($config['fields']['image2']['enabled'] ?? false) && !empty($obj->image2) && file_exists(ROOT . $obj->image2)) {
            unlink(ROOT . $obj->image2);
        }
        
        // Удаление видео файла, если он есть
        if (($config['fields']['video']['enabled'] ?? false) && !empty($obj->video) && file_exists(ROOT . $obj->video)) {
            unlink(ROOT . $obj->video);
        }

        // Удаляем галерею черновика
        Gallery::delAll('pages', $obj->id);

        // Удаляем файлы черновика
        Files::delAll('pages', $obj->id);
        
        $obj->delete();

        // Если есть опубликованная версия и черновик был последним, удаляем и её
        if ($published && $useDrafts) {
            // Проверяем есть ли другие черновики для этой страницы
            $otherDrafts = Pages::where("WHERE original_id = ? AND id != ?", [$published->id, $obj->id]);
            
            if (empty($otherDrafts)) {
                // Удаление изображений опубликованной версии
                if (($config['fields']['image']['enabled'] ?? false) && !empty($published->image) && file_exists(ROOT . $published->image)) {
                    unlink(ROOT . $published->image);
                }
                
                // Удаление видео файла опубликованной версии
                if (($config['fields']['video']['enabled'] ?? false) && !empty($published->video) && file_exists(ROOT . $published->video)) {
                    unlink(ROOT . $published->video);
                }

                // Удаляем галерею опубликованной версии
                Gallery::delAll('pages', $published->id);

                // Удаляем файлы опубликованной версии
                Files::delAll('pages', $published->id);
                
                $published->delete();
            }
        }
        
        $_SESSION['notice'] = 'Удалено';

        header("Location: {$_SERVER['REDIRECT_URL']}?parent=" . $redirectParent);
        exit;

    else :
        // Заголовок модуля из конфига
        $title = $config['module']['title'] ?? '';

        $filter = false;

        // Определяем, какой parent использовать для отображения
        $display_parent_id = $parent;
        if ($useDrafts && $parent > 0) {
            // Находим черновик для этого чистовика
            $publishedParent = Pages::findById($parent);
            if ($publishedParent && $publishedParent->is_draft == 0) {
                $draftParent = Pages::where('WHERE original_id = ? AND is_draft = 1', [$publishedParent->id], true);
                if ($draftParent) {
                    $display_parent_id = $draftParent->id;
                }
            }
        }

        // Формируем условия WHERE
        $whereConditions = [];
        $params = [];
        
        $search = trim($_GET['search'] ?? '');
        if (!empty($search) && $config['filters']['search']) {
            $whereConditions[] = "(`name` like '%{$search}%' OR `name_menu` like '%{$search}%' OR `text` like '%{$search}%')";
            $filter = true;
            // При поиске игнорируем parent
            $display_parent_id = 0;
        } else {
            if(!empty($display_parent_id)) {
                $whereConditions[] = "parent = " . intval($display_parent_id);
            } else {
                $whereConditions[] = "(parent = 0 OR parent IS NULL)";
            }
        }
        
        // Фильтр по статусу черновика
        if ($useDrafts) {
            $whereConditions[] = "is_draft = 1";
        }

        // Формируем полное WHERE условие
        $where = '';
        if (!empty($whereConditions)) {
            $where = 'WHERE ' . implode(' AND ', $whereConditions);
        }

        $perPage = $_SESSION['pages']['per_page'] ?? $config['pagination']['default_per_page'];
        $order_by = $config['pagination']['order_by'] ?? 'ORDER BY rate DESC, id DESC';

        // Сохраняем дополнительные параметры для пагинации
        $additionalParams = ['parent' => $parent]; // Используем оригинальный parent для ссылок

        // Вызов пагинации
        $result = Pagination::create(
            modelClass: Pages::class,
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
            $page = Pages::findById($parent);
            if ($page) {
                $bread = Pages::adminBreadCrumbs($parent);
                $breadcrumbs = Pages::adminBread($bread, 0);
                
                $title = 'Страница: ' . $page->name;

                if(!empty($page->parent)) $queryString = '?parent='.$page->parent;
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
                        <div class="pole info"><?= $config['list']['info']['title'] ?? 'Страница' ?></div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="pole modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                    <?php endif; ?>
                    
                    <?php if ($useDrafts && ($config['list']['published_date']['enabled'] ?? false)): ?>
                        <div class="pole modified_date"><?= $config['list']['published_date']['title'] ?? 'Публикация' ?></div>
                    <?php endif; ?>
                                        
                    <div class="pole actions"></div>
                </div>
                <div class="table_body<?= ($totalCount <= $perPage && empty($_GET['search']) && $config['list']['handler']) ? ' sortbox-items' : '' ?>">
                <?php foreach ($objs as $obj):
                    // Находим чистовик для этого черновика
                    $original = null;
                    $publishedId = null;
                    if ($useDrafts && $obj->is_draft == 1) {
                        if (!empty($obj->original_id)) {
                            $original = Pages::findById($obj->original_id);
                            $publishedId = $original->id;
                            $pageUrl = Pages::getUrl($original->id);
                        }
                    } else {
                        $original = $obj;
                        $publishedId = $obj->id;
                    }
                    
                    // Проверяем наличие изменений в черновике
                    $has_changes = false;
                    if ($useDrafts && !empty($original) && $obj->edit_date != $original->edit_date || empty($original)) {
                        $has_changes = true;
                    }
                    
                    // Получаем количество дочерних страниц (ищем черновики)
                    $childCount = 0;
                    if ($useDrafts) {
                        // Ищем черновики, которые ссылаются на чистовик
                        if (!empty($publishedId)) {
                            $childCount = Pages::count("WHERE parent = " . $publishedId . " AND is_draft = 1");
                        }
                    } else {
                        $childCount = Pages::count("WHERE parent = " . $obj->id);
                    }
                    
                    // Определяем ссылку для перехода к дочерним страницам
                    $childLinkId = $useDrafts ? $publishedId : $obj->id;
                ?>

                    <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                        <?php if ($config['list']['handler']): ?>
                            <div class="pole handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($_GET['search'])) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                        <?php endif; ?>
                        
                        <?php if (($config['list']['info']['enabled'] ?? false)): ?>
                            <div class="pole info">
                                <div class="title"><?= $config['list']['info']['title'] ?? 'Страница' ?></div>
                                <div class="name">
                                    <?php if(empty($_GET['search']) && empty($filter) && !empty($childLinkId)): ?>
                                        <a href="?parent=<?= $childLinkId ?>" class="pageLink"><?= $obj->name_menu ?></a>
                                    <?php else: ?>
                                        <?= $obj->name_menu ?>
                                    <?php endif; ?>
                                </div>
                                <?php if($childCount > 0 && empty($_GET['search'])): ?>
                                    <div class="comment">Подстраниц: <?= $childCount ?></div>
                                <?php endif; ?>
                                
                                <?php if(!empty($_GET['search'])): 
                                    // Для поиска показываем хлебные крошки на основе чистовиков
                                    $breadForSearch = [];
                                    if (!empty($publishedId)) {
                                        $breadForSearch = Pages::adminBreadCrumbs($publishedId);
                                    }
                                    if(!empty($breadForSearch)):
                                        $c = count($breadForSearch) - 1;
                                ?>
                                        <div class="comment breadcrumbs">
                                            <a href="/<?= ADMIN_LINK ?>/pages" class="path">Главная</a>
                                            <?php foreach($breadForSearch AS $key=>$item): ?>
                                                <?php if($key < $c): ?>
                                                    <span class="path_arr">/</span><a href="<?= $item['url'] ?>" class="path"><?= $item['name'] ?></a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($useDrafts && $has_changes): ?>
                                    <div class="comment alarm">Есть неопубликованные изменения</div>
                                <?php elseif ($useDrafts && !empty($original)): ?>
                                    <div class="comment success">Опубликовано</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                            <div class="pole modified_date">
                                <div class="title"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                                <?= $obj->edit_date ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($useDrafts && ($config['list']['published_date']['enabled'] ?? false)): ?>
                            <div class="pole modified_date">
                                <div class="title"><?= $config['list']['published_date']['title'] ?? 'Публикация' ?></div>
                                <?= $original->edit_date ?? '-' ?>
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