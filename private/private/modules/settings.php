<?php
// Загружаем конфигурацию для настроек
$config = require_once ROOT . '/config/modules/settings.php';

use app\Models\Settings;
use app\FileUpload;
use app\Helpers;
use app\Form;

FileUpload::deleteImageFile();
$obj = Settings::findById(1);

if (isset($_POST['edit'])) :

    unset($_POST['edit']);

    foreach ($_POST as $name => $value) {
        if (is_array($value)) continue;
        
        // Проверяем, существует ли поле в конфигурации и включено ли оно
        $fieldEnabled = true;
        
        // Проверяем вкладку "Основные"
        if ($config['fields']['site']['enabled']) {
            if (isset($config['fields']['site'][$name])) {
                $fieldEnabled = $config['fields']['site'][$name]['enabled'] ?? false;
            }
        }
        
        // Проверяем вкладку "Контакты"
        if ($config['fields']['contacts']['enabled']) {
            // Проверяем email поля (прямые поля, не вложенные в emails)
            if (isset($config['fields']['contacts'][$name])) {
                $fieldEnabled = $config['fields']['contacts'][$name]['enabled'] ?? false;
            }
            
            // Проверяем телефонные поля (прямые поля, не вложенные в phones)
            if (in_array($name, ['phone', 'phone2', 'phone3'])) {
                if (isset($config['fields']['contacts'][$name])) {
                    $fieldEnabled = $config['fields']['contacts'][$name]['enabled'] ?? false;
                }
            }
            
            // Проверяем координаты карты
            if ($name === 'coords') {
                $fieldEnabled = $config['fields']['contacts']['map']['enabled'] && 
                               ($config['fields']['contacts']['map']['coords']['enabled'] ?? false);
            }
            
            // Проверяем текст на изображении
            if ($name === 'image_text') {
                $fieldEnabled = $config['fields']['contacts']['image_text']['enabled'] ?? false;
            }
        }
        
        // Проверяем вкладку "Организация"
        if ($config['fields']['organization']['enabled']) {
            if (isset($config['fields']['organization'][$name])) {
                $fieldEnabled = $config['fields']['organization'][$name]['enabled'] ?? false;
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

    // Загрузка изображения для контактов
    if ($config['fields']['contacts']['image']['enabled'] ?? false) {
        FileUpload::uploadImage(
            'image', 
            get_class($obj), 
            'image', 
            $obj->id, 
            $config['fields']['contacts']['image']['width'] ?? 1350, 
            $config['fields']['contacts']['image']['height'] ?? 600, 
            '/public/src/images/settings/', 
            0
        );
    }
    
    // Загрузка файла с реквизитами
    if ($config['fields']['contacts']['file']['enabled'] ?? false) {
        FileUpload::uploadFile(
            'file', 
            get_class($obj), 
            'file', 
            $obj->id, 
            '/public/src/files/settings/'
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

            <!-- Вкладки -->
            <div class="edit_tabs">
                <div class="edit_tabs_nav">
                    <?php if ($config['fields']['site']['enabled']): ?>
                    <button type="button" class="edit_tab_nav active" data-tab="general">Основные</button>
                    <?php endif; ?>
                    
                    <?php if ($config['fields']['contacts']['enabled']): ?>
                    <button type="button" class="edit_tab_nav <?= !$config['fields']['site']['enabled'] ? 'active' : '' ?>" data-tab="contacts">Контакты</button>
                    <?php endif; ?>
                    
                    <?php if ($config['fields']['organization']['enabled']): ?>
                    <button type="button" class="edit_tab_nav" data-tab="organization">Организация</button>
                    <?php endif; ?>
                </div>
                
                <div class="edit_tabs_content">
                    <!-- Вкладка "Основные" -->
                    <?php if ($config['fields']['site']['enabled']): ?>
                    <div class="edit_tab_content <?= $config['fields']['site']['enabled'] ? 'active' : '' ?>" id="tab_general">
                        <?php if ($config['fields']['site']['sitename']['enabled'] ?? false): ?>
                            <?= Form::input($config['fields']['site']['sitename']['title'] ?? 'Название сайта', 'sitename', $obj->sitename, 0, '', '', '') ?>
                        <?php endif; ?>
                        
                        <?php if ($config['fields']['site']['copyright']['enabled'] ?? false): ?>
                            <?= Form::input($config['fields']['site']['copyright']['title'] ?? 'Копирайт', 'copyright', $obj->copyright, 0, '', '', '') ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Вкладка "Контакты" -->
                    <?php if ($config['fields']['contacts']['enabled']): ?>
                    <div class="edit_tab_content <?= !$config['fields']['site']['enabled'] && $config['fields']['contacts']['enabled'] ? 'active' : '' ?>" id="tab_contacts">
                        
                        <!-- Email поля -->
                        <div class="flex2">
                            <?php if ($config['fields']['contacts']['email']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['contacts']['email']['title'] ?? 'Email для отображения на сайте', 'email', $obj->email, 0, '', '', '') ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['contacts']['email_sends']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['contacts']['email_sends']['title'] ?? 'Email для сообщений с сайта', 'email_sends', $obj->email_sends, 0, '', '', '') ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Телефонные поля -->
                        <div class="flex3">
                            <?php if ($config['fields']['contacts']['phone']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['contacts']['phone']['title'] ?? 'Телефон', 'phone', $obj->phone, 0, '', '', '') ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['contacts']['phone2']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['contacts']['phone2']['title'] ?? 'Телефон 2', 'phone2', $obj->phone2, 0, '', '', '') ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['contacts']['phone3']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['contacts']['phone3']['title'] ?? 'Телефон 3', 'phone3', $obj->phone3, 0, '', '', '') ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Время работы -->
                        <?php if ($config['fields']['contacts']['time_job']['enabled'] ?? false): ?>
                            <?= Form::textarea(
                                $config['fields']['contacts']['time_job']['title'] ?? 'Время работы', 
                                'time_job', 
                                $obj->time_job, 
                                100, 
                                '', 
                            ) ?>
                        <?php endif; ?>

                        <!-- Карта и координаты -->
                        <?php if (($config['fields']['contacts']['map']['enabled'] ?? false) && ($config['fields']['contacts']['map']['coords']['enabled'] ?? false)): ?>
                            <?= Form::input(
                                $config['fields']['contacts']['map']['coords']['title'] ?? 'Координаты на карте', 
                                'coords', 
                                $obj->coords, 
                                0, 
                                'hidden', 
                                '', 
                                ''
                            ) ?>
                            
                            <?
                                if(empty($obj->coords)) $obj->coords = '55.7558,37.6173';
                                $c = explode(',', $obj->coords);
                            ?>
                            <div class='map' id='YMapsID' data-lat='<?= $c[0] ?>' data-lng='<?= $c[1] ?>'></div>
                        <?php endif; ?>
                        
                        <!-- Изображение -->
                        <?php if ($config['fields']['contacts']['image']['enabled'] ?? false): ?>
                            <?= Form::image(
                                $config['fields']['contacts']['image']['title'].' ('.$config['fields']['contacts']['image']['width'].'x'.$config['fields']['contacts']['image']['height'].')', 
                                'image', 
                                $obj, 
                                '', 
                                0
                            ) ?>
                        <?php endif; ?>
                        
                        <!-- Текст на изображении -->
                        <?php if ($config['fields']['contacts']['image_text']['enabled'] ?? false): ?>
                            <?= Form::input(
                                $config['fields']['contacts']['image_text']['title'] ?? 'Текст на изображении', 
                                'image_text', 
                                $obj->image_text, 
                                0, 
                                '', 
                                '', 
                                ''
                            ) ?>
                        <?php endif; ?>

                        <!-- Реквизиты -->
                        <?php if ($config['fields']['contacts']['requisites']['enabled'] ?? false): ?>
                            <?= Form::textarea(
                                $config['fields']['contacts']['requisites']['title'] ?? 'Реквизиты', 
                                'requisites', 
                                $obj->requisites, 
                                200, 
                                ''
                            ) ?>
                        <?php endif; ?>

                        <!-- Файл с реквизитами -->
                        <?php if ($config['fields']['contacts']['file']['enabled'] ?? false): ?>
                            <?= Form::file(
                                $config['fields']['contacts']['file']['title'] ?? 'Файл с реквизитами', 
                                'file', 
                                $obj, 
                                ''
                            ) ?>
                        <?php endif; ?>

                    </div>
                    <?php endif; ?>
                    
                    <!-- Вкладка "Организация" -->
                    <?php if ($config['fields']['organization']['enabled']): ?>
                    <div class="edit_tab_content" id="tab_organization">
                        <?php if ($config['microdata']['organization'] ?? false): ?>
                            <small style="display: block; margin-bottom: 15px;">Данные для микроразметки Organization</small>
                        <?php endif; ?>
                        
                        <?php if ($config['fields']['organization']['company']['enabled'] ?? false): ?>
                            <?= Form::input(
                                $config['fields']['organization']['company']['title'] ?? 'Название организации', 
                                'company', 
                                $obj->company, 
                                0, 
                                '', 
                                '', 
                                ''
                            ) ?>
                        <?php endif; ?>
                        
                        <div class="flex2">
                            <?php if ($config['fields']['organization']['postcode']['enabled'] ?? false): ?>
                                <?= Form::input(
                                    $config['fields']['organization']['postcode']['title'] ?? 'Почтовый индекс', 
                                    'postcode', 
                                    $obj->postcode, 
                                    0, 
                                    '', 
                                    '', 
                                    ''
                                ) ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['organization']['region']['enabled'] ?? false): ?>
                                <?= Form::input(
                                    $config['fields']['organization']['region']['title'] ?? 'Регион', 
                                    'region', 
                                    $obj->region, 
                                    0, 
                                    '', 
                                    '', 
                                    ''
                                ) ?>
                            <?php endif; ?>
                        </div>

                        <div class="flex2">
                            <?php if ($config['fields']['organization']['city']['enabled'] ?? false): ?>
                                <?= Form::input(
                                    $config['fields']['organization']['city']['title'] ?? 'Город', 
                                    'city', 
                                    $obj->city, 
                                    0, 
                                    '', 
                                    '', 
                                    ''
                                ) ?>
                            <?php endif; ?>

                            <?php if ($config['fields']['organization']['address']['enabled'] ?? false): ?>
                                <?= Form::input(
                                    $config['fields']['organization']['address']['title'] ?? 'Адрес', 
                                    'address', 
                                    $obj->address, 
                                    0, 
                                    '', 
                                    '', 
                                    ''
                                ) ?>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?= Form::submit(1, 1, 'Сохранить', '') ?>
        </form>
    </div>

<?php endif; ?>