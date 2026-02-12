<?php

use app\Models\PageAbout;
use app\Form;
use app\FileUpload;
use app\Helpers;

if (isset($_GET['edit'])):

    $about_id = 1; // ID черновика
    $about_original_id = 2; // ID чистовика
    $about = PageAbout::findById($about_id);
    $about_original = PageAbout::findById($about_original_id);
    
    if (!$about) {
        // Если записи нет, создаем новую
        $about = new PageAbout();
        $about->id = $about_id;
        $about->is_draft = 1;
        $about->original_id = $about_original_id;
        $about->save();
        $about = PageAbout::findById($about_id);

        $about_original = new PageAbout();
        $about_original->id = $about_original_id;
        $about_original->is_draft = 0;
        $about_original->original_id = 0;
        $about_original->save();
        $about_original = PageAbout::findById($about_original_id);
    }
?>
    <input type="hidden" name="about_id" value="<?= $about->id ?>">
    <input type="hidden" name="about_original_id" value="<?= $about->original_id ?? 0 ?>">
    
        <?= Form::textarea(
            'Текст возле ключевых показателей',
            'about_text',
            $about->text ?? '',
            140,
            ''
        ) ?>

        <?= Form::textarea(
            'Текст в рамке',
            'about_text2',
            $about->text2 ?? '',
            140,
            ''
        ) ?>

    <fieldset class="input_block">
        <legend>Блок с двумя фото</legend>
        
        <?= Form::input(
            'Заголовок над текстом возле двух фото',
            'about_item_title',
            $about->item_title ?? '',
            0,
            'text',
            '',
            ''
        ) ?>

        <div class="flex2">
            <?= Form::textarea(
                'Первая часть текста',
                'about_item_text',
                $about->item_text ?? '',
                100,
                ''
            ) ?>

            <?= Form::textarea(
                'Вторая часть текста',
                'about_item_text2',
                $about->item_text2 ?? '',
                100,
                ''
            ) ?>
        </div>

        <div class="flex2">
            <?= Form::image(
                'Изображение 1 (330x390)',
                'about_image',
                $about,
                false,
                false,
                'image'
            ) ?>

            <?= Form::image(
                'Изображение 2 (330x390)',
                'about_image2',
                $about,
                false,
                false,
                'image2'
            ) ?>
        </div>
    </fieldset>

    <fieldset class="input_block">
        <legend>Блок с изображением и текстом</legend>
        
        <?= Form::image(
            'Изображение с текстом (1380x305)',
            'about_image3',
            $about,
            false,
            false,
            'image3'
        ) ?>

        <?= Form::textarea(
            'Текст на изображении',
            'about_image3_text',
            $about->image3_text ?? '',
            80,
            ''
        ) ?>
    </fieldset>

<?php
elseif (isset($_POST['edit'])):

    // Получаем ID страницы
    $page_id = $_POST['edit'] ?? 0;
    
    // ID записи page_about
    $about_id = $_POST['about_id'] ?? 1;
    $about_original_id = $_POST['about_original_id'] ?? 0;
    
    // Получаем или создаем объект
    $about = PageAbout::findById($about_id);
    if (!$about) {
        $about = new PageAbout();
        $about->id = $about_id;
    }
    
    // Заполняем данные из формы
    $about->text = trim($_POST['about_text'] ?? '');
    $about->text2 = trim($_POST['about_text2'] ?? '');
    $about->item_title = trim($_POST['about_item_title'] ?? '');
    $about->item_text = trim($_POST['about_item_text'] ?? '');
    $about->item_text2 = trim($_POST['about_item_text2'] ?? '');
    $about->image3_text = trim($_POST['about_image3_text'] ?? '');
    
    // Системные поля
    $about->edit_date = date("Y-m-d H:i:s");
    $about->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
    $about->is_draft = 1; // Всегда сохраняем как черновик
    
    // Если есть оригинал, сохраняем связь
    if (!empty($about_original_id) && $about_original_id != $about_id) {
        $about->original_id = $about_original_id;
    }
    
    // Сохраняем запись
    $about->save();
    
    // Загрузка изображений
    $uploadPath = '/public/src/images/pages/page_about/';
    
    // Загружаем изображение 1
    if (!empty($_FILES['about_image']['name'][0])) {
        FileUpload::uploadImage(
            'about_image',
            get_class($about),
            'image',
            $about->id,
            330,
            390,
            $uploadPath,
            0
        );
    }
    
    // Загружаем изображение 2
    if (!empty($_FILES['about_image2']['name'][0])) {
        FileUpload::uploadImage(
            'about_image2',
            get_class($about),
            'image2',
            $about->id,
            330,
            390,
            $uploadPath,
            0
        );
    }
    
    // Загружаем изображение 3
    if (!empty($_FILES['about_image3']['name'][0])) {
        FileUpload::uploadImage(
            'about_image3',
            get_class($about),
            'image3',
            $about->id,
            1380,
            305,
            $uploadPath,
            0
        );
    }

    // Если нажата кнопка "Опубликовать"
    if (!empty($_POST['publish']) && $useDrafts) {
        
        // Находим или создаем чистовик
        if (!empty($about->original_id)) {
            $published = PageAbout::findById($about->original_id);
        } else {
            $published = PageAbout::findById(1); // ID чистовика
            if (!$published) {
                $published = new PageAbout();
                $published->id = 1;
            }
        }
        
        // Копируем данные из черновика в чистовик
        $published = PageAbout::copyData($about, $published, ['id', 'is_draft', 'original_id','image3','image2','image']);
        
        $published->is_draft = 0;
        $published->original_id = 0;
        $published->edit_date = date("Y-m-d H:i:s");
        $published->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
        
        // Копируем изображения
        $imageFields = ['image', 'image2', 'image3'];
        foreach ($imageFields as $field) {
            // Удаляем старые изображения чистовика
            if (!empty($published->$field) && file_exists(ROOT . $published->$field)) {
                unlink(ROOT . $published->$field);
                $published->$field = '';
            }
            
            // Копируем новые из черновика
            if (!empty($about->$field) && file_exists(ROOT . $about->$field)) {
                $extension = pathinfo(ROOT . $about->$field, PATHINFO_EXTENSION);
                $newFile = $uploadPath . uniqid() . '.' . $extension;
                copy(ROOT . $about->$field, ROOT . $newFile);
                $published->$field = $newFile;
            }
        }
        
        $published->save();
        
        // Обновляем связь черновика
        $about->original_id = $published->id;
        $about->save();
    }

endif;
?>