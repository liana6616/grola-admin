<?php
// Загружаем конфигурацию для шаблонов параметров
$config = require_once ROOT . '/config/modules/params_templates.php';

use app\Models\ParamsTemplates;
use app\Models\ParamsGroups;
use app\Models\ParamsGroupsItems;
use app\Models\Params;
use app\Models\Directories;
use app\Helpers;
use app\Pagination;
use app\FileUpload;
use app\Form;

$ids = isset($_GET['ids']) && is_numeric($_GET['ids']) ? (int)$_GET['ids'] : null;
$group_id = isset($_GET['group_id']) && is_numeric($_GET['group_id']) ? (int)$_GET['group_id'] : null;

// === УРОВЕНЬ 3: Редактирование параметра в группе ===
if (isset($_GET['addItem']) || isset($_GET['editItem']) || isset($_GET['copyItem'])) :

    $obj = new ParamsGroupsItems();
    $obj->type = 0;
    $obj->filter = 0;
    $obj->filter_rate = 0;
    $obj->rate = 0;
    $obj->show = 1;

    $title = 'Добавление параметра';
    $id = false;
    
    if(isset($_GET['editItem']) && is_numeric($_GET['editItem'])) {
        $id = (int)$_GET['editItem'];
        $title = 'Редактирование параметра';
    }
    
    if(isset($_GET['copyItem']) && is_numeric($_GET['copyItem']) && $config['items_actions']['copy']) {
        $id = (int)$_GET['copyItem'];
        $title = 'Копирование параметра';
    }

    if ($id !== false) {
        $obj = ParamsGroupsItems::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . ($ids ?? '') . "&group_id=" . ($group_id ?? ''));
            exit;
        }
    }

    if(isset($_GET['copyItem']) && $config['items_actions']['copy']) {
        $id = false;
        $obj->id = null;
    }

    $group = ParamsGroups::findById($group_id);
    if (!$group) {
        header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . ($ids ?? ''));
        exit;
    }
    
    // Получаем все доступные параметры
    $allParams = Params::where("ORDER BY name ASC");
    
    // Получаем все справочники
    $directories = Directories::where("ORDER BY name ASC");
    
    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>?ids=<?= $ids ?>&group_id=<?= $group_id ?>' 
               class='btn btn_white btn_back'>Вернуться назад</a>
        </div>
    </div>
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form paramsTemplatesItems'>
            <!-- Hidden поля для контекста -->
            <input type='hidden' name='ids' value='<?= $ids ?>'>
            <input type='hidden' name='group_id' value='<?= $group_id ?>'>
            
            <?php if (isset($_GET['copyItem']) && $config['items_actions']['copy']): ?>
                <input type="hidden" name="copy_from" value="<?= (int)$_GET['copyItem'] ?>">
            <?php endif; ?>

            <div class="flex2">
                <?php if ($config['items_fields']['show']['enabled'] ?? false): ?>
                    <?= Form::checkbox('show', (bool)$obj->show, $config['items_fields']['show']['title'] ?? 'Показывать', 1, null) ?>
                <?php endif; ?>
                
                <?php if ($config['items_fields']['rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['items_fields']['rate']['title'] ?? 'Сортировка в карточке', 'rate', $obj->rate, 0, 'number', '', '') ?>
                <?php endif; ?>

                <?php if ($config['items_fields']['filter_rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['items_fields']['filter_rate']['title'] ?? 'Сортировка в фильтре', 'filter_rate', $obj->filter_rate, 0, 'number', '', '') ?>
                <?php endif; ?>
            </div>

            <?php if ($config['items_fields']['param_id']['enabled'] ?? false): ?>
                <?= Form::select(
                    $config['items_fields']['param_id']['title'] ?? 'Параметр', 
                    'param_id', 
                    $allParams, 
                    $obj->param_id, 
                    false, 
                    'Выберите параметр', 
                    'name', 
                    0, 
                    '', 
                    0, 
                    ''
                ) ?>
            <?php endif; ?>

            <?php if ($config['items_fields']['type']['enabled'] ?? false): ?>
                <?= Form::select(
                    $config['items_fields']['type']['title'] ?? 'Тип значения', 
                    'type', 
                    [
                        0 => 'Текст',
                        1 => 'Справочник'
                    ], 
                    $obj->type, 
                    false, 
                    'Выберите тип', 
                    '', 
                    2, 
                    'type-select', 
                    0, 
                    ''
                ) ?>
            <?php endif; ?>

            <div class="input_block" id="directory-field" style="<?= $obj->type == 1 ? '' : 'display: none;' ?>">
                <?php if ($config['items_fields']['directory_id']['enabled'] ?? false): ?>
                    <?= Form::select(
                        $config['items_fields']['directory_id']['title'] ?? 'Справочник', 
                        'directory_id', 
                        $directories, 
                        $obj->directory_id, 
                        false, 
                        'Выберите справочник', 
                        'name', 
                        0, 
                        '', 
                        0, 
                        ''
                    ) ?>
                <?php endif; ?>
            </div>

            <?php if ($config['items_fields']['filter']['enabled'] ?? false): ?>
                <?= Form::select(
                    $config['items_fields']['filter']['title'] ?? 'Тип фильтра', 
                    'filter', 
                    [
                        0 => 'Нет',
                        1 => 'Список чекбоксов',
                        2 => 'Выпадающий список',
                        3 => 'Диапазон значений'
                    ], 
                    $obj->filter, 
                    false, 
                    'Выберите тип фильтра', 
                    '', 
                    2, 
                    '', 
                    0, 
                    ''
                ) ?>
            <?php endif; ?>

            <!-- Кнопка сохранения для уровня 3 -->
            <?= Form::submit($id, $obj->id, 'Сохранить', $ids . '_' . $group_id) ?>

        </form>
    </div>

<?php 
// Сохранение параметра в группе (Уровень 3)
elseif (isset($_POST['addItem']) || isset($_POST['editItem'])) :

    $id = isset($_POST['editItem']) && is_numeric($_POST['editItem']) ? (int)$_POST['editItem'] : 0;
    $ids = isset($_POST['ids']) && is_numeric($_POST['ids']) ? (int)$_POST['ids'] : null;
    $group_id = isset($_POST['group_id']) && is_numeric($_POST['group_id']) ? (int)$_POST['group_id'] : null;
    
    if (isset($_POST['editItem']) && $id > 0) {
        $obj = ParamsGroupsItems::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . $ids . "&group_id=" . $group_id);
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        $obj = new ParamsGroupsItems();
        
        if (isset($_POST['copy_from']) && is_numeric($_POST['copy_from']) && $config['items_actions']['copy']) {
            $_SESSION['notice'] = 'Скопировано';
        } else {
            $_SESSION['notice'] = 'Добавлено';
        }
    }

    // Заполняем данные из формы
    $obj->group_id = $group_id;
    
    if ($config['items_fields']['param_id']['enabled'] ?? false) {
        $obj->param_id = isset($_POST['param_id']) && is_numeric($_POST['param_id']) ? (int)$_POST['param_id'] : 0;
    }
    
    if ($config['items_fields']['type']['enabled'] ?? false) {
        $obj->type = isset($_POST['type']) && is_numeric($_POST['type']) ? (int)$_POST['type'] : 0;
    }
    
    if ($config['items_fields']['directory_id']['enabled'] ?? false) {
        $obj->directory_id = $obj->type == 1 && isset($_POST['directory_id']) && is_numeric($_POST['directory_id']) ? (int)$_POST['directory_id'] : null;
    }
    
    if ($config['items_fields']['filter']['enabled'] ?? false) {
        $obj->filter = isset($_POST['filter']) && is_numeric($_POST['filter']) ? (int)$_POST['filter'] : 0;
    }
    
    if ($config['items_fields']['filter_rate']['enabled'] ?? false) {
        $obj->filter_rate = isset($_POST['filter_rate']) && is_numeric($_POST['filter_rate']) ? (int)$_POST['filter_rate'] : 0;
    }
    
    if ($config['items_fields']['rate']['enabled'] ?? false) {
        $obj->rate = isset($_POST['rate']) && is_numeric($_POST['rate']) ? (int)$_POST['rate'] : 0;
    }
    
    if ($config['items_fields']['show']['enabled'] ?? false) {
        $obj->show = isset($_POST['show']) ? 1 : 0;
    }
    
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    // Правильный редирект с ids и group_id
    header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . $ids . "&group_id=" . $group_id . "&editItem=" . $obj->id);
    exit;

// Удаление параметра из группы (Уровень 3)
elseif (isset($_GET['deleteItem'])) :

    $id = isset($_GET['deleteItem']) && is_numeric($_GET['deleteItem']) ? (int)$_GET['deleteItem'] : 0;
    
    if ($id > 0) {
        $obj = ParamsGroupsItems::findById($id);
        
        if ($obj) {
            $obj->delete();
            $_SESSION['notice'] = 'Удалено';
        }
    }

    header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . ($ids ?? '') . "&group_id=" . ($group_id ?? ''));
    exit;

// === УРОВЕНЬ 2: Редактирование группы в шаблоне ===
elseif (isset($_GET['editGroup']) || isset($_GET['addGroup']) || isset($_GET['copyGroup'])) :

    $obj = new ParamsGroups();
    $obj->show = 1;
    $obj->rate = 0;

    $title = 'Добавление группы';
    $id = false;
    
    if(isset($_GET['editGroup']) && is_numeric($_GET['editGroup'])) {
        $id = (int)$_GET['editGroup'];
        $title = 'Редактирование группы';
    }
    
    if(isset($_GET['copyGroup']) && is_numeric($_GET['copyGroup']) && $config['groups_actions']['copy']) {
        $id = (int)$_GET['copyGroup'];
        $title = 'Копирование группы';
    }

    if ($id !== false) {
        $obj = ParamsGroups::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . ($ids ?? ''));
            exit;
        }
    }

    if(isset($_GET['copyGroup']) && $config['groups_actions']['copy']) {
        $id = false;
        $obj->id = null;
    }

    $template = ParamsTemplates::findById($ids);
    if (!$template) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>?ids=<?= $ids ?>' class='btn btn_white btn_back'>Вернуться назад</a>
        </div>
    </div>
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form'>
            <!-- Hidden поле для контекста -->
            <input type='hidden' name='ids' value='<?= $ids ?>'>
            
            <?php if (isset($_GET['copyGroup']) && $config['groups_actions']['copy']): ?>
                <input type="hidden" name="copy_from" value="<?= (int)$_GET['copyGroup'] ?>">
            <?php endif; ?>

            <div class="flex2">
                <?php if ($config['groups_fields']['show']['enabled'] ?? false): ?>
                    <?= Form::checkbox('show', (bool)$obj->show, $config['groups_fields']['show']['title'] ?? 'Показывать', 1, null) ?>
                <?php endif; ?>
                
                <?php if ($config['groups_fields']['rate']['enabled'] ?? false): ?>
                    <?= Form::input($config['groups_fields']['rate']['title'] ?? 'Рейтинг для сортировки', 'rate', $obj->rate, 0, 'number', '', '') ?>
                <?php endif; ?>
            </div>

            <?php if ($config['groups_fields']['name']['enabled'] ?? false): ?>
                <?= Form::input(
                    $config['groups_fields']['name']['title'] ?? 'Название группы', 
                    'name', 
                    $obj->name, 
                    1, 
                    '', 
                    '', 
                    'placeholder="' . ($config['groups_fields']['name']['placeholder'] ?? '') . '"'
                ) ?>
            <?php endif; ?>

            <!-- Кнопка сохранения для уровня 2 -->
            <?= Form::submit($id, $obj->id, 'Сохранить', $ids) ?>

        </form>
    </div>

<?php
// Сохранение группы (Уровень 2)
elseif (isset($_POST['addGroup']) || isset($_POST['editGroup'])) :

    $id = isset($_POST['editGroup']) && is_numeric($_POST['editGroup']) ? (int)$_POST['editGroup'] : 0;
    $ids = isset($_POST['ids']) && is_numeric($_POST['ids']) ? (int)$_POST['ids'] : null;
    
    if (isset($_POST['editGroup']) && $id > 0) {
        $obj = ParamsGroups::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . $ids);
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        $obj = new ParamsGroups();
        
        if (isset($_POST['copy_from']) && is_numeric($_POST['copy_from']) && $config['groups_actions']['copy']) {
            $_SESSION['notice'] = 'Скопировано';
        } else {
            $_SESSION['notice'] = 'Добавлено';
        }
    }

    $obj->template_id = $ids;
    
    if ($config['groups_fields']['name']['enabled'] ?? false) {
        $obj->name = trim($_POST['name'] ?? '');
    }
    
    if ($config['groups_fields']['rate']['enabled'] ?? false) {
        $obj->rate = isset($_POST['rate']) && is_numeric($_POST['rate']) ? (int)$_POST['rate'] : 0;
    }
    
    if ($config['groups_fields']['show']['enabled'] ?? false) {
        $obj->show = isset($_POST['show']) ? 1 : 0;
    }
    
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    // Правильный редирект с ids
    header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . $ids . "&editGroup=" . $obj->id);
    exit;

// Удаление группы (Уровень 2)
elseif (isset($_GET['deleteGroup'])) :

    $id = isset($_GET['deleteGroup']) && is_numeric($_GET['deleteGroup']) ? (int)$_GET['deleteGroup'] : 0;
    
    if ($id > 0) {
        $obj = ParamsGroups::findById($id);
        
        if ($obj) {
            // Удаляем все параметры группы
            $items = ParamsGroupsItems::findWhere("WHERE group_id = " . $id);
            if (!empty($items)) {
                foreach ($items as $item) {
                    $item->delete();
                }
            }
            
            $obj->delete();
            $_SESSION['notice'] = 'Удалено';
        }
    }

    header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . $ids);
    exit;

// === УРОВЕНЬ 1: Редактирование шаблона ===
elseif (isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy'])) :

    $obj = new ParamsTemplates();
    
    $title = 'Добавление шаблона';
    $id = false;
    
    if(isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        $title = 'Редактирование шаблона';
    }
    
    if(isset($_GET['copy']) && is_numeric($_GET['copy']) && $config['actions']['copy']) {
        $id = (int)$_GET['copy'];
        $title = 'Копирование шаблона';
    }

    if ($id !== false) {
        $obj = ParamsTemplates::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
    }

    ?>
    <div class="editHead">
        <h1><?= $title ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться к списку</a>
        </div>
    </div>
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form'>
            
            <?php if (isset($_GET['copy']) && $config['actions']['copy']): ?>
                <input type="hidden" name="copy_from" value="<?= (int)$_GET['copy'] ?>">
            <?php endif; ?>

            <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                <?= Form::input(
                    $config['fields']['name']['title'] ?? 'Название шаблона', 
                    'name', 
                    $obj->name, 
                    1, 
                    '', 
                    '', 
                    'placeholder="' . ($config['fields']['name']['placeholder'] ?? '') . '"'
                ) ?>
            <?php endif; ?>

            <!-- Кнопка сохранения для уровня 1 -->
            <?= Form::submit($id, $obj->id, 'Сохранить') ?>

        </form>
    </div>

<?php
// Сохранение шаблона (Уровень 1)
elseif (isset($_POST['add']) || isset($_POST['edit'])) :

    $id = isset($_POST['edit']) && is_numeric($_POST['edit']) ? (int)$_POST['edit'] : 0;
    
    if (isset($_POST['edit']) && $id > 0) {
        $obj = ParamsTemplates::findById($id);
        if (!$obj) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        $_SESSION['notice'] = 'Изменено';
    } else {
        $obj = new ParamsTemplates();
        
        if (isset($_POST['copy_from']) && is_numeric($_POST['copy_from']) && $config['actions']['copy']) {
            $_SESSION['notice'] = 'Скопировано';
        } else {
            $_SESSION['notice'] = 'Добавлено';
        }
    }

    if ($config['fields']['name']['enabled'] ?? false) {
        $obj->name = trim($_POST['name'] ?? '');
    }
    
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    header("Location: {$_SERVER['REDIRECT_URL']}?edit={$obj->id}");
    exit;

// Удаление шаблона (Уровень 1)
elseif (isset($_GET['delete'])) :

    $id = isset($_GET['delete']) && is_numeric($_GET['delete']) ? (int)$_GET['delete'] : 0;
    
    if ($id > 0) {
        $obj = ParamsTemplates::findById($id);
        
        if ($obj) {
            // Получаем все группы шаблона
            $groups = ParamsGroups::findWhere("WHERE template_id = " . $id);
            if (!empty($groups)) {
                foreach ($groups as $group) {
                    // Удаляем параметры группы
                    $items = ParamsGroupsItems::findWhere("WHERE group_id = " . $group->id);
                    if (!empty($items)) {
                        foreach ($items as $item) {
                            $item->delete();
                        }
                    }
                    $group->delete();
                }
            }
            
            $obj->delete();
            $_SESSION['notice'] = 'Удалено';
        }
    }

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;

// === ОСНОВНОЙ РЕЖИМ - ОТОБРАЖЕНИЕ СПИСКОВ ===
else :
    $title = 'Шаблоны параметров товаров';
    $add = 'шаблон параметров';

    // Уровень 3: Параметры в группе
    if (!empty($ids) && !empty($group_id)):
        $template = ParamsTemplates::findById($ids);
        $group = ParamsGroups::findById($group_id);
        
        if (!$template || !$group) {
            header("Location: {$_SERVER['REDIRECT_URL']}?ids=" . ($ids ?? ''));
            exit;
        }
        
        $title = 'Шаблон: ' . $template->name . ' → Группа: ' . $group->name;
        $back = true;
        $add = 'параметр'; // Для head.php

        // Пагинация для параметров
        $where = "WHERE group_id = " . $group->id;
        
        $search = trim($_GET['search'] ?? '');
        if (!empty($search) && ($config['items_filters']['search'] ?? false)) {
            // Ищем по названию параметра через JOIN
            $where .= " AND param_id IN (SELECT id FROM params WHERE name LIKE '%{$search}%')";
        }
        
        $perPage = $_SESSION['paramsgroupsitems']['per_page'] ?? ($config['items_pagination']['default_per_page'] ?? 50);
        $order_by = $config['items_pagination']['order_by'] ?? 'ORDER BY rate ASC, id ASC';
        
        $additionalParams = ['ids' => $ids, 'group_id' => $group_id];

        $result = Pagination::create(
            modelClass: ParamsGroupsItems::class,
            where: $where,
            order: $order_by,
            defaultItemsPerPage: $perPage,
            additionalParams: $additionalParams
        );
        
        $objs = $result['items'];
        $pagination = $result['pagination'];
        $totalCount = $result['totalCount'];

        include ROOT . '/private/views/components/head.php'; 
        
        // Получаем все параметры для отображения названий
        $allParams = [];
        $paramsList = Params::findAll();
        foreach ($paramsList as $param) {
            $allParams[$param->id] = $param->name;
        }
        
        // Получаем все справочники
        $allDirectories = [];
        $directoriesList = Directories::findAll();
        foreach ($directoriesList as $dir) {
            $allDirectories[$dir->id] = $dir->name;
        }
        
        $filterTypes = [
            0 => 'Нет',
            1 => 'Список чекбоксов',
            2 => 'Выпадающий список',
            3 => 'Диапазон значений'
        ];
        
        $valueTypes = [
            0 => 'Текст',
            1 => 'Справочник'
        ];
        ?>
        
        <div class="table_container">
            <div class="table_header">
                <?php if ($config['items_list']['handler'] ?? false): ?>
                    <div class="handler_block"></div>
                <?php endif; ?>
                
                <?php if ($config['items_list']['info']['enabled'] ?? false): ?>
                    <div class="info"><?= $config['items_list']['info']['title'] ?? 'Параметр' ?></div>
                <?php endif; ?>
                
                <?php if ($config['items_list']['edit_date']['enabled'] ?? false): ?>
                    <div class="modified_date"><?= $config['items_list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body<?= ($totalCount <= $perPage && empty($search) && ($config['items_list']['handler'] ?? false)) ? ' sortbox-items' : '' ?>">
            <?php if (!empty($objs)): 
                foreach ($objs as $obj): 
                    $paramName = $allParams[$obj->param_id] ?? 'Неизвестный параметр';
                    $directoryName = $obj->directory_id ? ($allDirectories[$obj->directory_id] ?? '') : '';
                ?>
                    <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                        <?php if ($config['items_list']['handler'] ?? false): ?>
                            <div class="handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($search)) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                        <?php endif; ?>
                        
                        <?php if ($config['items_list']['info']['enabled'] ?? false): ?>
                            <div class="info">
                                <div class="name"><?= $paramName ?></div>
                                <div class="comment">
                                    <small>
                                        Тип: <?= $valueTypes[$obj->type] ?? 'Неизвестно' ?>
                                        <?php if ($obj->type == 1 && $directoryName): ?>
                                            (Справочник: <?= $directoryName ?>)
                                        <?php endif; ?>
                                        | Фильтр: <?= $filterTypes[$obj->filter] ?? 'Нет' ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($config['items_list']['edit_date']['enabled'] ?? false): ?>
                            <div class="modified_date">
                                <?= $obj->edit_date ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php include ROOT.'/private/views/components/actions.php' ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="table_empty">
                    <div class="not_found">Параметров не найдено</div>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <?= !empty($pagination) ? $pagination : '' ?>

    <?php
    // Уровень 2: Группы в шаблоне
    elseif (!empty($ids)):
        $template = ParamsTemplates::findById($ids);
        
        if (!$template) {
            header("Location: {$_SERVER['REDIRECT_URL']}");
            exit;
        }
        
        $title = 'Шаблон: ' . $template->name;
        $back = true;
        $add = 'группу'; // Для head.php

        // Пагинация для групп
        $where = "WHERE template_id = " . $template->id;
        
        $search = trim($_GET['search'] ?? '');
        if (!empty($search) && ($config['groups_filters']['search'] ?? false)) {
            $where .= " AND (`name` like '%{$search}%')";
        }
        
        $perPage = $_SESSION['paramsgroups']['per_page'] ?? ($config['groups_pagination']['default_per_page'] ?? 50);
        $order_by = $config['groups_pagination']['order_by'] ?? 'ORDER BY rate ASC, id ASC';
        
        $additionalParams = ['ids' => $ids];

        $result = Pagination::create(
            modelClass: ParamsGroups::class,
            where: $where,
            order: $order_by,
            defaultItemsPerPage: $perPage,
            additionalParams: $additionalParams
        );
        
        $objs = $result['items'];
        $pagination = $result['pagination'];
        $totalCount = $result['totalCount'];

        include ROOT . '/private/views/components/head.php'; 
        ?>
        
        <div class="table_container">
            <div class="table_header">
                <?php if ($config['groups_list']['handler'] ?? false): ?>
                    <div class="handler_block"></div>
                <?php endif; ?>
                
                <?php if ($config['groups_list']['info']['enabled'] ?? false): ?>
                    <div class="info"><?= $config['groups_list']['info']['title'] ?? 'Название группы' ?></div>
                <?php endif; ?>
                
                <?php if ($config['groups_list']['edit_date']['enabled'] ?? false): ?>
                    <div class="modified_date"><?= $config['groups_list']['edit_date']['title'] ?? 'Изменение' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body<?= ($totalCount <= $perPage && empty($search) && ($config['groups_list']['handler'] ?? false)) ? ' sortbox-items' : '' ?>">
            <?php if (!empty($objs)): 
                foreach ($objs as $obj): 
                    // Получаем количество параметров в группе
                    $count = ParamsGroupsItems::count("WHERE group_id = " . $obj->id);
                ?>
                    <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                        <?php if ($config['groups_list']['handler'] ?? false): ?>
                            <div class="handler tooltip-trigger" data-tooltip="<?= ($totalCount > $perPage || !empty($search)) ? 'Перетаскивание для сортировки включается когда все записи выведены на одной странице и не применены фильтры и поиск' : 'Перетащите для сортировки' ?>"></div>
                        <?php endif; ?>
                        
                        <?php if ($config['groups_list']['info']['enabled'] ?? false): ?>
                            <div class="info">
                                <div class="name">
                                    <a href='?ids=<?= $ids ?>&group_id=<?= $obj->id ?>'>
                                        <?= $obj->name ?>
                                    </a>
                                </div>
                                <div class="comment">Параметров: <?= $count ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($config['groups_list']['edit_date']['enabled'] ?? false): ?>
                            <div class="modified_date">
                                <?= $obj->edit_date ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php include ROOT.'/private/views/components/actions.php' ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="table_empty">
                    <div class="not_found">Групп не найдено</div>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <?= !empty($pagination) ? $pagination : '' ?>

    <?php
    // Уровень 1: Список шаблонов
    else:
        include ROOT . '/private/views/components/head.php';

        $whereConditions = [];
        
        $search = trim($_GET['search'] ?? '');
        if (!empty($search) && $config['filters']['search']) {
            if ($config['fields']['name']['enabled'] ?? false) {
                $whereConditions[] = "`name` like '%{$search}%'";
            }
        }

        $where = '';
        if (!empty($whereConditions)) {
            $where = 'WHERE ' . implode(' AND ', $whereConditions);
        }

        $perPage = $_SESSION['paramstemplates']['per_page'] ?? $config['pagination']['default_per_page'];
        $order_by = $config['pagination']['order_by'] ?? 'ORDER BY name ASC, id ASC';

        $result = Pagination::create(
            modelClass: ParamsTemplates::class,
            where: $where,
            order: $order_by,
            defaultItemsPerPage: $perPage
        );
        
        $objs = $result['items'];
        $pagination = $result['pagination'];
        $totalCount = $result['totalCount'];

        if (!empty($objs)): ?>
            <div class="table_container">
                <div class="table_header">
                    <?php if ($config['list']['info']['enabled'] ?? false): ?>
                        <div class="info"><?= $config['list']['info']['title'] ?? 'Название шаблона' ?></div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                        <div class="modified_date"><?= $config['list']['edit_date']['title'] ?? 'Изменение' ?></div>
                    <?php endif; ?>
                    
                    <div class="actions"></div>
                </div>
                <div class="table_body">
                <?php foreach ($objs as $obj): 
                    // Получаем количество групп в шаблоне
                    $count = ParamsGroups::count("WHERE template_id = " . $obj->id);
                ?>
                    <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                        <?php if ($config['list']['info']['enabled'] ?? false): ?>
                            <div class="info">
                                <div class="name">
                                    <a href='?ids=<?= $obj->id ?>'>
                                        <?= $obj->name ?>
                                    </a>
                                </div>
                                <div class="comment">Групп: <?= $count ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($config['list']['edit_date']['enabled'] ?? false): ?>
                            <div class="modified_date">
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
        <?php endif;
    endif;
endif;
?>