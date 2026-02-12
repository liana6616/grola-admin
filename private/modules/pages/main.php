<?php
// /private/modules/pages/main.php

use app\Models\PageMain;
use app\Form;
use app\FileUpload;
use app\Helpers;

if (isset($_GET['edit'])):

    $draft_id = 1; // ID черновика
    $main_published_id = 2; // ID чистовика
    
    $main = PageMain::findById($draft_id);
    $main_published = PageMain::findById($main_published_id);
    
    if (!$main) {
        // Если записи нет, создаем новую
        $main = new PageMain();
        $main->id = $draft_id;
        $main->is_draft = 1;
        $main->original_id = $main_published_id;
        $main->save();
        $main = PageMain::findById($draft_id);

        $main_published = new PageMain();
        $main_published->id = $main_published_id;
        $main_published->is_draft = 0;
        $main_published->original_id = 0;
        $main_published->save();
        $main_published = PageMain::findById($main_published_id);
    }
?>

    <input type="hidden" name="main_id" value="<?= $main->id ?>">
    <input type="hidden" name="main_published_id" value="<?= $main->original_id ?? 0 ?>">
    
    <fieldset class="input_block">
        <legend>Блок с ключевыми показателями</legend>
        
        <div class="flex2">
            <?= Form::input('Цифра 1', 'num1', $main->num1 ?? '', 0, 'text', '', '') ?>
            <?= Form::input('Текст 1', 'txt1', $main->txt1 ?? '', 0, 'text', '', '') ?>
        </div>
        
        <div class="flex2">
            <?= Form::input('Цифра 2', 'num2', $main->num2 ?? '', 0, 'text', '', '') ?>
            <?= Form::input('Текст 2', 'txt2', $main->txt2 ?? '', 0, 'text', '', '') ?>
        </div>
        
        <div class="flex2">
            <?= Form::input('Цифра 3', 'num3', $main->num3 ?? '', 0, 'text', '', '') ?>
            <?= Form::input('Текст 3', 'txt3', $main->txt3 ?? '', 0, 'text', '', '') ?>
        </div>
    </fieldset>

    <fieldset class="input_block">
        <legend>Блок "О нас"</legend>
        
        <?= Form::textarea('Текст о нас', 'about_text', $main->about_text ?? '', 140, '') ?>
        
        <div class="flex2">
            <?= Form::image('Фото (450x490)', 'about_image', $main, false, false, 'about_image') ?>
            <div class="input_block">
                <?= Form::input('Имя', 'about_name', $main->about_name ?? '', 0, 'text', '', '') ?>
                <?= Form::input('Должность', 'about_position', $main->about_position ?? '', 0, 'text', '', '') ?>
                <?= Form::input('Текст кнопки', 'about_btn', $main->about_btn ?? '', 0, 'text', '', '') ?>
                <?= Form::input('Ссылка с кнопки', 'about_btn_link', $main->about_btn_link ?? '', 0, 'text', '', '') ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="input_block">
        <legend>Баннер</legend>
        
        <?= Form::input('Текст', 'info_text', $main->info_text ?? '', 0, 'text', '', '') ?>
        <?= Form::image('Изображение (1380x260)', 'info_image', $main, false, false, 'info_image') ?>
    </fieldset>

    <fieldset class="input_block">
        <legend>Блок FAQ</legend>
        
        <?= Form::input('Заголовок', 'faq_name', $main->faq_name ?? '', 0, 'text', '', '') ?>
        <?= Form::textarea('Текст', 'faq_text', $main->faq_text ?? '', 100, '') ?>
        <div class="flex2">
            <?= Form::input('Текст кнопки', 'faq_btn', $main->faq_btn ?? '', 0, 'text', '', '') ?>
            <?= Form::input('Ссылка с кнопки', 'faq_btn_link', $main->faq_btn_link ?? '', 0, 'text', '', '') ?>
        </div>
    </fieldset>

    <fieldset class="input_block">
        <legend>Баннер рядом с новостями</legend>
        <div class="flex2">
        <?= Form::image('Изображение (730x590)', 'opt_image', $main, false, false, 'opt_image') ?>
        <div class="input_block">
            <?= Form::input('Заголовок', 'opt_name', $main->opt_name ?? '', 0, 'text', '', '') ?>
            <?= Form::input('Текст', 'opt_text', $main->opt_text ?? '', 0, 'text', '', '') ?>
            <?= Form::input('Текст кнопки', 'opt_btn', $main->opt_btn ?? '', 0, 'text', '', '') ?>
            <?= Form::input('Ссылка с кнопки', 'opt_btn_link', $main->opt_btn_link ?? '', 0, 'text', '', '') ?>
    </fieldset>

<?php
elseif (isset($_POST['add']) || isset($_POST['edit'])):

    $main_id = $_POST['main_id'] ?? 1;
    $main_published_id = $_POST['main_published_id'] ?? 2;
    
    // Получаем или создаем объект
    $main = PageMain::findById($main_id);
    if (!$main) {
        $main = new PageMain();
        $main->id = $main_id;
    }
    
    // Заполняем данные из формы
    $main->num1 = trim($_POST['num1'] ?? '');
    $main->txt1 = trim($_POST['txt1'] ?? '');
    $main->num2 = trim($_POST['num2'] ?? '');
    $main->txt2 = trim($_POST['txt2'] ?? '');
    $main->num3 = trim($_POST['num3'] ?? '');
    $main->txt3 = trim($_POST['txt3'] ?? '');
    $main->about_text = trim($_POST['about_text'] ?? '');
    $main->about_name = trim($_POST['about_name'] ?? '');
    $main->about_position = trim($_POST['about_position'] ?? '');
    $main->about_btn = trim($_POST['about_btn'] ?? '');
    $main->about_btn_link = trim($_POST['about_btn_link'] ?? '');
    $main->info_text = trim($_POST['info_text'] ?? '');
    $main->faq_name = trim($_POST['faq_name'] ?? '');
    $main->faq_text = trim($_POST['faq_text'] ?? '');
    $main->faq_btn = trim($_POST['faq_btn'] ?? '');
    $main->faq_btn_link = trim($_POST['faq_btn_link'] ?? '');
    $main->opt_name = trim($_POST['opt_name'] ?? '');
    $main->opt_text = trim($_POST['opt_text'] ?? '');
    $main->opt_btn = trim($_POST['opt_btn'] ?? '');
    $main->opt_btn_link = trim($_POST['opt_btn_link'] ?? '');
    
    // Системные поля
    $main->edit_date = date("Y-m-d H:i:s");
    $main->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
    $main->is_draft = 1;
    
    if (!empty($main_published_id) && $main_published_id != $main_id) {
        $main->original_id = $main_published_id;
    }
    
    // Сохраняем запись
    $main->save();
    
    // Путь для загрузки изображений
    $uploadPath = '/public/src/images/pages/page_main/';
    
    // Загружаем изображения
    $imageFields = [
        'about_image' => [450,490],
        'info_image' => [1380,260],
        'opt_image' => [730,590]
    ];
    
    foreach ($imageFields as $field => $size) {
        if (!empty($_FILES[$field]['name'][0])) {
            FileUpload::uploadImage(
                $field,
                get_class($main),
                $field,
                $main->id,
                $size[0],
                $size[1],
                $uploadPath,
                0
            );
        }
    }

    // Если нажата кнопка "Опубликовать"
    if (!empty($publish)) {
        
        // Находим или создаем чистовик
        if (!empty($main->original_id)) {
            $main_published = PageMain::findById($main->original_id);
        } else {
            $main_published = PageMain::findById(2);
            if (!$main_published) {
                $main_published = new PageMain();
                $main_published->id = 2;
            }
        }
        
        // Копируем данные из черновика в чистовик
        $excludeFields = ['id', 'is_draft', 'original_id', 'about_image', 'info_image', 'opt_image'];
        $main_published = PageMain::copyData($main, $main_published, $excludeFields);
        
        $main_published->is_draft = 0;
        $main_published->original_id = 0;
        $main_published->edit_date = date("Y-m-d H:i:s");
        $main_published->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
        
        // Копируем изображения
        $imageFields = ['about_image', 'info_image', 'opt_image'];
        foreach ($imageFields as $field) {
            // Удаляем старые изображения чистовика
            if (!empty($main_published->$field) && file_exists(ROOT . $main_published->$field)) {
                unlink(ROOT . $main_published->$field);
                $main_published->$field = '';
            }
            
            // Копируем новые из черновика
            if (!empty($main->$field) && file_exists(ROOT . $main->$field)) {
                $extension = pathinfo(ROOT . $main->$field, PATHINFO_EXTENSION);
                $newFile = $uploadPath . uniqid() . '.' . $extension;
                copy(ROOT . $main->$field, ROOT . $newFile);
                $main_published->$field = $newFile;
            }
        }
        
        $main_published->save();
        
        // Обновляем связь черновика
        $main->original_id = $main_published->id;
        $main->save();
    }

endif;
?>