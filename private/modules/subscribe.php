<?php
// Загружаем конфигурацию для подписчиков
$config = require_once ROOT . '/config/modules/subscribe.php';

use app\Models\Subscribe;
use app\Models\Admins;
use app\Helpers;
use app\Pagination;
use app\Form;

if (isset($_GET['edit']) || isset($_GET['view'])) :

    $id = $_GET['edit'] ?? $_GET['view'] ?? false;
    if ($id) $obj = Subscribe::findById($id);

    $title = 'Просмотр подписчика';
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
                <?= Form::label('Дата подписки', !empty($obj->date) ? date('d.m.Y H:i', $obj->date) : '', '') ?>
                <?= Form::label('ID', $obj->id, '') ?>
                <?php 
                    $status_label = $obj->active == 1 
                        ? '<span style="color: #14AE5C;">Активен</span>' 
                        : '<span style="color: #E7411D;">Неактивен</span>';
                    echo Form::label('Статус', $status_label, '');
                ?>
            </div>
            
            <div class="flex2">
                <?= Form::label('Email', $obj->email ?? '—', '') ?>
                <?= Form::label('IP-адрес', $obj->ip ?? '—', '') ?>
            </div>

            <div class="flex2">
                <?= Form::label('Пользователь', !empty($obj->user_id) ? "ID: {$obj->user_id}" : 'Не авторизован', '') ?>
            </div>

            <div class="submit_block">
                <a href='<?= $_SERVER['REDIRECT_URL'] ?>' class='btn btn_white btn_back'>Вернуться назад</a>
            </div>
        </div>
    </div>
<?php
elseif (isset($_GET['delete'])) :

    $obj = Subscribe::findById($_GET['delete']);
    $obj->delete();

    $_SESSION['notice'] = 'Удалено';

    header("Location: {$_SERVER['REDIRECT_URL']}");
    exit;
else :
    $title = 'Подписчики';
    $add = 'подписчика';

    // Формируем условия WHERE
    $whereConditions = [];
    
    $search = trim($_GET['search'] ?? '');
    if (!empty($search) && ($config['filters']['search'] ?? false)) {
        $searchFields = [];
        
        if (($config['fields']['email']['enabled'] ?? false)) {
            $searchFields[] = "`email` like '%{$search}%'";
        }
        if (($config['fields']['ip']['enabled'] ?? false)) {
            $searchFields[] = "`ip` like '%{$search}%'";
        }
        
        if (!empty($searchFields)) {
            $whereConditions[] = "(" . implode(' OR ', $searchFields) . ")";
        }
    }

    // Фильтр по активности
    $active = trim($_GET['active'] ?? '');
    if ($active !== '' && ($config['filters']['active'] ?? false)) {
        if ($active === '1') {
            $whereConditions[] = "`active` = 1";
        } elseif ($active === '0') {
            $whereConditions[] = "`active` = 0";
        }
    }

    // Формируем полное WHERE условие
    $where = '';
    if (!empty($whereConditions)) {
        $where = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    $perPage = $_SESSION['subscribe']['per_page'] ?? ($config['pagination']['default_per_page'] ?? 20);
    $order_by = $config['pagination']['order_by'] ?? 'ORDER BY date DESC, id DESC';

    // Вызов пагинации
    $result = Pagination::create(
        modelClass: Subscribe::class,
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
                <?php if (($config['list']['email']['enabled'] ?? false) && ($config['fields']['email']['enabled'] ?? false)): ?>
                    <div class="info"><?= $config['list']['email']['title'] ?? 'Email' ?></div>
                <?php endif; ?>
                
                <?php if (($config['list']['user_id']['enabled'] ?? false) && ($config['fields']['user_id']['enabled'] ?? false)): ?>
                    <div class="category"><?= $config['list']['user_id']['title'] ?? 'Пользователь' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['date']['enabled'] ?? false): ?>
                    <div class="modified_date"><?= $config['list']['date']['title'] ?? 'Дата подписки' ?></div>
                <?php endif; ?>
                
                <?php if ($config['list']['active']['enabled'] ?? false): ?>
                    <div class="category"><?= $config['list']['active']['title'] ?? 'Статус' ?></div>
                <?php endif; ?>
                
                <div class="actions"></div>
            </div>
            <div class="table_body">
            <?php foreach ($objs as $obj): ?>
                <div class="table_row" data-id="<?= $obj->id ?>" data-class="<?= get_class($obj) ?>">
                    <?php if (($config['list']['email']['enabled'] ?? false) && ($config['fields']['email']['enabled'] ?? false)): ?>
                        <div class="info">
                            <div class="name"><?= $obj->email ?></div>
                            <?php if (($config['fields']['ip']['enabled'] ?? false) && !empty($obj->ip)): ?>
                                <div class="comment">
                                    IP: <?= $obj->ip ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($config['list']['user_id']['enabled'] ?? false) && ($config['fields']['user_id']['enabled'] ?? false)): ?>
                        <div class="category">
                            <?= !empty($obj->user_id) ? "ID: {$obj->user_id}" : 'Не авторизован' ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['date']['enabled'] ?? false): ?>
                        <div class="modified_date">
                            <?= !empty($obj->date) ? date('d.m.Y H:i', strtotime($obj->date)) : '—' ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($config['list']['active']['enabled'] ?? false): ?>
                        <div class="category">
                            <?php if ($obj->active == 1): ?>
                                <span class="status_active">Активен</span>
                            <?php else: ?>
                                <span class="status_inactive">Неактивен</span>
                            <?php endif; ?>
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