<?php
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
        <legend>Баннер</legend>
        
        <?= Form::textarea('Текст', 'info_text', $main->info_text ?? '', '60') ?>
        <?= Form::image('Изображение (1380x260)', 'info_image', $main, false, false, 'info_image') ?>
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
            </div>
        </div>
    </fieldset>

<?php
elseif (isset($_POST['edit'])):

    $main_id = $_POST['main_id'] ?? 1;
    $main_published_id = $_POST['main_published_id'] ?? 2;

    // Получаем или создаем объект
    $main = PageMain::findById($main_id);
    if (!$main) {
        $main = new PageMain();
        $main->id = $main_id;
    }
    
    // Заполняем данные из формы
    $main->info_text = trim($_POST['info_text'] ?? '');
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
        $excludeFields = ['id', 'is_draft', 'original_id', 'info_image', 'opt_image'];
        $main_published = PageMain::copyData($main, $main_published, $excludeFields);
        
        $main_published->is_draft = 0;
        $main_published->original_id = 0;
        $main_published->edit_date = date("Y-m-d H:i:s");
        $main_published->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
        
        // Копируем изображения
        $imageFields = ['info_image', 'opt_image'];
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