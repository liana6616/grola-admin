<div class="head">
    
    <?= !empty($breadcrumbs)?$breadcrumbs:'' ?>

    <h1 class="title"><?= $title ?></h1>

    <?php if(!empty($back)): ?>
        <div class="button_block">
            <a href='<?= $_SERVER['REDIRECT_URL'] ?><?= $queryString ?? '' ?>' class='btn btn_white btn_back'>Вернуться к списку</a>
        </div>
    <?php endif; ?>
    
    <div class="search_block">
        <div class="searchs">

            <? if(!empty($totalCount)): ?>
                <div class="items_count">
                    Элементов:
                    <span><?= $totalCount ?></span>
                </div>
            <? endif; ?>

            <form id="search_form" action='/<?= URI ?>' method='get' class='search_form'>
                
                <?php 
                // Сохраняем все GET-параметры кроме 'search'
                foreach ($_GET as $key => $value) {
                    if ($key !== 'search' && $key !== 'p' && $key !== 'parent') { // Исключаем параметр пагинации
                        if (is_array($value)) {
                            foreach ($value as $arrayValue) {
                                echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($arrayValue) . '">';
                            }
                        } else {
                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                        }
                    }
                }
                ?>

                <input type='text' name='search' class='search' placeholder='<?= !empty($search_placeholder) ? $search_placeholder : 'Введите название' ?>' value='<?= isset($_GET['search']) ? $_GET['search'] : '' ?>'>
                <button type='submit' class='search_btn'></button>
            </form>

            <? if (!empty($_GET['search'])) : ?>
                <a class="btn btn_transparent filter_reset" href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?><?= !empty($ids)?'?ids='.$ids:'' ?>">Сбросить</a>
            <? endif; ?>

        </div>

        <? if(!empty($add)): ?>
            <a href="?add<?= !empty($ids)?'Item':'' ?><?= !empty($ids)?'&ids='.$ids:'' ?><?= !empty($parent)?'&parent='.$parent:'' ?>" class="btn btn_red btn_add">
                <?= !empty($add_text) ? $add_text : 'Добавить' ?>
            </a>
        <? endif ?>

    </div>

    <? if (!empty($filters)): ?>
        <?= $filters ?>
    <? endif; ?>

</div>