<?php
// Загружаем конфигурацию для заявок
$config = require_once ROOT . '/config/modules/forms.php';

use app\Models\Forms;
use app\Models\FormsType;
use app\Models\Admins;
use app\Helpers;
use app\Pagination;
use app\Form;

if (isset($_GET['edit']) || isset($_GET['view'])) :

    $id = $_GET['edit'] ?? $_GET['view'] ?? false;
    if ($id) $obj = Forms::findById($id);

    $title = 'Просмотр заявки';
    ?>
    <div class="editHead">
        <h1><?= $title ?> #<?= $obj->id ?></h1>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться назад</a>
        </div>
    </div>
    <div class="edit_block">
        <div class="edit_form">
            <div class="flex3">
                <?= Form::label('Дата заявки', !empty($obj->date) ? date('d.m.Y H:i', $obj->date) : '', '') ?>
                <?= Form::label('ID', $obj->id, '') ?>
                <?php 
                    $status_label = match($obj->status) {
                        0 => '<span style="color: #67748E;">Новая</span>',
                        1 => '<span style="color: #14AE5C;">Обработана</span>',
                        2 => '<span style="color: #E7411D;">Отклонена</span>',
                        default => '<span style="color: #67748E;">Новая</span>'
                    };
                    echo Form::label('Статус', $status_label, '');
                ?>
            </div>
            
            <div class="flex2">
                <?= Form::label('Тип заявки', FormsType::findById($obj->type_id)->name ?? '—', '') ?>
                <?= Form::label('IP-адрес', $obj->ip ?? '—', '') ?>
            </div>

            <div class="flex2">
                <?= Form::label('ФИО', $obj->name ?? '—', '') ?>
                <?= Form::label('Телефон', $obj->phone ?? '—', '') ?>
            </div>

            <div class="flex2">
                <?= Form::label('Email', $obj->email ?? '—', '') ?>
                <?= Form::label('Пользователь', !empty($obj->user_id) ? "ID: {$obj->user_id}" : 'Не авторизован', '') ?>
            </div>

            <?= Form::label('Сообщение', nl2br(htmlspecialchars($obj->text ?? '')), '') ?>
            
            <?php if (!empty($obj->link)): ?>
                <?= Form::label('Ссылка на страницу', '<a href="' . $obj->link . '" target="_blank" rel="noopener noreferrer">' . $obj->link . '</a>', '') ?>
            <?php endif; ?>

            <div class="submit_block">
                <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться назад</a>
            </div>
        </div>
    </div>
<?php
elseif (isset($_GET['delete'])) :

    $obj = Forms::findById($_GET['delete']);
    $obj->delete();

    $_SESSION['notice'] = 'Удалено';

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;
else :
    $title = 'Заявки';
    $add = 'заявку';

    $filter = true;

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && $config['filters']['search']) {
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
        
        if (!empty($searchFields)) {
            $whereConditions[] = "(" . implode(' OR ', $searchFields) . ")";
        }
    }

    $type_id = trim($_GET['type_id'] ?? '');
    if (!empty($type_id) && $type_id !== 'all' && $config['filters']['type']) {
        $whereConditions[] = "`type_id` = '{$type_id}'";
    }

    // Формируем полное WHERE условие
    $where = '';
    if (!empty($whereConditions)) {
        $where = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    $perPage = $_SESSION['forms']['per_page'] ?? $config['pagination']['default_per_page'];
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
                
                <?php if (($config['list']['text']['enabled'] ?? false) && ($config['fields']['text']['enabled'] ?? false)): ?>
                    <div class="description"><?= $config['list']['text']['title'] ?? 'Сообщение' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['date']['enabled'] ?? false): ?>
                    <div class="modified_date"><?= $config['list']['date']['title'] ?? 'Дата' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body">
            <?php foreach ($objs as $obj): 
                $form_type = FormsType::findById($obj->type_id);
                $is_unread = empty($obj->read);
            ?>
                <div class="table_row<?= $is_unread ? ' unread' : '' ?>" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if (($config['list']['name']['enabled'] ?? false) && ($config['fields']['name']['enabled'] ?? false)): ?>
                        <div class="info">
                            <div class="name"><?= $obj->name ?></div>
                            <div class="comment">
                                <?php if (($config['fields']['phone']['enabled'] ?? false) && !empty($obj->phone)): ?>
                                    тел.: <?= $obj->phone ?><br>
                                <?php endif; ?>
                                <?php if (($config['fields']['email']['enabled'] ?? false) && !empty($obj->email)): ?>
                                    email: <?= $obj->email ?>
                                <?php endif; ?>
                            </div>
                            <div class="comment breadcrumbs">
                                <span class="path_arr">ID:</span> 
                                <span class="path"><?= $obj->id ?></span>
                                <span class="path_arr">|</span>
                                <span class="path_arr">IP:</span> 
                                <span class="path"><?= $obj->ip ?></span>
                                <?php if (($config['fields']['user_id']['enabled'] ?? false) && !empty($obj->user_id)): ?>
                                    <span class="path_arr">|</span>
                                    <span class="path_arr">Пользователь:</span> 
                                    <span class="path">ID <?= $obj->user_id ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['type']['enabled'] ?? false) && ($config['fields']['type_id']['enabled'] ?? false)): ?>
                        <div class="category">
                            <?= $form_type->name ?? '—' ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['text']['enabled'] ?? false) && ($config['fields']['text']['enabled'] ?? false)): ?>
                        <div class="description">
                            <?= nl2br(htmlspecialchars(mb_substr($obj->text ?? '', 0, 150))) . (mb_strlen($obj->text ?? '') > 150 ? '...' : '') ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['date']['enabled'] ?? false): ?>
                        <div class="modified_date">
                            <?= !empty($obj->date) ? date('d.m.Y H:i', $obj->date) : '—' ?>
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