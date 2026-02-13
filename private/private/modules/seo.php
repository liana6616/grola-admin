<?php
// Загружаем конфигурацию для SEO
$config = require_once ROOT . '/config/modules/seo.php';

use app\Models\Seo;
use app\Models\Admins;
use app\Helpers;
use app\Pagination;
use app\Form;

if (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new Seo();
    
    // Устанавливаем значения по умолчанию
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
        $obj = Seo::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }

    if(isset($_GET['add']) && isset($_GET['url'])) $obj->url = $_GET['url'];

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

            <?php if ($config['fields']['url']['enabled'] ?? false): ?>
                <?= Form::input($config['fields']['url']['title'] ?? 'Ссылка на страницу', 'url', $obj->url, 1, '', '', 'placeholder="/page/ или https://site.com/page/"') ?>
            <?php endif; ?>

            <fieldset class="input_block">
                <legend>SEO настройки</legend>
                
                <?php if ($config['fields']['title']['enabled'] ?? false): ?>
                    <?= Form::input($config['fields']['title']['title'] ?? 'Title (заголовок страницы)', 'title', $obj->title, 0, '', '', 'placeholder="До 70 символов" maxlength="70"') ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['keywords']['enabled'] ?? false): ?>
                    <?= Form::textarea($config['fields']['keywords']['title'] ?? 'Keywords (ключевые слова)', 'keywords', $obj->keywords, 140, 'placeholder="Через запятую, до 1024 символов" maxlength="1024"') ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['description']['enabled'] ?? false): ?>
                    <?= Form::textarea($config['fields']['description']['title'] ?? 'Description (описание)', 'description', $obj->description, 140, 'placeholder="Краткое описание страницы, до 160 символов" maxlength="160"') ?>
                <?php endif; ?>
                
                <div class="input_block">
                    <small>Title: рекомендуется до 70 символов</small><br>
                    <small>Description: рекомендуется до 160 символов</small><br>
                    <small>Keywords: через запятую, до 1024 символов</small>
                </div>
            </fieldset>

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
        $obj = Seo::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    } else {
        // Добавление новой записи
        $obj = new Seo();
    }
    
    $_SESSION['notice'] = 'Добавлено';
    if(isset($_POST['edit'])) $_SESSION['notice'] = 'Изменено';
    if(isset($_POST['copy_from'])) $_SESSION['notice'] = 'Скопировано';

    // Заполняем данные из формы
    $obj->url = ($config['fields']['url']['enabled'] ?? false) ? trim($_POST['url'] ?? '') : '';
    $obj->title = ($config['fields']['title']['enabled'] ?? false) ? trim($_POST['title'] ?? '') : '';
    $obj->keywords = ($config['fields']['keywords']['enabled'] ?? false) ? trim($_POST['keywords'] ?? '') : '';
    $obj->description = ($config['fields']['description']['enabled'] ?? false) ? trim($_POST['description'] ?? '') : '';
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    header("Location: {$_SERVER['REQUEST_URI']}?edit=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $obj = Seo::findById($_GET['delete']);
    
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
        
        if ($config['fields']['url']['enabled'] ?? false) {
            $searchFields[] = "`url` like '%{$search}%'";
        }
        if ($config['fields']['title']['enabled'] ?? false) {
            $searchFields[] = "`title` like '%{$search}%'";
        }
        if ($config['fields']['keywords']['enabled'] ?? false) {
            $searchFields[] = "`keywords` like '%{$search}%'";
        }
        if ($config['fields']['description']['enabled'] ?? false) {
            $searchFields[] = "`description` like '%{$search}%'";
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

    $perPage = $_SESSION['seo']['per_page'] ?? $config['pagination']['default_per_page'];
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY url ASC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: Seo::class,
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
                <?php if (($config['list']['url']['enabled'] ?? false) && ($config['fields']['url']['enabled'] ?? false)): ?>
                    <div class="pole info"><?= $config['list']['url']['title'] ?? 'Страница' ?></div>
                <?php endif; ?>
                
                <?php if (($config['list']['title']['enabled'] ?? false) && ($config['fields']['title']['enabled'] ?? false)): ?>
                    <div class="pole category"><?= $config['list']['title']['title'] ?? 'Title' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                    <div class="pole modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body<?= ($totalCount <= $perPage && empty($_GET['search']) && $config['list']['handler']) ? ' sortbox-items' : '' ?>">
            <?php foreach ($objs as $obj): ?>
                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if (($config['list']['url']['enabled'] ?? false) && ($config['fields']['url']['enabled'] ?? false)): ?>
                        <div class="pole info">
                            <div class="title"><?= $config['list']['url']['title'] ?? 'Страница' ?></div>
                            <div class="name"><?= $obj->url ?></div>
                            <?php if (($config['list']['title']['enabled'] ?? false) && ($config['fields']['title']['enabled'] ?? false) && !empty($obj->title)): ?>
                                <div class="comment"><?= mb_substr($obj->title, 0, 100) . (mb_strlen($obj->title) > 100 ? '...' : '') ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['title']['enabled'] ?? false) && ($config['fields']['title']['enabled'] ?? false)): ?>
                        <div class="pole category">
                            <div class="title"><?= $config['list']['title']['title'] ?? 'Title' ?></div>
                            <?= !empty($obj->title) ? mb_substr($obj->title, 0, 50) . (mb_strlen($obj->title) > 50 ? '...' : '') : '' ?>
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