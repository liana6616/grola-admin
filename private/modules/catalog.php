<?php

use app\Models\Catalog;
use app\Models\Categories;
use app\Models\Manufacturers;
use app\Models\CatalogPrices;
use app\Models\FinishedProducts;
use app\Models\FinishedProductsCatalog;
use app\Models\CatalogParams;
use app\Models\Params;
use app\Models\ParamsGroups;
use app\Models\ParamsGroupsItems;
use app\Models\Directories;
use app\Models\DirectoriesValues;
use app\Models\Admins;
use app\Models\Gallery;
use app\Models\Files;
use app\FileUpload;
use app\Helpers;
use app\Pagination;
use app\Form;

$configPath = '/config/modules/catalog.php';

if (!file_exists(ROOT.$configPath)):
    ?>
    <div class="alert alert-danger">
        Внимание: отсутствует конфигурационный файл: <strong><?= $configPath ?></strong>
    </div>
<?php else:

    $config = require_once ROOT.$configPath;

    $supportsDrafts = Catalog::supportsDrafts();
    $useDrafts = $supportsDrafts && ($config['drafts']['enabled'] ?? false);

    if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

        $obj = new Catalog();
        $original = null;
        
        $obj->show = 1;
        $obj->rate = 0;
        $obj->price = 0;
        $obj->price_old = 0;
        $obj->count = 0;
        $obj->action = 0;
        $obj->new = 0;
        $obj->popular = 0;
        $obj->edit_date = date('Y-m-d H:i:s', time());
        
        $title = 'Добавление товара';
        $id = false;
        $action = '';
        
        if(!empty($_GET['edit'])) {
            $id = (int)$_GET['edit'];
            $title = 'Редактирование товара';
            $action = 'edit';
        }
        if(!empty($_GET['copy']) && $config['actions']['copy']) {
            $id = (int)$_GET['copy'];
            $title = 'Копирование товара';
            $action = 'copy';
        }

        $id0 = $id;
        
        if(!empty($id)) {
            $obj = Catalog::findById($id);
            if (!$obj) {
                header("Location: {$_SERVER['REDIRECT_URL']}");
                exit;
            }
            elseif($obj->is_draft == 0 && $useDrafts) {
                $draft = Catalog::where('WHERE original_id = ?', [$obj->id], true);
                if ($draft) {
                    header("Location: {$_SERVER['REDIRECT_URL']}?".$action."=".$draft->id);
                    exit;
                }
            }

            if($useDrafts && $obj->original_id > 0) {
                $original = Catalog::findById($obj->original_id);
            }
        }
        
        if(!empty($_GET['copy']) && $config['actions']['copy']) {
            $obj->image_preview = '';
            $obj->is_draft = 1;
            $obj->original_id = 0;
            $id = false;
        }

        if(!empty($obj->url)) $obj->url = str_replace('draft-','',$obj->url);

        $obj->show = (bool)($obj->show ?? 1);
        $obj->action = (bool)($obj->action ?? 0);
        $obj->new = (bool)($obj->new ?? 0);
        $obj->popular = (bool)($obj->popular ?? 0);

        $has_changes = false;

        if (isset($_GET['edit'])) {
            if(empty($original)) {
                $has_changes = true;
            }
            else if ($original->edit_date != $obj->edit_date) {
                $has_changes = true;
            }
        } else {
            $has_changes = true;
        }

        if(!isset($_GET['edit'])) $has_changes = true;

        $breadcrumbs = '';
        if (!empty($obj->category_id)) {
            $bread = Catalog::adminBreadCrumbs($obj->category_id);
            $breadcrumbs = Categories::adminBread($bread, 1);
        }
        
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
                
                <?php if($config['actions']['open'] && !empty($_GET['edit']) && !empty($obj->original_id)): ?>
                    <?php $published = Catalog::findById($obj->original_id); ?>
                    <?php if ($published): ?>
                        <a href='<?= Catalog::getUrl($published->id) ?>' class='btn btn_white' rel='external' target="_blank">Смотреть на сайте</a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($config['actions']['open'] && !empty($_GET['edit']) && $useDrafts): ?>
                    <a href='<?= Catalog::getUrl($id) ?>' class='btn btn_white' rel='external' target="_blank">Предпросмотр черновика</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="edit_block">
            <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form' id='edit_form'
                  enctype='multipart/form-data'>
                
                <?php if (isset($_GET['copy']) && $config['actions']['copy']): ?>
                    <input type="hidden" name="copy_from" value="<?= $_GET['copy'] ?>">
                <?php endif; ?>
                                
                <?php if ($useDrafts && $obj->original_id): ?>
                    <input type="hidden" name="original_id" value="<?= $obj->original_id ?>">
                <?php endif; ?>

                <input type="hidden" name="publish" id="publish" value="0">

                <?php if (!empty($breadcrumbs)): ?>
                <div class="breadcrumbs_block">
                    <?= $breadcrumbs ?>
                </div>
                <?php endif; ?>
                
                <div class="edit_tabs">
                    <div class="edit_tabs_nav">
                        <?php if ($config['fields']['enabled']): ?>
                            <button type="button" class="edit_tab_nav active" data-tab="content"><?= $config['fields']['tab_name'] ?? 'Основная информация' ?></button>
                        <?php endif; ?>
                        
                        <?php if ($config['prices']['enabled']): ?>
                            <button type="button" class="edit_tab_nav" data-tab="prices"><?= $config['prices']['tab_name'] ?? 'Стоимость по весу' ?></button>
                        <?php endif; ?>

                        <?php if ($config['params']['enabled']): ?>
                            <button type="button" class="edit_tab_nav" data-tab="params"><?= $config['params']['tab_name'] ?? 'Характеристики' ?></button>
                        <?php endif; ?>

                        <?php if ($config['finished_products']['enabled']): ?>
                            <button type="button" class="edit_tab_nav" data-tab="finished_products"><?= $config['finished_products']['tab_name'] ?? 'Готовая продукция' ?></button>
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
                        <?php if ($config['fields']['enabled']): ?>
                        <div class="edit_tab_content active" id="tab_content">
                            <div class="flex3">
                                <?php if ($config['fields']['show']['enabled'] ?? false): ?>
                                    <?= Form::checkbox('show', (bool)$obj->show, $config['fields']['show']['title'] ?? 'Показывать на сайте', 1, null) ?>
                                <?php endif; ?>

                                <?php if ($config['fields']['rate']['enabled'] ?? false): ?>
                                    <?= Form::input($config['fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex3 top">

                                <?php if ($config['fields']['action']['enabled'] ?? false): ?>
                                    <?= Form::checkbox('action', (bool)$obj->action, $config['fields']['action']['title'] ?? 'Шильдик "Акция"', 1, null) ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['new']['enabled'] ?? false): ?>
                                    <?= Form::checkbox('new', (bool)$obj->new, $config['fields']['new']['title'] ?? 'Шильдик "Новинка"', 1, null) ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['popular']['enabled'] ?? false): ?>
                                    <?= Form::checkbox('popular', (bool)$obj->popular, $config['fields']['popular']['title'] ?? 'Шильдик "Популярное"', 1, null) ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($config['fields']['category_id']['enabled'] ?? false): ?>
                                <?php
                                $allCategories = Categories::getHierarchical();
                                ?>
                                <?= Form::select(
                                    $config['fields']['category_id']['title'] ?? 'Категория товара', 
                                    'category_id', 
                                    $allCategories, 
                                    $obj->category_id, 
                                    true, 
                                    'Выберите категорию', 
                                    'name', 
                                    0, 
                                    'category-select', 
                                    0, 
                                    '' 
                                ) ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['manufacturer_id']['enabled'] ?? false): ?>
                                <?php
                                $manufacturers = Manufacturers::findAll();
                                ?>
                                <?= Form::select(
                                    $config['fields']['manufacturer_id']['title'] ?? 'Производитель', 
                                    'manufacturer_id', 
                                    $manufacturers, 
                                    $obj->manufacturer_id, 
                                    true, 
                                    'Не выбран', 
                                    'name', 
                                    0, 
                                    '', 
                                    0, 
                                    '' 
                                ) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['name']['title'] ?? 'Название товара (H1)', 'name', $obj->name, 1, '', '', '') ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['url']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['url']['title'] ?? 'Ссылка (URL)', 'url', $obj->url, 0, '', '', '') ?>
                            <?php endif; ?>

                            <div class="flex3">
                                
                                <?php if ($config['fields']['price']['enabled'] ?? false): ?>
                                    <?= Form::input($config['fields']['price']['title'] ?? 'Стоимость (руб)', 'price', $obj->price, 0, 'number', '', '') ?>
                                <?php endif; ?>
                                
                                <?php if ($config['fields']['price_old']['enabled'] ?? false): ?>
                                    <?= Form::input($config['fields']['price_old']['title'] ?? 'Старая цена (руб)', 'price_old', $obj->price_old, 0, 'number', '', '') ?>
                                <?php endif; ?>

                                <?php if ($config['fields']['count']['enabled'] ?? false): ?>
                                    <?= Form::input($config['fields']['count']['title'] ?? 'Доступное количество', 'count', $obj->count, 0, 'number', '', '') ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($config['fields']['image_preview']['enabled'] ?? false): ?>
                                <?php 
                                $title = $config['fields']['image_preview']['title'];
                                $width = $config['fields']['image_preview']['width'] ?? 300;
                                $height = $config['fields']['image_preview']['height'] ?? 300;
                                
                                // Добавляем размеры только если ширина не равна 0
                                if ($width != 0 && $height != 0) {
                                    $title .= " ($width" . "х" . "$height)";
                                }
                                ?>
                                <?= Form::image($title, 'image_preview', $obj, '', 0) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['textshort']['enabled'] ?? false): ?>
                                <?= Form::textbox($config['fields']['textshort']['title'] ?? 'Краткое описание', 'textshort', $obj->textshort) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['text']['enabled'] ?? false): ?>
                                <?= Form::textbox($config['fields']['text']['title'] ?? 'Полное описание товара', 'text', $obj->text) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['text2']['enabled'] ?? false): ?>
                                <?= Form::textbox($config['fields']['text2']['title'] ?? 'Дополнительное описание', 'text2', $obj->text2 ?? '') ?>
                            <?php endif; ?>

                        </div>
                        <?php endif; ?>
                        
                        <!-- Вкладка "Стоимость по весу" -->
                        <?php if ($config['prices']['enabled']): ?>
                        <div class="edit_tab_content" id="tab_prices">
                            <h3 class="params_description"><?= $config['prices']['title'] ?? 'Стоимость в зависимости от веса' ?></h3>
                            
                            <div id="prices-container">
                                <?php
                                $prices = Catalog::getPrices($id0 ?: 0);
                                $priceIndex = 0;
                                
                                if (!empty($prices)):  
                                    foreach ($prices as $price):
                                ?>
                                    <?= Catalog::catalogPriceCard($price->id,$priceIndex); ?>
                                <?php  
                                        $priceIndex++;
                                    endforeach;
                                endif;
                                ?>
                            </div>
                            
                            <button type="button" class="btn btn_add btn_gray catalogPriceAdd" data-index="<?= $priceIndex ?>">Добавить вариант цены</button>
                            
                        </div>
                        <?php endif; ?>

                        <!-- Вкладка "Характеристики" -->
                        <?php if ($config['params']['enabled']): ?>
                        <div class="edit_tab_content" id="tab_params">
                            <?php if ($config['params']['description'] ?? false): ?>
                                <p class="params_description"><?= $config['params']['description'] ?></p>
                            <?php endif; ?>
                            
                            <div class="input_block" id="params-container">
                                <?php
                                if (!empty($obj->category_id)) {
                                    $category = Categories::findById($obj->category_id);
                                    
                                    if ($category && $category->template_id) {
                                        $groups = ParamsGroups::findWhere("WHERE template_id = " . $category->template_id . " AND `show` = 1 ORDER BY rate ASC");
                                        
                                        foreach ($groups as $group) {
                                            echo '<fieldset class="input_block">';
                                            echo '<legend>' . htmlspecialchars($group->name) . '</legend>';
                                            
                                            $groupItems = ParamsGroupsItems::findWhere("WHERE group_id = " . $group->id . " AND `show` = 1 ORDER BY rate ASC");
                                            
                                            foreach ($groupItems as $item) {
                                                $param = Params::findById($item->param_id);
                                                
                                                if ($param) {
                                                    $currentValue = '';
                                                    if ($id) {
                                                        $paramValue = CatalogParams::findWhere("WHERE catalog_id = " . $id . " AND param_id = " . $item->param_id);
                                                        if (!empty($paramValue)) {
                                                            $currentValue = $paramValue[0]->value;
                                                        }
                                                    }
                                                    
                                                    if ($item->type == 1 && $item->directory_id) {
                                                        $directoryValues = DirectoriesValues::findWhere("WHERE directory_id = " . $item->directory_id . " AND `show` = 1 ORDER BY rate ASC");
                                                        $options = [];
                                                        foreach ($directoryValues as $value) {
                                                            $options[$value->id] = $value->value;
                                                        }
                                                        
                                                        echo Form::select(
                                                            $param->name,
                                                            'params[' . $item->param_id . ']',
                                                            $options,
                                                            $currentValue,
                                                            true,
                                                            'Не выбрано',
                                                            '',
                                                            2,
                                                            '',
                                                            0,
                                                            ''
                                                        );
                                                    } else {
                                                        echo Form::input(
                                                            $param->name,
                                                            'params[' . $item->param_id . ']',
                                                            $currentValue,
                                                            false,
                                                            'text',
                                                            '',
                                                            'placeholder="Введите значение"'
                                                        );
                                                    }
                                                }
                                            }
                                            
                                            echo '</fieldset>';
                                        }
                                    } else {
                                        echo '<p class="params_description error">Для выбранной категории не настроен шаблон параметров.</p>';
                                    }

                                } else {
                                    echo '<p class="params_description error">Сначала выберите категорию товара и сохраните изменения.</p>';
                                }
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Вкладка "Готовая продукция" -->
                        <?php if ($config['finished_products']['enabled']): ?>
                        <div class="edit_tab_content" id="tab_finished_products">

                            <?php if ($config['finished_products']['description'] ?? false): ?>
                                <p class="params_description"><?= $config['finished_products']['description'] ?></p>
                            <?php endif; ?>
                            
                            <?php
                            $currentProducts = [];
                            if ($id) {
                                $links = FinishedProductsCatalog::findWhere("WHERE catalog_id = " . $id);
                                foreach ($links as $link) {
                                    $currentProducts[] = $link->product_id;
                                }
                            }
                            $currentValue = implode('|', $currentProducts);
                            
                            $allProducts = FinishedProducts::getHierarchical();
                            
                            echo Form::multiple(
                                $config['finished_products']['title'] ?? 'Готовая продукция',
                                'finished_products',
                                $allProducts,
                                $currentValue,
                                $config['finished_products']['description'] ?? ''
                            );
                            ?>
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
                            
                            <?= Form::gallery($config['gallery']['title'] ?? 'Фотогалерея товара', 'gallery', Gallery::findGallery('product',$obj->id)) ?>
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
                            
                            <?= Form::files($config['files']['title'] ?? 'Файлы', 'files', Files::findFiles('catalog',$obj->id)) ?>
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
        
        if (isset($_POST['edit']) && $id > 0) {
            FileUpload::deleteImageFile();
            $obj = Catalog::findById($id);
            if (!$obj) {
                header("Location: {$_SERVER['REDIRECT_URL']}");
                exit;
            }
        } else {
            $obj = new Catalog();
        }
        
        $_SESSION['notice'] = 'Добавлено';
        if(isset($_POST['edit'])) $_SESSION['notice'] = 'Изменено';
        if(isset($_POST['copy_from'])) $_SESSION['notice'] = 'Скопировано';
        if(!empty($_POST['publish']) && $useDrafts) $_SESSION['notice'] = 'Опубликовано';

        $url = trim($_POST['url'] ?? '');
        if (empty($url) && ($config['fields']['url']['enabled'] ?? false)) {
            $url = Helpers::str2url(trim($_POST['name'] ?? ''));
        }
        
        if ($url <> '' && ($config['fields']['url']['enabled'] ?? false)) {
            $i = 1;
            $u = $url;
            $existingId = $obj->id ?? 0;
            $cat = Catalog::findWhere("WHERE url='" . $url . "' AND id<>'" . $existingId . "' AND original_id<>0 LIMIT 1");
            while ($cat) {
                $url = $u . '-' . $i;
                $cat = Catalog::findWhere("WHERE url='" . $url . "' AND id<>'" . $existingId . "' AND original_id<>0 LIMIT 1");
                $i++;
            }
        }

        $url = str_replace('draft-','',$url);
        $url = 'draft-'.$url;

        $oldData = null;
        if ($useDrafts && isset($_POST['edit']) && $id > 0) {
            $oldData = Catalog::findById($id)->toArray();
        }

        $obj->url = ($config['fields']['url']['enabled'] ?? false) ? $url : '';
        $obj->name = ($config['fields']['name']['enabled'] ?? false) ? trim($_POST['name'] ?? '') : '';
        $obj->category_id = ($config['fields']['category_id']['enabled'] ?? false) ? (int)($_POST['category_id'] ?? 0) : 0;
        $obj->manufacturer_id = ($config['fields']['manufacturer_id']['enabled'] ?? false) ? (int)($_POST['manufacturer_id'] ?? 0) : 0;
        $obj->price = ($config['fields']['price']['enabled'] ?? false) ? (int)($_POST['price'] ?? 0) : 0;
        $obj->price_old = ($config['fields']['price_old']['enabled'] ?? false) ? (int)($_POST['price_old'] ?? 0) : 0;
        $obj->count = ($config['fields']['count']['enabled'] ?? false) ? (int)($_POST['count'] ?? 0) : 0;
        $obj->text = ($config['fields']['text']['enabled'] ?? false) ? trim($_POST['text'] ?? '') : '';
        $obj->text2 = ($config['fields']['text2']['enabled'] ?? false) ? trim($_POST['text2'] ?? '') : '';
        $obj->textshort = ($config['fields']['textshort']['enabled'] ?? false) ? trim($_POST['textshort'] ?? '') : '';
        $obj->show = ($config['fields']['show']['enabled'] ?? false) ? (int)($_POST['show'] ?? 0) : 1;
        $obj->action = ($config['fields']['action']['enabled'] ?? false) ? (int)($_POST['action'] ?? 0) : 0;
        $obj->new = ($config['fields']['new']['enabled'] ?? false) ? (int)($_POST['new'] ?? 0) : 0;
        $obj->popular = ($config['fields']['popular']['enabled'] ?? false) ? (int)($_POST['popular'] ?? 0) : 0;
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
        
        if ($config['seo']['enabled']) {
            $obj->title = ($config['seo']['title']['enabled'] ?? false) ? trim($_POST['title'] ?? '') : '';
            $obj->keywords = ($config['seo']['keywords']['enabled'] ?? false) ? trim($_POST['keywords'] ?? '') : '';
            $obj->description = ($config['seo']['description']['enabled'] ?? false) ? trim($_POST['description'] ?? '') : '';
        }
        
        $obj->edit_date = date("Y-m-d H:i:s");
        $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
        $obj->is_draft = 1;

        $obj->save();

        if ($config['params']['enabled'] && isset($_POST['params'])) {
            Catalog::saveParams($obj->id, $_POST['params']);
        }

        // Стоимость для сортировки
        $obj->price_sorts = $obj->price;

        if ($config['prices']['enabled'] && isset($_POST['prices'])) {
            $oldPrices = CatalogPrices::where("WHERE catalog_id = ?", [$obj->id]);
            foreach ($oldPrices as $price) {
                $price->delete();
            }
            
            foreach ($_POST['prices'] as $priceData) {
                if (!empty($priceData['weight']) || !empty($priceData['price'])) {
                    $price = new CatalogPrices();
                    $price->catalog_id = $obj->id;
                    $price->weight = !empty($priceData['weight']) ? (float)$priceData['weight'] : null;
                    $price->price = !empty($priceData['price']) ? (int)$priceData['price'] : null;
                    $price->count = !empty($priceData['count']) ? (int)$priceData['count'] : null;
                    $price->unit = !empty($priceData['unit']) ? trim($priceData['unit']) : null;
                    $price->edit_date = date("Y-m-d H:i:s");
                    $price->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
                    $price->save();

                    if($obj->price_sorts == 0 || $obj->price_sorts > $price->price) $obj->price_sorts = $price->price;
                }
            }
        }
        $obj->save();


        if ($config['finished_products']['enabled'] && isset($_POST['finished_products'])) {
            Catalog::saveFinishedProducts($obj->id, $_POST['finished_products']);
        }

        if (($config['fields']['image_preview']['enabled'] ?? false)) {
            $width = $config['fields']['image_preview']['width'] ?? 300;
            $height = $config['fields']['image_preview']['height'] ?? 300;
            
            FileUpload::uploadImage(
                'image_preview',  
                get_class($obj),  
                'image_preview',  
                $obj->id,  
                $width,  
                $height,  
                '/public/src/images/catalog/',  
                0 
            );
        }

        FileUpload::updateGallery();
        FileUpload::uploadGallery('gallery', 'product', $obj->id,  
            $config['gallery']['image_width'] ?? 800,  
            $config['gallery']['image_height'] ?? 600,  
            '/public/src/images/catalog/',  
            $config['gallery']['thumbnail_width'] ?? 400,  
            $config['gallery']['thumbnail_height'] ?? 300,  
            1);

        FileUpload::updateFiles();
        FileUpload::uploadFiles('files', 'catalog', $obj->id, '/public/src/files/catalog/');

        if ($publish || !$useDrafts) {
            $draftObj = Catalog::findById($obj->id);
            
            if($draftObj->original_id) {
                $published = Catalog::findById($draftObj->original_id);
            } else {
                $published = new Catalog();
            }
            
            $published = Catalog::copyData($draftObj, $published, ['id', 'is_draft', 'original_id','image_preview']);
            
            $published->url = str_replace('draft-','',$published->url);
            $published->is_draft = 0;
            $published->original_id = 0;
            $published->edit_date = date("Y-m-d H:i:s");
            $published->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
            
            if(!empty($published->image_preview) && file_exists(ROOT . $published->image_preview)) {
                unlink(ROOT . $published->image_preview);
                $published->image_preview = '';
            }
            
            if(!empty($draftObj->image_preview) && file_exists(ROOT . $draftObj->image_preview)) {
                $extension = pathinfo(ROOT . $draftObj->image_preview, PATHINFO_EXTENSION);
                $image_preview = '/public/src/images/catalog/'.uniqid().'.'.$extension;
                copy(ROOT . $draftObj->image_preview, ROOT . $image_preview);
                $published->image_preview = $image_preview;
            }
            
            $published->save();
            
            $draftObj->original_id = $published->id;
            $draftObj->save();

            $draftParams = CatalogParams::where("WHERE catalog_id = ?", [$obj->id]);
            $publishedParams = CatalogParams::where("WHERE catalog_id = ?", [$published->id]);

            foreach ($publishedParams as $item) {
                $item->delete();
            }

            foreach ($draftParams as $item) {
                $newParam = new CatalogParams();
                $newParam->catalog_id = $published->id;
                $newParam->param_id = $item->param_id;
                $newParam->value = $item->value;
                $newParam->edit_date = date("Y-m-d H:i:s");
                $newParam->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
                $newParam->save();
            }

            $draftPrices = CatalogPrices::where("WHERE catalog_id = ?", [$obj->id]);
            $publishedPrices = CatalogPrices::where("WHERE catalog_id = ?", [$published->id]);

            foreach ($publishedPrices as $item) {
                $item->delete();
            }

            foreach ($draftPrices as $item) {
                $newPrice = new CatalogPrices();
                $newPrice->catalog_id = $published->id;
                $newPrice->weight = $item->weight;
                $newPrice->price = $item->price;
                $newPrice->count = $item->count;
                $newPrice->unit = $item->unit;
                $newPrice->edit_date = date("Y-m-d H:i:s");
                $newPrice->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
                $newPrice->save();
            }

            $draftLinks = FinishedProductsCatalog::where("WHERE catalog_id = ?", [$obj->id]);
            $publishedLinks = FinishedProductsCatalog::where("WHERE catalog_id = ?", [$published->id]);

            foreach ($publishedLinks as $item) {
                $item->delete();
            }

            foreach ($draftLinks as $item) {
                $newLink = new FinishedProductsCatalog();
                $newLink->catalog_id = $published->id;
                $newLink->product_id = $item->product_id;
                $newLink->edit_date = date("Y-m-d H:i:s");
                $newLink->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
                $newLink->save();
            }

            $draftGallery = Gallery::where("WHERE type = 'catalog' AND ids = ?", [$obj->id]);
            $publishedGallery = Gallery::where("WHERE type = 'catalog' AND ids = ?", [$published->id]);

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

            foreach ($draftGallery as $item) {
                $newGallery = new Gallery();
                $newGallery->type = 'catalog';
                $newGallery->ids = $published->id;
                $newGallery->alt = $item->alt;
                $newGallery->rate = $item->rate;
                $newGallery->show = $item->show;
                $newGallery->edit_date = date("Y-m-d H:i:s");
                $newGallery->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
                
                if (!empty($item->image) && file_exists(ROOT . $item->image)) {
                    $extension = pathinfo(ROOT . $item->image, PATHINFO_EXTENSION);
                    $newImage = '/public/src/images/catalog/gallery_' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->image, ROOT . $newImage);
                    $newGallery->image = $newImage;
                }
                
                if (!empty($item->image_small) && file_exists(ROOT . $item->image_small)) {
                    $extension = pathinfo(ROOT . $item->image_small, PATHINFO_EXTENSION);
                    $newImageSmall = '/public/src/images/catalog/gallery_small_' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->image_small, ROOT . $newImageSmall);
                    $newGallery->image_small = $newImageSmall;
                }
                
                if (!empty($item->image_origin) && file_exists(ROOT . $item->image_origin)) {
                    $extension = pathinfo(ROOT . $item->image_origin, PATHINFO_EXTENSION);
                    $newImageOrigin = '/public/src/images/catalog/gallery_origin_' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->image_origin, ROOT . $newImageOrigin);
                    $newGallery->image_origin = $newImageOrigin;
                }
                
                $newGallery->save();
            }

            $draftFiles = Files::where("WHERE type = 'catalog' AND ids = ?", [$obj->id]);
            $publishedFiles = Files::where("WHERE type = 'catalog' AND ids = ?", [$published->id]);

            foreach ($publishedFiles as $item) {
                if (!empty($item->file) && file_exists(ROOT . $item->file)) {
                    unlink(ROOT . $item->file);
                }
                $item->delete();
            }

            foreach ($draftFiles as $item) {
                $newFile = new Files();
                $newFile->type = 'catalog';
                $newFile->ids = $published->id;
                $newFile->filename = $item->filename;
                $newFile->extension = $item->extension;
                $newFile->rate = $item->rate;
                $newFile->show = $item->show;
                $newFile->edit_date = date("Y-m-d H:i:s");
                $newFile->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
                
                if (!empty($item->file) && file_exists(ROOT . $item->file)) {
                    $extension = pathinfo(ROOT . $item->file, PATHINFO_EXTENSION);
                    $newFilePath = '/public/src/files/catalog/' . uniqid() . '.' . $extension;
                    copy(ROOT . $item->file, ROOT . $newFilePath);
                    $newFile->file = $newFilePath;
                }
                
                $newFile->save();
            }
        }

        header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
        exit;
        
    elseif (isset($_GET['delete'])) :

        $id = $_GET['delete'];
        $obj = Catalog::findById($id);
        
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        
        $published = null;
        if ($obj->original_id) {
            $published = Catalog::findById($obj->original_id);
        }
        
        if (($config['fields']['image_preview']['enabled'] ?? false) && !empty($obj->image_preview) && file_exists(ROOT . $obj->image_preview)) {
            unlink(ROOT . $obj->image_preview);
        }

        $params = CatalogParams::where("WHERE catalog_id = ?", [$obj->id]);
        foreach ($params as $param) {
            $param->delete();
        }
        
        $prices = CatalogPrices::where("WHERE catalog_id = ?", [$obj->id]);
        foreach ($prices as $price) {
            $price->delete();
        }
        
        $links = FinishedProductsCatalog::where("WHERE catalog_id = ?", [$obj->id]);
        foreach ($links as $link) {
            $link->delete();
        }
        
        Gallery::delAll('product', $obj->id);
        Files::delAll('catalog', $obj->id);
        
        $obj->delete();

        if ($published && $useDrafts) {
            $otherDrafts = Catalog::where("WHERE original_id = ? AND id != ?", [$published->id, $obj->id]);
            
            if (empty($otherDrafts)) {
                if (($config['fields']['image_preview']['enabled'] ?? false) && !empty($published->image_preview) && file_exists(ROOT . $published->image_preview)) {
                    unlink(ROOT . $published->image_preview);
                }

                $publishedParams = CatalogParams::where("WHERE catalog_id = ?", [$published->id]);
                foreach ($publishedParams as $param) {
                    $param->delete();
                }
                
                $publishedPrices = CatalogPrices::where("WHERE catalog_id = ?", [$published->id]);
                foreach ($publishedPrices as $price) {
                    $price->delete();
                }
                
                $publishedLinks = FinishedProductsCatalog::where("WHERE catalog_id = ?", [$published->id]);
                foreach ($publishedLinks as $link) {
                    $link->delete();
                }
                
                Gallery::delAll('product', $published->id);
                Files::delAll('catalog', $published->id);
                
                $published->delete();
            }
        }
        
        $_SESSION['notice'] = 'Удалено';

        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;

    else :
        $title = $config['module']['title'] ?? '';

        $whereConditions = [];
        $params = [];
        
        $search = trim($_GET['search'] ?? '');
        if (!empty($search) && $config['filters']['search']) {
            $whereConditions[] = "(`name` like '%{$search}%' OR `text` like '%{$search}%' OR `textshort` like '%{$search}%' OR `text2` like '%{$search}%')";
        }
        
        $whereConditions[] = "is_draft = 1";
        
        $where = '';
        if (!empty($whereConditions)) {
            $where = 'WHERE ' . implode(' AND ', $whereConditions);
        }

        $perPage = $_SESSION['catalog']['per_page'] ?? $config['pagination']['default_per_page'];
        $order_by = $config['pagination']['order_by'] ?? 'ORDER BY rate DESC, id DESC';

        $additionalParams = [];

        $result = Pagination::create(
            modelClass: Catalog::class,
            where: $where,
            order: $order_by,
            defaultItemsPerPage: $perPage,
            additionalParams: $additionalParams
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
                    
                    <?php if (($config['list']['image_preview']['enabled'] ?? false) && ($config['fields']['image_preview']['enabled'] ?? false)): ?>
                        <div class="pole image_preview"><?= $config['list']['image_preview']['title'] ?? 'Превью' ?></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['info']['enabled'] ?? false)): ?>
                        <div class="pole info"><?= $config['list']['info']['title'] ?? 'Товар' ?></div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['category']['enabled'] ?? false) && ($config['fields']['category_id']['enabled'] ?? false)): ?>
                        <div class="pole category"><?= $config['list']['category']['title'] ?? 'Категория' ?></div>
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
                    if(!empty($obj->category_id) && ($config['fields']['category_id']['enabled'] ?? false)) {
                        $obj->category = Categories::findById($obj->category_id);
                    }
                    
                    if(!empty($obj->original_id)) {
                        $original = Catalog::findById($obj->original_id);
                        $pageUrl = Catalog::getUrl($obj->original_id);
                    }
                    
                    $has_changes = false;
                    if ($useDrafts && !empty($original) && $obj->edit_date != $original->edit_date || empty($original)) {
                        $has_changes = true;
                    }
                ?>

                    <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                        <?php if ($config['list']['handler']): ?>
                            <div class="pole handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($_GET['search'])) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                        <?php endif; ?>
                        
                        <?php if (($config['list']['image_preview']['enabled'] ?? false) && ($config['fields']['image_preview']['enabled'] ?? false)): ?>
                            <div class="pole image_preview">
                                <div class="title"><?= $config['list']['image_preview']['title'] ?? 'Превью' ?></div>
                                <?php if (!empty($obj->image_preview)): ?>
                                    <img src="<?= $obj->image_preview ?>" alt="<?= htmlspecialchars($obj->name, ENT_QUOTES, 'UTF-8') ?>" width="50" height="50">
                                <?php else: ?>
                                    <div class="no-image">Нет фото</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (($config['list']['info']['enabled'] ?? false)): ?>
                            <div class="pole info">
                                <div class="title"><?= $config['list']['info']['title'] ?? 'Товар' ?></div>
                                <div class="name">
                                    <?= $obj->name ?>
                                </div>

                                <?php if ($useDrafts && $has_changes): ?>
                                    <div class="comment alarm">Есть неопубликованные изменения</div>
                                <?php elseif ($useDrafts && !empty($original)): ?>
                                    <div class="comment success">Опубликовано</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (($config['list']['category']['enabled'] ?? false) && ($config['fields']['category_id']['enabled'] ?? false)): ?>
                            <div class="pole category">
                                <div class="title"><?= $config['list']['category']['title'] ?? 'Категория' ?></div>
                                <?php if(!empty($obj->category)): ?>
                                    <?= $obj->category->name_menu ?? $obj->category->name ?>
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
            <div class='not_found'>Товары не найдены</div>
        <?php
        endif;

    endif;
endif;