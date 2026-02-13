<?php

use app\Models\PagePayment;
use app\Models\PagePaymentServices;
use app\Form;
use app\FileUpload;
use app\Helpers;

if (isset($_GET['edit'])):

    $draft_id = 1; // ID черновика
    $published_id = 2; // ID чистовика
    
    $payment = PagePayment::findById($draft_id);
    $payment_published = PagePayment::findById($published_id);
    
    if (!$payment) {
        // Если записи нет, создаем новую
        $payment = new PagePayment();
        $payment->id = $draft_id;
        $payment->is_draft = 1;
        $payment->original_id = $published_id;
        $payment->save();
        $payment = PagePayment::findById($draft_id);

        $payment_published = new PagePayment();
        $payment_published->id = $published_id;
        $payment_published->is_draft = 0;
        $payment_published->original_id = 0;
        $payment_published->save();
        $payment_published = PagePayment::findById($published_id);
    }
?>

    <input type="hidden" name="payment_id" value="<?= $payment->id ?? 0 ?>">
    <input type="hidden" name="payment_published_id" value="<?= $payment->original_id ?? 0 ?>">

    <!-- Доставка по СПБ -->
    <fieldset class="input_block">
        <legend>Доставка по Санкт-Петербургу</legend>
        
        <?= Form::input(
            'Заголовок',
            'payment_delivery_spb_title',
            $payment->delivery_spb_title ?? '',
            0,
            'text',
            '',
            ''
        ) ?>
        
        <?= Form::input(
            'Жирный текст',
            'payment_delivery_spb_nm',
            $payment->delivery_spb_nm ?? '',
            0,
            'text',
            '',
            ''
        ) ?>

        <?= Form::textarea(
            'Текст',
            'payment_delivery_spb_text',
            $payment->delivery_spb_text ?? '',
            100,
            ''
        ) ?>
        
        <?= Form::textarea(
            'Текст 2',
            'payment_delivery_spb_text2',
            $payment->delivery_spb_text2 ?? '',
            100,
            ''
        ) ?>
        
        <?= Form::textarea(
            'Текст на сером фоне',
            'payment_delivery_spb_text3',
            $payment->delivery_spb_text3 ?? '',
            80,
            ''
        ) ?>
    </fieldset>

    <!-- Доставка по России -->
    <fieldset class="input_block">
        <legend>Доставка по России</legend>
        
        <?= Form::input(
            'Заголовок',
            'payment_delivery_russia_title',
            $payment->delivery_russia_title ?? '',
            0,
            'text',
            '',
            ''
        ) ?>
        
        <?= Form::textarea(
            'Текст',
            'payment_delivery_russia_text',
            $payment->delivery_russia_text ?? '',
            100,
            ''
        ) ?>
    </fieldset>

    <!-- Доставка по СНГ -->
    <fieldset class="input_block">
        <legend>Доставка по СНГ</legend>
        
        <?= Form::input(
            'Заголовок',
            'payment_delivery_sng_title',
            $payment->delivery_sng_title ?? '',
            0,
            'text',
            '',
            ''
        ) ?>
        
        <?= Form::textarea(
            'Текст',
            'payment_delivery_sng_text',
            $payment->delivery_sng_text ?? '',
            100,
            ''
        ) ?>
    </fieldset>

    <!-- Службы доставки -->
    <fieldset class="input_block">
        <legend>Службы доставки</legend>

        <div id="items-container" class="sortbox">
            <?php
            // Получаем службы доставки для текущего черновика
            $items = PagePaymentServices::where("WHERE payment_id = ? ORDER BY rate DESC, id ASC", [$payment->id]);
            $itemIndex = 0;
            
            if (!empty($items)):  
                foreach ($items as $item):
            ?>
                <?= PagePaymentServices::itemCard($item->id, $itemIndex); ?>
            <?php  
                    $itemIndex++;
                endforeach;
            endif;
            ?>
        </div>
        
        <button type="button" class="btn btn_add btn_gray pagePaymentServiceAdd" data-index="<?= $itemIndex ?>">Добавить службу доставки</button>

    </fieldset>

    <!-- Блок про документы -->
    <fieldset class="input_block">
        <legend>Блок про документы</legend>

        <?= Form::input(
            'Заголовок',
            'payment_block_docs_title',
            $payment->block_docs_title ?? '',
            0,
            'text',
            '',
            ''
        ) ?>

        <div class="flex2">

            <div class="input_block">
            
                <?= Form::input(
                    'Жирный текст',
                    'payment_block_docs_nm',
                    $payment->block_docs_nm ?? '',
                    0,
                    'text',
                    '',
                    ''
                ) ?>

                <?= Form::textarea(
                    'Текст',
                    'payment_block_docs_text',
                    $payment->block_docs_text ?? '',
                    260,
                    ''
                ) ?>
            </div>
            <div class="input_block">
                
                <?= Form::input(
                    'Жирный текст 2',
                    'payment_block_docs_nm2',
                    $payment->block_docs_nm2 ?? '',
                    0,
                    'text',
                    '',
                    ''
                ) ?>

                <?= Form::textarea(
                'Текст 2',
                'payment_block_docs_text2',
                $payment->block_docs_text2 ?? '',
                260,
                ''
            ) ?>

            </div>
        </div>
                
        <?= Form::textarea(
            'Текст 3',
            'payment_block_docs_text3',
            $payment->block_docs_text3 ?? '',
            140,
            ''
        ) ?>
    </fieldset>

    <fieldset class="input_block">
        <div class="flex2">
            <?= Form::image(
                'Изображение (1350x740)',
                'payment_image',
                $payment,
                false,
                false,
                'image'
            ) ?>
            
            <?= Form::image(
                'Изображение 2 (1350x740)',
                'payment_image2',
                $payment,
                false,
                false,
                'image2'
            ) ?>
        </div>
    </fieldset>

    <!-- Калькулятор -->
    <fieldset class="input_block">
        <legend>Калькулятор доставки</legend>
        
        <?= Form::input(
            'Заголовок',
            'payment_calc_title',
            $payment->calc_title ?? '',
            0,
            'text',
            '',
            ''
        ) ?>
        
        <div class="flex2">
            <?= Form::textarea(
                'Текст',
                'payment_calc_text',
                $payment->calc_text ?? '',
                200,
                ''
            ) ?>
            
            <?= Form::textarea(
                'Текст 2',
                'payment_calc_text2',
                $payment->calc_text2 ?? '',
                200,
                ''
            ) ?>
        </div>
    </fieldset>

    <!-- Общий блок доставки -->
    <fieldset class="input_block">
        <legend>Общий блок доставки</legend>
        
            <?= Form::input(
                'Заголовок',
                'payment_delivery_title',
                $payment->delivery_title ?? '',
                0,
                'text',
                '',
                ''
            ) ?>
            
            <?= Form::textarea(
                'Текст',
                'payment_delivery_text',
                $payment->delivery_text ?? '',
                80,
                ''
            ) ?>
    </fieldset>

<?php
elseif (isset($_POST['edit'])):

    $payment_id = $_POST['payment_id'] ?? 1;
    $payment_published_id = $_POST['payment_published_id'] ?? 2;
    
    // Получаем или создаем объект
    $payment = PagePayment::findById($payment_id);
    if (!$payment) {
        $payment = new PagePayment();
        $payment->id = $payment_id;
    }
    
    // Заполняем данные из формы с уникальными именами
    $payment->delivery_spb_title = trim($_POST['payment_delivery_spb_title'] ?? '');
    $payment->delivery_spb_nm = trim($_POST['payment_delivery_spb_nm'] ?? '');
    $payment->delivery_spb_text = trim($_POST['payment_delivery_spb_text'] ?? '');
    $payment->delivery_spb_text2 = trim($_POST['payment_delivery_spb_text2'] ?? '');
    $payment->delivery_spb_text3 = trim($_POST['payment_delivery_spb_text3'] ?? '');
    
    $payment->delivery_russia_title = trim($_POST['payment_delivery_russia_title'] ?? '');
    $payment->delivery_russia_text = trim($_POST['payment_delivery_russia_text'] ?? '');
    
    $payment->delivery_sng_title = trim($_POST['payment_delivery_sng_title'] ?? '');
    $payment->delivery_sng_text = trim($_POST['payment_delivery_sng_text'] ?? '');
    
    $payment->block_docs_title = trim($_POST['payment_block_docs_title'] ?? '');
    $payment->block_docs_nm = trim($_POST['payment_block_docs_nm'] ?? '');
    $payment->block_docs_text = trim($_POST['payment_block_docs_text'] ?? '');
    $payment->block_docs_nm2 = trim($_POST['payment_block_docs_nm2'] ?? '');
    $payment->block_docs_text2 = trim($_POST['payment_block_docs_text2'] ?? '');
    $payment->block_docs_text3 = trim($_POST['payment_block_docs_text3'] ?? '');
    
    $payment->calc_title = trim($_POST['payment_calc_title'] ?? '');
    $payment->calc_text = trim($_POST['payment_calc_text'] ?? '');
    $payment->calc_text2 = trim($_POST['payment_calc_text2'] ?? '');
    
    $payment->delivery_title = trim($_POST['payment_delivery_title'] ?? '');
    $payment->delivery_text = trim($_POST['payment_delivery_text'] ?? '');
    
    // Системные поля
    $payment->edit_date = date("Y-m-d H:i:s");
    $payment->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
    $payment->is_draft = 1;
    
    if (!empty($payment_published_id) && $payment_published_id != $payment_id) {
        $payment->original_id = $payment_published_id;
    }
    
    // Сохраняем запись
    $payment->save();
    
    // ========== СОХРАНЕНИЕ СЛУЖБ ДОСТАВКИ ==========
    if (isset($_POST['payment_service']) && is_array($_POST['payment_service'])) {
        
        // Получаем существующие службы доставки
        $existingServices = PagePaymentServices::where("WHERE payment_id = ?", [$payment->id]);
        $existingIds = [];
        foreach ($existingServices as $service) {
            $existingIds[$service->id] = $service;
        }
        
        // Обрабатываем полученные из формы данные
        $processedIds = [];
        
        foreach ($_POST['payment_service'] as $index => $serviceData) {
            $serviceId = $serviceData['id'] ?? 0;
            $processedIds[] = $serviceId;
            
            // Получаем или создаем объект службы доставки
            if ($serviceId > 0 && isset($existingIds[$serviceId])) {
                $service = $existingIds[$serviceId];
            } else {
                $service = new PagePaymentServices();
                $service->payment_id = $payment->id;
            }
            
            // Заполняем данные
            $service->alt = trim($serviceData['alt'] ?? '');
            $service->show = (int)($serviceData['show'] ?? 1);
            $service->rate = (int)($serviceData['rate'] ?? $index);

            $service->edit_date = date("Y-m-d H:i:s");
            $service->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
            
            // Сохраняем службу доставки (нужен ID для загрузки изображения)
            $service->save();
            
            // Путь для загрузки изображений служб доставки
            $uploadPath = '/public/src/images/pages/page_payment_services/';
            
            // Загружаем изображение для службы доставки
            // Имя поля в $_FILES: payment_service[$index][image]
            $fileInputName = 'payment_service_' . $index . '_image';
            
            // Перестраиваем массив $_FILES для корректной обработки
            if (isset($_FILES['payment_service']['name'][$index]['image']) && 
                !empty($_FILES['payment_service']['name'][$index]['image'])) {
                
                // Создаем временную структуру FILES для этого файла
                $_FILES[$fileInputName] = [
                    'name' => $_FILES['payment_service']['name'][$index]['image'],
                    'type' => $_FILES['payment_service']['type'][$index]['image'],
                    'tmp_name' => $_FILES['payment_service']['tmp_name'][$index]['image'],
                    'error' => $_FILES['payment_service']['error'][$index]['image'],
                    'size' => $_FILES['payment_service']['size'][$index]['image']
                ];
                
                // Загружаем изображение
                FileUpload::uploadImage(
                    $fileInputName,
                    get_class($service),
                    'image',
                    $service->id,
                    0,  // исходный размер
                    0,
                    $uploadPath,
                    0
                );
                
                // Удаляем временную переменную
                unset($_FILES[$fileInputName]);
            }
        }
        
        // Удаляем службы доставки, которые не пришли в POST
        foreach ($existingIds as $id => $service) {
            if (!in_array($id, $processedIds)) {
                // Удаляем файл изображения
                if (!empty($service->image) && file_exists(ROOT . $service->image)) {
                    @unlink(ROOT . $service->image);
                }
                $service->delete();
            }
        }
    } else {
        // Если POST данных нет, удаляем все службы доставки
        $services = PagePaymentServices::where("WHERE payment_id = ?", [$payment->id]);
        foreach ($services as $service) {
            if (!empty($service->image) && file_exists(ROOT . $service->image)) {
                @unlink(ROOT . $service->image);
            }
            $service->delete();
        }
    }
    // ========== КОНЕЦ СОХРАНЕНИЯ СЛУЖБ ДОСТАВКИ ==========
    
    // Путь для загрузки изображений
    $uploadPath = '/public/src/images/pages/page_payment/';
        
    // Загружаем изображения с уникальными именами
    if (!empty($_FILES['payment_image']['name'][0])) {
        FileUpload::uploadImage(
            'payment_image',
            get_class($payment),
            'image',
            $payment->id,
            1350,
            740,
            $uploadPath,
            0
        );
    }
    
    if (!empty($_FILES['payment_image2']['name'][0])) {
        FileUpload::uploadImage(
            'payment_image2',
            get_class($payment),
            'image2',
            $payment->id,
            1350,
            740,
            $uploadPath,
            0
        );
    }

    // Если нажата кнопка "Опубликовать"
    if (!empty($_POST['publish']) && isset($useDrafts) && $useDrafts) {
        
        // Находим или создаем чистовик
        if (!empty($payment->original_id)) {
            $payment_published = PagePayment::findById($payment->original_id);
        } else {
            $payment_published = PagePayment::findById(2);
            if (!$payment_published) {
                $payment_published = new PagePayment();
                $payment_published->id = 2;
            }
        }
        
        // Копируем данные из черновика в чистовик
        $excludeFields = ['id', 'is_draft', 'original_id', 'image', 'image2'];
        $payment_published = PagePayment::copyData($payment, $payment_published, $excludeFields);
        
        $payment_published->is_draft = 0;
        $payment_published->original_id = 0;
        $payment_published->edit_date = date("Y-m-d H:i:s");
        $payment_published->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
        
        // Копируем изображения
        $imageFields = ['image', 'image2'];
        foreach ($imageFields as $field) {
            // Удаляем старые изображения чистовика
            if (!empty($payment_published->$field) && file_exists(ROOT . $payment_published->$field)) {
                @unlink(ROOT . $payment_published->$field);
                $payment_published->$field = '';
            }
            
            // Копируем новые из черновика
            if (!empty($payment->$field) && file_exists(ROOT . $payment->$field)) {
                $extension = pathinfo(ROOT . $payment->$field, PATHINFO_EXTENSION);
                $newFile = $uploadPath . uniqid() . '.' . $extension;
                if (copy(ROOT . $payment->$field, ROOT . $newFile)) {
                    $payment_published->$field = $newFile;
                }
            }
        }
        
        $payment_published->save();
        
        // ========== КОПИРУЕМ СЛУЖБЫ ДОСТАВКИ В ЧИСТОВИК ==========
        // Получаем службы доставки черновика
        $draftServices = PagePaymentServices::where("WHERE payment_id = ?", [$payment->id]);
        
        // Получаем существующие службы доставки чистовика
        $publishedServices = PagePaymentServices::where("WHERE payment_id = ?", [$payment_published->id]);
        
        // Удаляем старые службы доставки чистовика и их изображения
        foreach ($publishedServices as $service) {
            if (!empty($service->image) && file_exists(ROOT . $service->image)) {
                @unlink(ROOT . $service->image);
            }
            $service->delete();
        }
        
        // Копируем службы доставки из черновика в чистовик
        $servicesUploadPath = '/public/src/images/pages/page_payment_services/';
        
        foreach ($draftServices as $service) {
            $newService = new PagePaymentServices();
            $newService->payment_id = $payment_published->id;
            $newService->alt = $service->alt;
            $newService->show = $service->show;
            $newService->rate = $service->rate;
            $newService->save();
            
            // Копируем изображение
            if (!empty($service->image) && file_exists(ROOT . $service->image)) {
                $extension = pathinfo(ROOT . $service->image, PATHINFO_EXTENSION);
                $newFile = $servicesUploadPath . uniqid() . '.' . $extension;
                if (copy(ROOT . $service->image, ROOT . $newFile)) {
                    $newService->image = $newFile;
                    $newService->save();
                }
            }
        }
        // ========== КОНЕЦ КОПИРОВАНИЯ СЛУЖБ ДОСТАВКИ ==========
        
        // Обновляем связь черновика
        $payment->original_id = $payment_published->id;
        $payment->save();
    }

endif;
?>