<?php
// Загружаем конфигурацию для цитат директора
$config = require_once ROOT . '/config/modules/director_quotes.php';

use app\Models\DirectorQuotes;
use app\FileUpload;
use app\Helpers;
use app\Form;

FileUpload::deleteImageFile();

// Получаем существующую запись (предполагается, что будет только одна запись)
$obj = DirectorQuotes::findById(1);

// Если запись не существует, создаем новую
if (!$obj) {
    $obj = new DirectorQuotes();
    $obj->id = 1;
    $obj->save();
}

if (isset($_POST['edit'])) :

    unset($_POST['edit']);

    foreach ($_POST as $name => $value) {
        if (is_array($value)) continue;
        
        // Проверяем, существует ли поле в конфигурации и включено ли оно
        $fieldEnabled = false;
        
        // Проверяем основные поля (image, name, position, text)
        if (isset($config['fields'][$name])) {
            $fieldEnabled = $config['fields'][$name]['enabled'] ?? false;
        }
        // Проверяем поля кнопки
        else if (isset($config['fields']['button'])) {
            if ($config['fields']['button']['enabled'] && isset($config['fields']['button'][$name])) {
                $fieldEnabled = $config['fields']['button'][$name]['enabled'] ?? false;
            }
        }
        
        if ($fieldEnabled) {
            $obj->$name = trim($value);
        }
    }
    
    // Системные поля
    $obj->edit_date = date("Y-m-d H:i:s");
    $obj->edit_admin_id = $_SESSION['admin']['id'] ?? 0;

    $obj->save();

    // Загрузка изображения директора
    if (isset($config['fields']['image']['enabled']) && $config['fields']['image']['enabled']) {
        FileUpload::uploadImage(
            'image', 
            get_class($obj), 
            'image', 
            $obj->id, 
            $config['fields']['image']['width'] ?? 400, 
            $config['fields']['image']['height'] ?? 400, 
            '/public/src/images/director_quotes/', 
            0
        );
    }

    $_SESSION['notice'] = 'Сохранено';

    header("Location: {$_SERVER['REQUEST_URI']}");
    exit;
else: 

    // Заголовок модуля из конфига
    $title = $config['module']['title'] ?? '';
?>

    <div class="editHead">
        <h1><?= $title ?></h1>
    </div>
    
    <div class="edit_block">
        <form action='<?= $_SERVER['REDIRECT_URL'] ?>' method='post' class='edit_form' enctype='multipart/form-data'>

            <?php if (isset($config['fields']['image']['enabled']) && $config['fields']['image']['enabled']): ?>
                <?= Form::image(
                    $config['fields']['image']['title'] . ' (' . 
                    ($config['fields']['image']['width'] ?? 400) . '×' . 
                    ($config['fields']['image']['height'] ?? 400) . ')', 
                    'image', 
                    $obj, 
                    0
                ) ?>
            <?php endif; ?>
            
            <?php if (isset($config['fields']['name']['enabled']) && $config['fields']['name']['enabled']): ?>
                <?= Form::input(
                    $config['fields']['name']['title'] ?? 'ФИО директора', 
                    'name', 
                    $obj->name, 
                    0, 
                    '', 
                    '', 
                    ''
                ) ?>
            <?php endif; ?>
            
            <?php if (isset($config['fields']['position']['enabled']) && $config['fields']['position']['enabled']): ?>
                <?= Form::input(
                    $config['fields']['position']['title'] ?? 'Должность директора', 
                    'position', 
                    $obj->position, 
                    0, 
                    '', 
                    '', 
                    ''
                ) ?>
            <?php endif; ?>
            
            <?php if (isset($config['fields']['text']['enabled']) && $config['fields']['text']['enabled']): ?>
                <?= Form::textarea(
                    $config['fields']['text']['title'] ?? 'Цитата', 
                    'text', 
                    $obj->text, 
                    200, 
                    ''
                ) ?>
            <?php endif; ?>
                    
            <!-- Вкладка "Кнопка" -->
            <?php if (isset($config['fields']['button']['enabled']) && $config['fields']['button']['enabled']): ?>
            <fieldset class="input_block">
                <legend>Кнопка</legend>
                <?php if (isset($config['fields']['button']['button_name']['enabled']) && $config['fields']['button']['button_name']['enabled']): ?>
                    <?= Form::input(
                        $config['fields']['button']['button_name']['title'] ?? 'Текст на кнопке', 
                        'button_name', 
                        $obj->button_name, 
                        0, 
                        '', 
                        '', 
                        ''
                    ) ?>
                <?php endif; ?>
                
                <?php if (isset($config['fields']['button']['button_link']['enabled']) && $config['fields']['button']['button_link']['enabled']): ?>
                    <?= Form::input(
                        $config['fields']['button']['button_link']['title'] ?? 'Ссылка с кнопки', 
                        'button_link', 
                        $obj->button_link, 
                        0, 
                        '', 
                        '', 
                        ''
                    ) ?>
                <?php endif; ?>
            </fieldset>
            <?php endif; ?>

            <?= Form::submit(1, 1, 'Сохранить', '') ?>
        </form>
    </div>

<?php endif; ?>