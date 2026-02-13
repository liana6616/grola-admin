<div class="pole actions">
    
    <?php if (!empty($config['actions']['open']) && $config['actions']['open'] || !empty($config['values_actions']['open']) && $config['values_actions']['open']): ?>
        <a href='<?= $pageUrl ?? '' ?>' class='action icon_open tooltip-trigger<?= empty($pageUrl)?' hide':'' ?>' data-tooltip='<?= !empty($linkTitle)?$linkTitle:'Смотреть на сайте' ?>' rel='external'></a>
    <? endif; ?>
    
    <?php if (!empty($config['actions']['show']) && $config['actions']['show'] || !empty($config['values_actions']['show']) && $config['values_actions']['show']): ?>
        <div class='action action_show icon_show<?= ($obj->show == 1) ? ' active' : '' ?> tooltip-trigger<?= get_class($obj)::hasField('show')?'':' none' ?>' data-tooltip='Показывать на сайте'  data-id='<?= $obj->id ?>' data-className='<?= get_class($obj) ?>'></div>
    <? endif; ?>
    
    <?php 
    // Определяем параметры для разных типов объектов
    $editParam = 'edit';
    $copyParam = 'copy';
    $deleteParam = 'delete';
    $urlParams = '';

    $noEdit = false;
    $noDelete = false;
    
    // Определяем класс объекта
    $className = get_class($obj);
    
    switch ($className) {
        // Для значений справочников (уровень 2 в directories)
        case 'app\Models\DirectoriesValues':
            $editParam = 'editItem';
            $copyParam = 'copyItem';
            $deleteParam = 'deleteItem';
            $urlParams = '&ids=' . ($_GET['ids'] ?? '');
            break;
            
        // Для параметров в группах (уровень 3 в params_templates)
        case 'app\Models\ParamsGroupsItems':
            $editParam = 'editItem';
            $copyParam = 'copyItem';
            $deleteParam = 'deleteItem';
            $urlParams = '&ids=' . ($_GET['ids'] ?? '') . '&group_id=' . ($_GET['group_id'] ?? '');
            break;
            
        // Для групп в шаблонах (уровень 2 в params_templates)
        case 'app\Models\ParamsGroups':
            $editParam = 'editGroup';
            $copyParam = 'copyGroup';
            $deleteParam = 'deleteGroup';
            $urlParams = '&ids=' . ($_GET['ids'] ?? '');
            break;
            
        // Для шаблонов (уровень 1 в params_templates)
        case 'app\Models\ParamsTemplates':
            // Оставляем стандартные параметры
            break;
            
        // Для справочников (уровень 1 в directories)
        case 'app\Models\Directories':
            // Оставляем стандартные параметры
            $urlParams = '';
            break;

        // Для администраторов
        case 'app\Models\Admins':
            if($is_system_user && $_SESSION['admin']['id'] != $obj->id) $noEdit = true;
            if($is_system_user) $noDelete = true;
            break;

        // Для заявок используем view вместо edit
        case 'app\Models\Forms':
            $editParam = 'view';
            break;
            
        // Для обычных объектов (старая логика)
        default:
            $urlParams = !empty($ids) ? '&ids='.$ids : '';
            $urlParams .= !empty($parent) ? '&parent='.$parent : '';
            break;
    }
    
    // Проверяем разрешение на копирование для разных типов объектов
    $canCopy = false;
    switch ($className) {
        case 'app\Models\DirectoriesValues':
            $canCopy = !empty($config['values_actions']['copy']) && $config['values_actions']['copy'] && \app\Models\Admins::canCopy();
            break;
        case 'app\Models\ParamsGroupsItems':
            $canCopy = !empty($config['items_actions']['copy']) && $config['items_actions']['copy'] && \app\Models\Admins::canCopy();
            break;
        case 'app\Models\ParamsGroups':
            $canCopy = !empty($config['groups_actions']['copy']) && $config['groups_actions']['copy'] && \app\Models\Admins::canCopy();
            break;
        case 'app\Models\ParamsTemplates':
        case 'app\Models\Directories':
            $canCopy = !empty($config['actions']['copy']) && $config['actions']['copy'] && \app\Models\Admins::canCopy();
            break;
        default:
            $canCopy = (!empty($config['actions']['copy']) && $config['actions']['copy'] || !empty($config['values_actions']['copy']) && $config['values_actions']['copy']) && \app\Models\Admins::canCopy();
            break;
    }
    ?>
    
    <?php if ($canCopy && $className !== 'app\Models\Forms'): // Заявки нельзя копировать ?>
        <a href='?<?= $copyParam ?>=<?= $obj->id ?><?= $urlParams ?>' class='action icon_copy tooltip-trigger' data-tooltip='Копировать'></a>
    <?php endif; ?>

    <?php if (\app\Models\Admins::canEdit() && !$noEdit): ?>
        <a href='?<?= $editParam ?>=<?= $obj->id ?><?= $urlParams ?>' class='action icon_edit tooltip-trigger' data-tooltip='<?= $editParam === 'view' ? 'Просмотр' : 'Редактировать' ?>'></a>
    <?php endif; ?>
    
    <?php if (\app\Models\Admins::canDelete() && !$noDelete): ?>
        <a href='?<?= $deleteParam ?>=<?= $obj->id ?><?= $urlParams ?>' class='action icon_delete tooltip-trigger' data-tooltip='Удалить'></a>
    <?php endif; ?>

</div>