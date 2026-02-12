<?php

use app\Models\Articles;
use app\Models\ArticlesSections;
use app\Models\Admins;
use app\Models\Gallery;
use app\Models\Files;
use app\FileUpload;
use app\Helpers;
use app\Pagination;
use app\Form;

$configPath = '/config/modules/articles.php';

// Проверяем существование конфигурационного файла
if (!file_exists(ROOT.$configPath)):
    ?>
    <div class="alert alert-danger">
        Внимание: отсутствует конфигурационный файл: <strong><?= $configPath ?></strong>
    </div>
<?php else: 

    $config = require_once ROOT.$configPath;

    // Проверяем, поддерживает ли таблица черновики
    $supportsDrafts = Articles::supportsDrafts();
    $useDrafts = $supportsDrafts && ($config['drafts']['enabled'] ?? false);

    if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

        $obj = new Articles();
        $original = $obj;
        
        // Устанавливаем значения по умолчанию
        $obj->show = 1; // Явно устанавливаем boolean значение
        $obj->date = date('Y-m-d H:i:s', time());
        $obj->section_id = 0;
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
            $obj = Articles::findById($id);
            if (!$obj) {
                header("Location: {$_SERVER['REDIRECT_URL']}");
                exit;
            }
            elseif($obj->is_draft == 0 && $useDrafts) {
                $original = Articles::where('WHERE original_id= ? ',[$obj->id],true);
                header("Location: {$_SERVER['REDIRECT_URL']}?".$action."=".$original->id);
                exit;
            }
 
            if($useDrafts) {
                $original = Articles::where('WHERE id= ? ',[$obj->original_id],true);
            }
        }
        
        if(!empty($_GET['copy']) && $config['actions']['copy']) {
            
            $obj->image = '';
            $obj->image_preview = '';
            $obj->is_draft = 1;
            $obj->original_id = 0;

            $id = false;
        }

        if(!empty($obj->url)) $obj->url = str_replace('draft-','',$obj->url);

        // Убедимся, что $obj->show всегда boolean
        $obj->show = (bool)($obj->show ?? 1);

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
                <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться назад</a>
                
                <?php if($config['actions']['open'] && !empty($_GET['edit']) && !empty($original)): ?>
                    <a href='<?= Articles::getUrl($original->id) ?>' class='btn btn_white' rel='external' target="_blank">Смотреть на сайте</a>
                <?php endif; ?>

                <?php if ($config['actions']['open'] && !empty($_GET['edit']) && $useDrafts): ?>

                    <a href='<?= Articles::getUrl($id) ?>' class='btn btn_white' rel='external' target="_blank">Предпросмотр черновика</a>
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
                                
                <?php if ($useDrafts && $obj->original_id): ?>
                    <!-- Сохраняем ID связанного чистовика -->
                    <input type="hidden" name="original_id" value="<?= $obj->original_id ?>">
                <?php endif; ?>

                <!-- Поле для публикации -->
                <input type="hidden" name="publish" id="publish" value="0">

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
                    </div>
                    
                    <div class="edit_tabs_content">
                        <!-- Вкладка "Контент" -->
                        <?php if ($config['fields']['enabled']): ?>
                        <div class="edit_tab_content active" id="tab_content">

                            <div class="flex3">
                                <?php if ($config['fields']['show']['enabled'] ?? false): ?>
                                    <?= Form::checkbox('show', (bool)$obj->show, $config['fields']['show']['title'] ?? 'Показывать на сайте', 1, null) ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['date']['enabled'] ?? false): ?>
                                    <?= Form::input($config['fields']['date']['title'] ?? 'Дата', 'date', date('Y-m-d',strtotime($obj->date)), 0, 'date', '', '') ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($config['fields']['section_id']['enabled'] ?? false): ?>
                                <?= Form::select($config['fields']['section_id']['title'] ?? 'Категория', 'section_id', ArticlesSections::where('ORDER BY name ASC'), $obj->section_id, true, 'Не выбрано', 'name', 0, '', 0, '') ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['name']['title'] ?? 'Название', 'name', $obj->name, 1, '', '', '') ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['url']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['url']['title'] ?? 'Ссылка', 'url', $obj->url, 0, '', '', '') ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['preview']['enabled']): ?>
                            <fieldset class="input_block flex2 top">
                                <legend><?= $config['fields']['preview']['block_name'] ?? 'Превью' ?></legend>
                                
                                <?php if ($config['fields']['preview']['image_preview']['enabled'] ?? false): ?>
                                    <?= Form::image(
                                        $config['fields']['preview']['image_preview']['title'] . ' (' . 
                                        ($config['fields']['preview']['image_preview']['width'] ?? 300) . 'х' . 
                                        ($config['fields']['preview']['image_preview']['height'] ?? 300) . ')', 
                                        'image_preview', 
                                        $obj, 
                                        '', 
                                        0
                                    ) ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['preview']['textshort']['enabled'] ?? false): ?>
                                    <?= Form::textarea($config['fields']['preview']['textshort']['title'] ?? 'Краткое описание', 'textshort', $obj->textshort, 150, '') ?>
                                <?php endif; ?>
                            </fieldset>
                            <?php endif; ?>

                            <?php if ($config['fields']['image']['enabled'] ?? false): ?>
                                <?= Form::image(
                                    $config['fields']['image']['title'] . ' (' . 
                                    ($config['fields']['image']['width'] ?? 1820) . 'х' . 
                                    ($config['fields']['image']['height'] ?? 1040) . ')', 
                                    'image', 
                                    $obj, 
                                    '', 
                                    0
                                ) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['text']['enabled'] ?? false): ?>
                                <?= Form::textbox($config['fields']['text']['title'] ?? 'Текст', 'text', $obj->text) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['text2']['enabled'] ?? false): ?>
                                <?= Form::textbox($config['fields']['text2']['title'] ?? 'Дополнительный текст', 'text2', $obj->text2 ?? '') ?>
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

                            <?= Form::gallery($config['gallery']['title'] ?? 'Фотогалерея', 'gallery', Gallery::findGallery('articles',$obj->id)) ?>
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

                            <?= Form::files($config['files']['title'] ?? 'Файлы', 'files', Files::findFiles('articles',$obj->id)) ?>
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
                                <small>Description: рекомендуется до 160 символов</small>
                            <?php endif; ?>
                            
                            <?php if ($config['seo']['description']['enabled'] ?? false): ?>
                                <?= Form::textarea($config['seo']['description']['title'] ?? 'Description (описание)', 'description', $obj->description ?? '', 140, '') ?>
                                <small>Keywords: через запятую, до 1024 символов</small>
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
        
        // Инициализируем объект $obj в зависимости от ситуации
        if (isset($_POST['edit']) && $id > 0) {
            // Редактирование существующей записи
            FileUpload::deleteImageFile();
            $obj = Articles::findById($id);
            if (!$obj) {
                header("Location: {$_SERVER['REDIRECT_URL']}");
                exit;
            }
        } else {
            // Добавление новой записи
            $obj = new Articles();
        }
        
        $_SESSION['notice'] = 'Добавлено';
        if(isset($_POST['edit'])) $_SESSION['notice'] = 'Изменено';
        if(isset($_POST['copy_from'])) $_SESSION['notice'] = 'Скопировано';
        if(!empty($_POST['publish']) && $useDrafts) $_SESSION['notice'] = 'Опубликовано';

        $date = date("Y-m-d H:i:s");
        if (!empty($_POST['date']) && ($config['fields']['date']['enabled'] ?? false)) {
            $date = $_POST['date'];
        }

        $url = trim($_POST['url'] ?? '');
        if (empty($url) && ($config['fields']['url']['enabled'] ?? false)) {
            $url = date('d-m-Y', strtotime($date)) . '-' . Helpers::str2url(trim($_POST['name'] ?? ''));
        }
        if ($url <> '' && ($config['fields']['url']['enabled'] ?? false)) {
            $i = 1;
            $u = $url;
            // Исправляем проверку уникальности URL
            $existingId = $obj->id ?? 0;
            $pg = Articles::findWhere("WHERE url='" . $url . "' AND id<>'" . $existingId . "' AND original_id<>0 LIMIT 1");
            while ($pg) {
                $url = $u . '-' . $i;
                $pg = Articles::findWhere("WHERE url='" . $url . "' AND id<>'" . $existingId . "' AND original_id<>0 LIMIT 1");
                $i++;
            }
        }

        $url = str_replace('draft-','',$url);
        $url = 'draft-'.$url;

        // Сохраняем старые данные для сравнения (только для черновиков)
        $oldData = null;
        if ($useDrafts && isset($_POST['edit']) && $id > 0) {
            $oldData = Articles::findById($id)->toArray();
        }

        // Заполняем данные из формы В ЧЕРНОВИК
        $obj->url = ($config['fields']['url']['enabled'] ?? false) ? $url : '';
        $obj->name = ($config['fields']['name']['enabled'] ?? false) ? trim($_POST['name'] ?? '') : '';
        $obj->text = ($config['fields']['text']['enabled'] ?? false) ? trim($_POST['text'] ?? '') : '';
        $obj->text2 = ($config['fields']['text2']['enabled'] ?? false) ? trim($_POST['text2'] ?? '') : '';
        $obj->textshort = ($config['fields']['preview']['textshort']['enabled'] ?? false) ? trim($_POST['textshort'] ?? '') : '';
        $obj->date = $date;
        $obj->show = ($config['fields']['show']['enabled'] ?? false) ? (int)($_POST['show'] ?? 0) : 1;
        $obj->section_id = ($config['fields']['section_id']['enabled'] ?? false) ? (int)($_POST['section_id'] ?? 0) : 0;
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

        // Сохраняем текущие данные
        $currentData = $obj->toArray();

        // Получаем ID администратора
        $adminId = $_SESSION['admin']['id'] ?? 0;

        // Сохраняем запись
        $obj->save();

        // Логируем изменения ТОЛЬКО для черновиков
        if ($useDrafts && $config['history']['enabled']) {
            // Определяем поля для проверки
            $fieldsToCheck = [];
            if ($config['fields']['name']['enabled'] ?? false) $fieldsToCheck[] = 'name';
            if ($config['fields']['url']['enabled'] ?? false) $fieldsToCheck[] = 'url';
            if ($config['fields']['text']['enabled'] ?? false) $fieldsToCheck[] = 'text';
            if ($config['fields']['text2']['enabled'] ?? false) $fieldsToCheck[] = 'text2';
            if ($config['fields']['preview']['textshort']['enabled'] ?? false) $fieldsToCheck[] = 'textshort';
            if ($config['fields']['show']['enabled'] ?? false) $fieldsToCheck[] = 'show';
            if ($config['fields']['date']['enabled'] ?? false) $fieldsToCheck[] = 'date';
            if ($config['fields']['section_id']['enabled'] ?? false) $fieldsToCheck[] = 'section_id';
            if ($config['fields']['rate']['enabled'] ?? false) $fieldsToCheck[] = 'rate';
            if ($config['seo']['title']['enabled'] ?? false) $fieldsToCheck[] = 'title';
            if ($config['seo']['keywords']['enabled'] ?? false) $fieldsToCheck[] = 'keywords';
            if ($config['seo']['description']['enabled'] ?? false) $fieldsToCheck[] = 'description';
        }

        // Загрузка изображений в ЧЕРНОВИК
        if (($config['fields']['image']['enabled'] ?? false)) {
            $width = $config['fields']['image']['width'] ?? 1820;
            $height = $config['fields']['image']['height'] ?? 1040;
            
            FileUpload::uploadImage(
                'image', 
                get_class($obj), 
                'image', 
                $obj->id, 
                $width, 
                $height, 
                '/public/src/images/articles/', 
                0
            );
        }
        
        if (($config['fields']['preview']['image_preview']['enabled'] ?? false)) {
            $width = $config['fields']['preview']['image_preview']['width'] ?? 300;
            $height = $config['fields']['preview']['image_preview']['height'] ?? 300;
            
            FileUpload::uploadImage(
                'image_preview', 
                get_class($obj), 
                'image_preview', 
                $obj->id, 
                $width, 
                $height, 
                '/public/src/images/articles/', 
                0
            );
        }

        // --- Фотогалерея --- //
        FileUpload::updateGallery();
        FileUpload::uploadGallery('gallery', 'articles', $obj->id, 800, 600, '/public/src/images/articles/', 400, 300, 1);
        // --- // --- //

        // --- Файлы --- //
        FileUpload::updateFiles();
        FileUpload::uploadFiles('files', 'articles', $obj->id, '/public/src/files/articles/');
        // --- // --- //


        if ($publish || !$useDrafts) {

            $obj = Articles::findById($obj->id);

            if($obj->original_id) {
                $published = Articles::findById($obj->original_id);
            } else {
                // Создаем пустой чистовик для нового черновика
                $published = new Articles();
            }
            
            // Копируем данные из черновика в опубликованную версию
            $published = Articles::copyData($obj, $published, ['id', 'is_draft', 'original_id','image','image_preview']);

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
            if(!empty($published->image_preview) && file_exists(ROOT . $published->image_preview)) {
                unlink(ROOT . $published->image_preview);
                $published->image_preview = '';
            }
            // Копируем новые изображения
            if(!empty($obj->image) && file_exists(ROOT . $obj->image)) {
                $extension = pathinfo(ROOT.$obj->image, PATHINFO_EXTENSION);
                $image = '/public/src/images/articles/'.uniqid().'.'.$extension;
                copy(ROOT.$obj->image,ROOT.$image);
                $published->image = $image;
            }

            if(!empty($obj->image_preview) && file_exists(ROOT . $obj->image_preview)) {
                $extension = pathinfo(ROOT.$obj->image_preview, PATHINFO_EXTENSION);
                $image_preview = '/public/src/images/articles/'.uniqid().'.'.$extension;
                copy(ROOT.$obj->image_preview,ROOT.$image_preview);
                $published->image_preview = $image_preview;
            }

            $published->save();

            // Копируем галерею
            $draftGallery = Gallery::where("WHERE type = 'articles' AND ids = ?", [$obj->id]);
            $publishedGallery = Gallery::where("WHERE type = 'articles' AND ids = ?", [$published->id]);

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
                $newGallery->type = 'articles';
                $newGallery->ids = $published->id;
                $newGallery->alt = $item->alt;
                $newGallery->rate = $item->rate;
                $newGallery->show = $item->show;
                $newGallery->edit_date = date("Y-m-d H:i:s");
                $newGallery->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
                
                // Копируем файлы
                if (!empty($item->image) && file_exists(ROOT . $item->image)) {
                    $extension = pathinfo(ROOT . $item->image, PATHINFO_EXTENSION);
                    $newImage = '/public/src/images/articles/gallery_' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->image, ROOT . $newImage);
                    $newGallery->image = $newImage;
                }
                
                if (!empty($item->image_small) && file_exists(ROOT . $item->image_small)) {
                    $extension = pathinfo(ROOT . $item->image_small, PATHINFO_EXTENSION);
                    $newImageSmall = '/public/src/images/articles/gallery_small_' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->image_small, ROOT . $newImageSmall);
                    $newGallery->image_small = $newImageSmall;
                }
                
                if (!empty($item->image_origin) && file_exists(ROOT . $item->image_origin)) {
                    $extension = pathinfo(ROOT . $item->image_origin, PATHINFO_EXTENSION);
                    $newImageOrigin = '/public/src/images/articles/gallery_origin_' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->image_origin, ROOT . $newImageOrigin);
                    $newGallery->image_origin = $newImageOrigin;
                }
                
                $newGallery->save();
            }

            // Копируем файлы
            $draftFiles = Files::where("WHERE type = 'articles' AND ids = ?", [$obj->id]);
            $publishedFiles = Files::where("WHERE type = 'articles' AND ids = ?", [$published->id]);

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
                $newFile->type = 'articles';
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
                    $newFilePath = '/public/src/files/articles/' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->file, ROOT . $newFilePath);
                    $newFile->file = $newFilePath;
                }
                
                $newFile->save();
            }

            // Обновляем связь черновика
            $obj->original_id = $published->id;
            $obj->save();
        }

        header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
        exit;
        
    elseif (isset($_GET['delete'])) :

        $id = $_GET['delete'];
        $obj = Articles::findById($id);
        
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        
        $published = null;
        if ($obj->original_id) {
            $published = Articles::findById($obj->original_id);
        }
        
        // Удаление изображений, если они есть
        if (($config['fields']['image']['enabled'] ?? false) && !empty($obj->image) && file_exists(ROOT . $obj->image)) {
            unlink(ROOT . $obj->image);
        }
        if (($config['fields']['preview']['image_preview']['enabled'] ?? false) && !empty($obj->image_preview) && file_exists(ROOT . $obj->image_preview)) {
            unlink(ROOT . $obj->image_preview);
        }

        // Удаляем галерею черновика
        Gallery::delAll('articles', $obj->id);

        // Удаляем файлы черновика
        Files::delAll('articles', $obj->id);
        
        $obj->delete();

        // Если есть опубликованная версия и черновик был последним, удаляем и её
        if ($published && $useDrafts) {
            // Проверяем есть ли другие черновики для этой статьи
            $otherDrafts = Articles::where("WHERE original_id = ? AND id != ?", [$published->id, $obj->id]);
            
            if (empty($otherDrafts)) {
                // Удаление изображений опубликованной версии
                if (($config['fields']['image']['enabled'] ?? false) && !empty($published->image) && file_exists(ROOT . $published->image)) {
                    unlink(ROOT . $published->image);
                }
                if (($config['fields']['preview']['image_preview']['enabled'] ?? false) && !empty($published->image_preview) && file_exists(ROOT . $published->image_preview)) {
                    unlink(ROOT . $published->image_preview);
                }

                // Удаляем галерею опубликованной версии
                Gallery::delAll('articles', $published->id);

                // Удаляем файлы опубликованной версии
                Files::delAll('articles', $published->id);
                
                $published->delete();
            }
        }
        
        $_SESSION['notice'] = 'Удалено';

        header("Location: {$_SERVER['REDIRECT_URL']}");
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
            $whereConditions[] = "(`name` like '%{$search}%' OR `text` like '%{$search}%' OR `textshort` like '%{$search}%' OR `text2` like '%{$search}%')";
        }
        
        // Фильтр по статусу черновика
        $whereConditions[] = "is_draft = 1";

        // Формируем полное WHERE условие
        $where = '';
        if (!empty($whereConditions)) {
            $where = 'WHERE ' . implode(' AND ', $whereConditions);
        }

        $perPage = $_SESSION['articles']['per_page'] ?? $config['pagination']['default_per_page'];
        $order_by = $config['pagination']['order_by'] ?? 'ORDER BY date DESC, id DESC';

        // Вызов пагинации
        $result = Pagination::create(
            modelClass: Articles::class,
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
                    
                    <?php if (($config['list']['image_preview']['enabled'] ?? false) && ($config['fields']['preview']['image_preview']['enabled'] ?? false)): ?>
                        <div class="image_preview"><?= $config['list']['image_preview']['title'] ?? 'Превью' ?></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                        <div class="info"><?= $config['list']['name']['title'] ?? 'Название' ?></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['section']['enabled'] ?? false) && ($config['fields']['section_id']['enabled'] ?? false)): ?>
                        <div class="category"><?= $config['list']['section']['title'] ?? 'Раздел' ?></div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                    <?php endif; ?>
                    
                    <?php if ($useDrafts && ($config['list']['published_date']['enabled'] ?? false)): ?>
                        <div class="modified_date"><?= $config['list']['published_date']['title'] ?? 'Публикация' ?></div>
                    <?php endif; ?>
                                        
                    <div class="actions"></div>
                </div>
                <div class="table_body<?= ($totalCount <= $perPage && empty($_GET['search']) && $config['list']['handler']) ? ' sortbox-items' : '' ?>">
                <?php foreach ($objs as $obj):
                    if(!empty($obj->section_id) && ($config['fields']['section_id']['enabled'] ?? false)) {
                        $obj->section = ArticlesSections::findById($obj->section_id);
                    }

                    if(!empty($obj->original_id)) {
                        $original = Articles::findById($obj->original_id);
                        $pageUrl = Articles::getUrl($obj->original_id);
                    }
                    
                    // Проверяем наличие изменений в черновике
                    $has_changes = false;
                    if ($useDrafts && !empty($original) && $obj->edit_date != $original->edit_date || empty($original)) {
                        $has_changes = true;
                    }
                ?>

                    <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                        <?php if ($config['list']['handler']): ?>
                            <div class="handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($_GET['search'])) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                        <?php endif; ?>
                        
                        <?php if (($config['list']['image_preview']['enabled'] ?? false) && ($config['fields']['preview']['image_preview']['enabled'] ?? false)): ?>
                            <div class="image_preview">
                                <?php if (!empty($obj->image_preview)): ?>
                                    <img src="<?= $obj->image_preview ?>" alt="<?= htmlspecialchars($obj->name, ENT_QUOTES, 'UTF-8') ?>" width="50" height="50">
                                <?php else: ?>
                                    <div class="no-image">Нет фото</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                            <div class="info">
                                <div class="name">
                                    <?= $obj->name ?>
                                </div>
                                <?php if ($config['list']['textshort'] && !empty($obj->textshort) && ($config['fields']['preview']['textshort']['enabled'] ?? false)): ?>
                                    <div class="textshort"><?= mb_substr($obj->textshort, 0, 100) . (mb_strlen($obj->textshort) > 100 ? '...' : '') ?></div>
                                <?php endif; ?>

                                <?php if ($useDrafts && $has_changes): ?>
                                    <div class="comment alarm">Есть неопубликованные изменения</div>
                                <?php elseif ($useDrafts && !empty($original)): ?>
                                    <div class="comment success">Опубликовано</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (($config['list']['section']['enabled'] ?? false) && ($config['fields']['section_id']['enabled'] ?? false)): ?>
                            <div class="category">
                                <?php if(!empty($obj->section)): ?>
                                    <?= $obj->section->name ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                            <div class="modified_date">
                                <?= $obj->edit_date ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($useDrafts && ($config['list']['published_date']['enabled'] ?? false)): ?>
                            <div class="modified_date">
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