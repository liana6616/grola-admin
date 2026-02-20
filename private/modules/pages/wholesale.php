<?php

use app\Models\PageWholesale;
use app\Form;
use app\FileUpload;
use app\Helpers;

if (isset($_GET['edit'])):

    $draft_id = 1; // ID черновика
    $published_id = 2; // ID чистовика
    
    $wholesale = PageWholesale::findById($draft_id);
    $wholesale_published = PageWholesale::findById($published_id);
    
    if (!$wholesale) {
        // Если записи нет, создаем новую
        $wholesale = new PageWholesale();
        $wholesale->id = $draft_id;
        $wholesale->is_draft = 1;
        $wholesale->original_id = $published_id;
        $wholesale->save();
        $wholesale = PageWholesale::findById($draft_id);

        $wholesale_published = new PageWholesale();
        $wholesale_published->id = $published_id;
        $wholesale_published->is_draft = 0;
        $wholesale_published->original_id = 0;
        $wholesale_published->save();
        $wholesale_published = PageWholesale::findById($published_id);
    }
?>

    <input type="hidden" name="wholesale_id" value="<?= $wholesale->id ?>">
    <input type="hidden" name="wholesale_published_id" value="<?= $wholesale->original_id ?? 0 ?>">

    <fieldset class="input_block">
        <legend>Блок под основным баннером</legend>

        <div class="flex2">
            <?= Form::image(
                'Изображение',
                'wholesale_info_image',
                $wholesale,
                false,
                false,
                'info_image' // поле в модели
            ) ?>
            
            <div class="input_block">
                <?= Form::input(
                    'Заголовок',
                    'wholesale_info_title',
                    $wholesale->info_title ?? '',
                    0,
                    'text',
                    '',
                    ''
                ) ?>
                
                <?= Form::textarea(
                    'Текст',
                    'wholesale_info_text',
                    $wholesale->info_text ?? '',
                    200,
                    ''
                ) ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="input_block">
        <legend>Блок на сером фоне с изображением</legend>
        
        <div class="flex2">
            <div class="input_block">
                <?= Form::input(
                    'Заголовок',
                    'wholesale_block_title',
                    $wholesale->title ?? '',
                    0,
                    'text',
                    '',
                    ''
                ) ?>

                <?= Form::textarea(
                    'Текст',
                    'wholesale_block_text',
                    $wholesale->text ?? '',
                    300,
                    ''
                ) ?>
            </div>

            <?= Form::image(
                'Изображение справа (730x590)',
                'wholesale_block_image',
                $wholesale,
                false,
                false,
                'image' // поле в модели
            ) ?>
        </div>
    </fieldset>

    <fieldset class="input_block">
        <legend>Баннер над формой</legend>
        
        <div class="flex2">
            <?= Form::image(
                'Изображение баннера',
                'wholesale_banner', 
                $wholesale,
                false,
                false,
                'banner' // поле в модели
            ) ?>
            
            <?= Form::textarea(
                'Текст на баннере',
                'wholesale_banner_text', 
                $wholesale->banner_text ?? '',
                150,
                ''
            ) ?>
        </div>
    </fieldset>

<?php
elseif (isset($_POST['add']) || isset($_POST['edit'])):

    $wholesale_id = $_POST['wholesale_id'] ?? 1;
    $wholesale_published_id = $_POST['wholesale_published_id'] ?? 2;
    
    // Получаем или создаем объект
    $wholesale = PageWholesale::findById($wholesale_id);
    if (!$wholesale) {
        $wholesale = new PageWholesale();
        $wholesale->id = $wholesale_id;
    }
    
    // Заполняем данные из формы
    $wholesale->info_title = trim($_POST['wholesale_info_title'] ?? '');
    $wholesale->info_text = trim($_POST['wholesale_info_text'] ?? '');
    $wholesale->title = trim($_POST['wholesale_block_title'] ?? ''); 
    $wholesale->text = trim($_POST['wholesale_block_text'] ?? '');   
    $wholesale->banner_text = trim($_POST['wholesale_banner_text'] ?? '');
    
    // Системные поля
    $wholesale->edit_date = date("Y-m-d H:i:s");
    $wholesale->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
    $wholesale->is_draft = 1;
    
    if (!empty($wholesale_published_id) && $wholesale_published_id != $wholesale_id) {
        $wholesale->original_id = $wholesale_published_id;
    }
    
    // Сохраняем запись
    $wholesale->save();
    
    // Путь для загрузки изображений
    $uploadPath = '/public/src/images/pages/page_wholesale/';
    
    // Создаем директорию, если её нет
    $fullPath = ROOT . $uploadPath;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
    }
    
    // Загружаем изображения
    // info_image
    if (!empty($_FILES['wholesale_info_image']['name'][0])) {
        FileUpload::uploadImage(
            'wholesale_info_image', // имя поля из формы
            get_class($wholesale),
            'info_image', // поле в модели
            $wholesale->id,
            0,
            0,
            $uploadPath,
            0
        );
    }
    
    // block image (бывшее image)
    if (!empty($_FILES['wholesale_block_image']['name'][0])) {
        FileUpload::uploadImage(
            'wholesale_block_image', // имя поля из формы
            get_class($wholesale),
            'image', // поле в модели
            $wholesale->id,
            730,
            590,
            $uploadPath,
            0
        );
    }
    
    // banner
    if (!empty($_FILES['wholesale_banner']['name'][0])) {
        FileUpload::uploadImage(
            'wholesale_banner', // имя поля из формы
            get_class($wholesale),
            'banner', // поле в модели
            $wholesale->id,
            0,
            0,
            $uploadPath,
            0
        );
    }

    // Если нажата кнопка "Опубликовать"
    if (!empty($_POST['publish']) && isset($useDrafts) && $useDrafts) {
    
        // Находим или создаем чистовик
        if (!empty($wholesale->original_id)) {
            $wholesale_published = PageWholesale::findById($wholesale->original_id);
        } else {
            $wholesale_published = PageWholesale::findById(2);
        }
        
        // ВАЖНО: Проверяем, существует ли опубликованная запись
        if (!$wholesale_published) {
            $wholesale_published = new PageWholesale();
            $wholesale_published->id = 2;
            $wholesale_published->is_draft = 0;
            $wholesale_published->original_id = 0;
        }
        
        // Копируем данные из черновика в чистовик
        $excludeFields = ['id', 'is_draft', 'original_id', 'info_image', 'image', 'banner'];
        $wholesale_published = PageWholesale::copyData($wholesale, $wholesale_published, $excludeFields);
        
        $wholesale_published->is_draft = 0;
        $wholesale_published->original_id = 0;
        $wholesale_published->edit_date = date("Y-m-d H:i:s");
        $wholesale_published->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
        
        // Копируем изображения
        $imageFields = ['info_image', 'image', 'banner'];
        foreach ($imageFields as $field) {
            // Удаляем старые изображения чистовика
            if (!empty($wholesale_published->$field) && file_exists(ROOT . $wholesale_published->$field)) {
                @unlink(ROOT . $wholesale_published->$field);
                $wholesale_published->$field = '';
            }
            
            // Копируем новые из черновика
            if (!empty($wholesale->$field) && file_exists(ROOT . $wholesale->$field)) {
                $extension = pathinfo(ROOT . $wholesale->$field, PATHINFO_EXTENSION);
                $newFile = $uploadPath . uniqid() . '.' . $extension;
                if (copy(ROOT . $wholesale->$field, ROOT . $newFile)) {
                    $wholesale_published->$field = $newFile;
                }
            }
        }
        
        $wholesale_published->save();
        
        // Обновляем связь черновика
        $wholesale->original_id = $wholesale_published->id;
        $wholesale->save();
    }

endif;
?>