<?php
// Загружаем конфигурацию для заявок
$config = require_once ROOT . '/config/modules/forms.php';

use app\Models\Forms;
use app\Models\FormsType;
use app\Models\Admins;
use app\Helpers;
use app\Pagination;
use app\Form;

if (isset($_GET['view'])) :

    $id = $_GET['view'] ?? false;
    $obj = Forms::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    // Помечаем как прочитанное
    if (empty($obj->read)) {
        $obj->read = 1;
        $obj->save();
    }

    $title = 'Просмотр заявки';
    
    // Загружаем связанные данные
    $form_type = null;
    if (($config['fields']['type_id']['enabled'] ?? false) && !empty($obj->type_id)) {
        $form_type = FormsType::findById($obj->type_id);
    }
    
    $admin = null;
    if (!empty($obj->edit_admin_id)) {
        $admin = Admins::findById($obj->edit_admin_id);
    }
    
    // Массив статусов для select
    $status_items = [];
    foreach ($config['statuses'] as $key => $status) {
        $status_items[$key] = $status['title'];
    }
    ?>
    <div class="editHead">
        <h1><?= $title ?> #<?= $obj->id ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться назад</a>
        </div>
    </div>
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form'>

            <div class="flex3">
                <?php if ($config['fields']['date']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['date']['title'] ?? 'Дата заявки', 
                        'date', 
                        !empty($obj->date) ? date('d.m.Y H:i', $obj->date) : '', 
                        0, 
                        'text', 
                        true, 
                        ''
                    ) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['status']['enabled'] ?? false): ?>
                    <?= Form::select(
                        $config['fields']['status']['title'] ?? 'Статус заявки',
                        'status',
                        $status_items,
                        $obj->status,
                        false,
                        '',
                        '',
                        2,
                        '',
                        0,
                        ''
                    ) ?>
                <?php endif; ?>
            </div>
            
            <div class="flex2">
                <?php if ($config['fields']['type_id']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['type_id']['title'] ?? 'Тип заявки', 
                        'type_id', 
                        $form_type->name ?? '—', 
                        0, 
                        'text', 
                        true, 
                        ''
                    ) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['ip']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['ip']['title'] ?? 'IP-адрес', 
                        'ip', 
                        $obj->ip ?? '—', 
                        0, 
                        'text', 
                        true, 
                        ''
                    ) ?>
                <?php endif; ?>
            </div>

            <div class="flex2">
                <?php if ($config['fields']['name']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['name']['title'] ?? 'ФИО клиента', 
                        'name', 
                        $obj->name ?? '—', 
                        0, 
                        'text', 
                        true, 
                        ''
                    ) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['phone']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['phone']['title'] ?? 'Телефон клиента', 
                        'phone', 
                        $obj->phone ?? '—', 
                        0, 
                        'text', 
                        true, 
                        ''
                    ) ?>
                <?php endif; ?>
            </div>

            <div class="flex2">
                <?php if ($config['fields']['email']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['email']['title'] ?? 'Эл. адрес клиента', 
                        'email', 
                        $obj->email ?? '—', 
                        0, 
                        'email', 
                        true, 
                        ''
                    ) ?>
                <?php endif; ?>
                
                <?php if ($config['fields']['user_id']['enabled'] ?? false): ?>
                    <?= Form::input(
                        $config['fields']['user_id']['title'] ?? 'Пользователь', 
                        'user_id', 
                        !empty($obj->user_id) ? "ID: {$obj->user_id}" : 'Не авторизован', 
                        0, 
                        'text', 
                        true, 
                        ''
                    ) ?>
                <?php endif; ?>
            </div>

            <?php if ($config['fields']['text']['enabled'] ?? false): ?>
                <?= Form::textarea(
                    $config['fields']['text']['title'] ?? 'Сообщение от клиента', 
                    'text', 
                    $obj->text ?? '', 
                    150, 
                    true
                ) ?>
            <?php endif; ?>
            
            <?php if (($config['fields']['link']['enabled'] ?? false) && !empty($obj->link)): ?>
                <?= Form::input(
                    $config['fields']['link']['title'] ?? 'Ссылка на страницу', 
                    'link', 
                    $obj->link, 
                    0, 
                    'url', 
                    true, 
                    ''
                ) ?>
            <?php endif; ?>

            <?= Form::submit($obj->id, $obj->id, 'Сохранить', '') ?>

        </form>
    </div>
<?php
elseif (isset($_POST['edit'])) :

    $id = $_POST['edit'] ?? 0;
    $obj = Forms::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    // Меняем только статус
    if (($config['fields']['status']['enabled'] ?? false) && isset($_POST['status'])) {
        $obj->status = (int)$_POST['status'];
    }
    
    // Системные поля - всегда сохраняются
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    $_SESSION['notice'] = 'Статус обновлен';

    header("Location: {$_SERVER['REQUEST_URI']}?view=$obj->id");
    exit;
    
elseif (isset($_GET['delete'])) :

    $id = $_GET['delete'];
    $obj = Forms::findById($id);
    
    if (!$obj) {
        header("Location: {$_SERVER['REDIRECT_URL']}");
        exit;
    }
    
    $obj->delete();

    $_SESSION['notice'] = 'Удалено';

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;
    
else :
    // Заголовок модуля из конфига
    $title = $config['module']['title'] ?? 'Заявки';

    $filter = true;

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && ($config['filters']['search'] ?? false)) {
        $searchFields = [];
        
        if ($config['fields']['name']['enabled'] ?? false) {
            $searchFields[] = "`name` like '%{$search}%'";
        }
        if ($config['fields']['phone']['enabled'] ?? false) {
            $searchFields[] = "`phone` like '%{$search}%'";
        }
        if ($config['fields']['email']['enabled'] ?? false) {
            $searchFields[] = "`email` like '%{$search}%'";
        }
        if ($config['fields']['text']['enabled'] ?? false) {
            $searchFields[] = "`text` like '%{$search}%'";
        }
        if ($config['fields']['ip']['enabled'] ?? false) {
            $searchFields[] = "`ip` like '%{$search}%'";
        }
        
        if (!empty($searchFields)) {
            $whereConditions[] = "(" . implode(' OR ', $searchFields) . ")";
        }
    }

    // Фильтр по типу заявки
    $type_id = trim($_GET['type_id'] ?? '');
    if (!empty($type_id) && $type_id !== 'all' && ($config['filters']['type'] ?? false)) {
        $whereConditions[] = "`type_id` = '{$type_id}'";
    }
    
    // Фильтр по статусу
    $status = trim($_GET['status'] ?? '');
    if ($status !== '' && $status !== 'all' && ($config['filters']['status'] ?? false)) {
        $whereConditions[] = "`status` = '{$status}'";
    }

    // Формируем полное WHERE условие
    $where = '';
    if (!empty($whereConditions)) {
        $where = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    $perPage = $_SESSION['forms']['per_page'] ?? ($config['pagination']['default_per_page'] ?? 20);
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY date DESC, id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: Forms::class,
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
                <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                    <div class="info"><?= $config['list']['name']['title'] ?? 'Заявка' ?></div>
                <?php endif; ?>
                
                <?php if (($config['list']['type']['enabled'] ?? false) && ($config['fields']['type_id']['enabled'] ?? false)): ?>
                    <div class="category"><?= $config['list']['type']['title'] ?? 'Тип' ?></div>
                <?php endif; ?>
                                
                <?php if ($config['list']['date']['enabled'] ?? false): ?>
                    <div class="modified_date"><?= $config['list']['date']['title'] ?? 'Дата' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['status']['enabled'] ?? false): ?>
                    <div class="category"><?= $config['list']['status']['title'] ?? 'Статус' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body">
            <?php foreach ($objs as $obj): 
                $form_type = null;
                if (($config['fields']['type_id']['enabled'] ?? false) && !empty($obj->type_id)) {
                    $form_type = FormsType::findById($obj->type_id);
                }
                $is_unread = empty($obj->read);
                $status_title = $config['statuses'][$obj->status]['title'] ?? 'Новая';
                $status_color = $config['statuses'][$obj->status]['color'] ?? '#67748E';
            ?>
                <div class="table_row<?= $is_unread ? ' unread' : '' ?>" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                        <div class="info">
                            <div class="name"><?= htmlspecialchars($obj->name ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="comment">
                                <?php if (($config['fields']['phone']['enabled'] ?? false) && !empty($obj->phone)): ?>
                                    тел.: <?= htmlspecialchars($obj->phone, ENT_QUOTES, 'UTF-8') ?><br>
                                <?php endif; ?>
                                <?php if (($config['fields']['email']['enabled'] ?? false) && !empty($obj->email)): ?>
                                    email: <?= htmlspecialchars($obj->email, ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </div>
                            <?php if (($config['list']['text']['enabled'] ?? false) && ($config['fields']['text']['enabled'] ?? false)): ?>
                                <div class="comment">
                                    <?= nl2br(htmlspecialchars(mb_substr($obj->text ?? '', 0, 150), ENT_QUOTES, 'UTF-8')) . (mb_strlen($obj->text ?? '') > 150 ? '...' : '') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['type']['enabled'] ?? false) && ($config['fields']['type_id']['enabled'] ?? false)): ?>
                        <div class="category">
                            <?= $form_type->name ?? '—' ?>
                        </div>
                    <?php endif; ?>
                    
                            
                    
                    <?php if ($config['list']['date']['enabled'] ?? false): ?>
                        <div class="modified_date">
                            <?= !empty($obj->date) ? date('d.m.Y H:i', $obj->date) : '—' ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['status']['enabled'] ?? false): ?>
                        <div class="category">
                            <span style="color: <?= $status_color ?>;"><?= $status_title ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php 

                    include ROOT.'/private/views/components/actions.php' 
                    ?>
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