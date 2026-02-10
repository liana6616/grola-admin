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
            // Проверяем email поля
            if ($config['fields']['contacts']['emails']['enabled']) {
                if (isset($config['fields']['contacts']['emails'][$name])) {
                    $fieldEnabled = $config['fields']['contacts']['emails'][$name]['enabled'] ?? false;
                }
            }
            
            // Проверяем телефонные поля
            if ($config['fields']['contacts']['phones']['enabled']) {
                if (isset($config['fields']['contacts']['phones'][$name])) {
                    $fieldEnabled = $config['fields']['contacts']['phones'][$name]['enabled'] ?? false;
                }
            }
            
            // Проверяем время работы
            if ($name === 'time_job') {
                $fieldEnabled = $config['fields']['contacts']['time_job']['enabled'] ?? false;
            }
            
            // Проверяем координаты карты
            if ($name === 'coords') {
                $fieldEnabled = $config['fields']['contacts']['map']['enabled'] && 
                               ($config['fields']['contacts']['map']['coords']['enabled'] ?? false);
            }
            
            // Проверяем реквизиты
            if ($name === 'requisites') {
                $fieldEnabled = $config['fields']['contacts']['requisites']['enabled'] && 
                               ($config['fields']['contacts']['requisites']['text']['enabled'] ?? false);
            }
        }
        
        // Проверяем вкладку "Организация"
        if ($config['fields']['organization']['enabled']) {
            // Проверяем название компании
            if ($name === 'company') {
                $fieldEnabled = $config['fields']['organization']['company']['enabled'] ?? false;
            }
            
            // Проверяем адресные поля
            if (isset($config['fields']['organization']['address'][$name])) {
                $fieldEnabled = $config['fields']['organization']['address'][$name]['enabled'] ?? false;
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
    if ($config['file_upload']['images']['contact_image']['enabled']) {
        FileUpload::uploadImage(
            'image', 
            get_class($obj), 
            'image', 
            $obj->id, 
            $config['file_upload']['images']['contact_image']['width'] ?? 800, 
            $config['file_upload']['images']['contact_image']['height'] ?? 600, 
            '/public/src/images/settings/', 
            0
        );
    }
    
    // Загрузка файла с реквизитами
    if ($config['file_upload']['files']['requisites_file']['enabled']) {
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
else: ?>
    <div class="editHead">
        <h1>Настройки</h1>
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
                        
                        <?php if (($config['fields']['contacts']['emails']['email']['enabled'] ?? false) || ($config['fields']['contacts']['emails']['email_sends']['enabled'] ?? false)): ?>
                        <div class="flex2">
                            <?php if ($config['fields']['contacts']['emails']['email']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['contacts']['emails']['email']['title'] ?? 'Email для отображения на сайте', 'email', $obj->email, 0, '', '', '') ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['contacts']['emails']['email_sends']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['contacts']['emails']['email_sends']['title'] ?? 'Email для сообщений с сайта', 'email_sends', $obj->email_sends, 0, '', '', '') ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (($config['fields']['contacts']['phones']['phone']['enabled'] ?? false) || ($config['fields']['contacts']['phones']['phone2']['enabled'] ?? false)): ?>
                        <div class="flex2">
                            <?php if ($config['fields']['contacts']['phones']['phone']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['contacts']['phones']['phone']['title'] ?? 'Телефон', 'phone', $obj->phone, 0, '', '', '') ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['contacts']['phones']['phone2']['enabled'] ?? false): ?>
                                <?= Form::input($config['fields']['contacts']['phones']['phone2']['title'] ?? 'Телефон 2', 'phone2', $obj->phone2, 0, '', '', '') ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($config['fields']['contacts']['time_job']['enabled'] ?? false): ?>
                            <?= Form::input(
                                $config['fields']['contacts']['time_job']['title'] ?? 'Время работы', 
                                'time_job', 
                                $obj->time_job, 
                                0, 
                                '', 
                                '', 
                                ''
                            ) ?>
                        <?php endif; ?>

                        <?php if (($config['fields']['contacts']['map']['enabled'] ?? false) && ($config['fields']['contacts']['map']['coords']['enabled'] ?? false)): ?>
                            <?= Form::input(
                                $config['fields']['contacts']['map']['coords']['title'] ?? 'Где вы находитесь? Поставьте точку на карте', 
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
                        
                        <?php if ($config['file_upload']['images']['contact_image']['enabled']): ?>
                            <?= Form::image(
                                $config['file_upload']['images']['contact_image']['title'] ?? 'Фото для страницы контактов', 
                                'image', 
                                $obj, 
                                '', 
                                0
                            ) ?>
                        <?php endif; ?>

                        <?php if (($config['fields']['contacts']['requisites']['enabled'] ?? false) && ($config['fields']['contacts']['requisites']['text']['enabled'] ?? false)): ?>
                            <?= Form::textarea(
                                $config['fields']['contacts']['requisites']['text']['title'] ?? 'Реквизиты', 
                                'requisites', 
                                $obj->requisites, 
                                200, 
                                ''
                            ) ?>
                        <?php endif; ?>

                        <?php if (($config['fields']['contacts']['requisites']['enabled'] ?? false) && ($config['fields']['contacts']['requisites']['file']['enabled'] ?? false)): ?>
                            <?= Form::file(
                                $config['fields']['contacts']['requisites']['file']['title'] ?? 'Файл с реквизитами', 
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
                            <small>Данные для микроразметки Organization</small>
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
                        
                        <?php if (($config['fields']['organization']['address']['postcode']['enabled'] ?? false) || 
                                 ($config['fields']['organization']['address']['region']['enabled'] ?? false) || 
                                 ($config['fields']['organization']['address']['city']['enabled'] ?? false)): ?>
                        <div class="flex2">
                            <?php if ($config['fields']['organization']['address']['postcode']['enabled'] ?? false): ?>
                                <?= Form::input(
                                    $config['fields']['organization']['address']['postcode']['title'] ?? 'Почтовый индекс', 
                                    'postcode', 
                                    $obj->postcode, 
                                    0, 
                                    '', 
                                    '', 
                                    ''
                                ) ?>
                            <?php endif; ?>
                            
                            <?php if ($config['fields']['organization']['address']['region']['enabled'] ?? false): ?>
                                <?= Form::input(
                                    $config['fields']['organization']['address']['region']['title'] ?? 'Регион', 
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
                            <?php if ($config['fields']['organization']['address']['city']['enabled'] ?? false): ?>
                                <?= Form::input(
                                    $config['fields']['organization']['address']['city']['title'] ?? 'Город', 
                                    'city', 
                                    $obj->city, 
                                    0, 
                                    '', 
                                    '', 
                                    ''
                                ) ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($config['fields']['organization']['address']['address']['enabled'] ?? false): ?>
                            <?= Form::input(
                                $config['fields']['organization']['address']['address']['title'] ?? 'Адрес', 
                                'address', 
                                $obj->address, 
                                0, 
                                '', 
                                '', 
                                ''
                            ) ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?= Form::submit(1, 1, 'Сохранить', '') ?>
        </form>
    </div>

<?php endif; ?>