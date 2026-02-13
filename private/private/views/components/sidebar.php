<aside class="sidebar">
    <div class="user_item">
        <div class="user_avatar">
            <? if(!empty($_SESSION['admin']['image'])): ?>
                <img src="<?= $_SESSION['admin']['image'] ?>" alt="">
            <? else: ?>
                <img src="/private/src/images/no_admin.png" alt="">
            <? endif; ?>
        </div>
        <div class="user_info">
            <div class="user_name"><?= $_SESSION['admin']['name'] ?></div>
            <div class="user_role"><?= $_SESSION['admin']['className'] ?></div>
            <div class="user_ip"><?= $_SERVER['REMOTE_ADDR'] ?></div>
        </div>
    </div>
    
    <div class="nav_menu">
        <?php 
        $URI = strtok($_SERVER["REQUEST_URI"], '?');
        $adminClass = $_SESSION['admin']['class'] ?? 0;
        $menuConfig = \app\Models\Admins::getMenu(); 
        
        // Фильтруем меню по классу администратора
        $filteredMenu = \app\Models\Admins::filterMenuByClass($menuConfig['menu'], $adminClass);
        
        foreach ($filteredMenu as $file => $item):
            $num = '';
            switch ($file) {
                case 'forms':
                    $forms = app\Models\Forms::findWhere("WHERE `status` = 0");
                    if (!empty($forms)) {
                        $num = count($forms);
                    }
                    break;
            }
            
            // Проверяем, есть ли вложенные пункты
            if (!isset($item['children'])): ?>
                <a href='/<?= ADMIN_LINK ?>/<?= $file ?>' class="nav_item<?= (strpos($URI,ADMIN_LINK.'/'.$file) != '') ? ' active' : '' ?><?= $item['icon']?' menu_icon '.$item['icon']:'' ?>">
                    <span><?= $item['title'] ?><?= !empty($num)?'<i>'.$num.'</i>':'' ?></span>
                </a>
            <?php else: 
                $f = 0;
                foreach ($item['children'] as $childFile => $childItem) :
                    if($URI == '/'.ADMIN_LINK.'/' . $childFile) { $f = 1; }
                endforeach;
                ?>
                <div class='nav_item open_block<?= ($f == 1)?' active':'' ?><?= $item['icon']?' menu_icon '.$item['icon']:'' ?>'>
                    <span><?= $item['title'] ?><?= !empty($num)?'<i>'.$num.'</i>':'' ?></span>
                    <div class='open_block_items'>
                        <?php foreach ($item['children'] as $childFile => $childItem) : ?>
                            <?php
                            $num_child = '';
                            switch ($childFile) {
                                case 'orders':
                                    $num_child = $num;
                                    break;
                            }
                            ?>
                            <a href='/<?= ADMIN_LINK ?>/<?= $childFile ?>' class="nav_item2<?= ($URI == '/'.ADMIN_LINK.'/' . $childFile) ? ' active' : '' ?><?= $childItem['icon']?' menu_icon '.$childItem['icon']:'' ?>">
                                <span><?= $childItem['title'] ?><?= !empty($num_child)?'<i>'.$num_child.'</i>':'' ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif;
        endforeach; ?>
    </div>
    <div class="visualteam logo_visualteam" data-i="<?= ADMIN_LINK ?>"></div>
</aside>