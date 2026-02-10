<?php
/**
 * Шаблон для загрузки изображения
 * @var string $title Заголовок поля
 * @var string $name Имя поля
 * @var object $object Объект с данными (опционально)
 * @var bool $required Обязательное поле
 */
?>
<fieldset class='input_block image_block'>
    
    <?php if(!empty($title)): ?>
        <legend><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>:</legend>
    <?php endif; ?>

    <input id='image_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
           type='file' 
           name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
           accept='image/jpeg,image/png,image/jpg' 
           <?= $required ? 'required' : '' ?>>
    <label for='image_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>'>Выбрать файл</label>

    <?php if (!empty($object->$name)): ?>
        <div class='image_card'>
            <div class='image_transparent'>
                <img src='<?= htmlspecialchars($object->$name ?? '', ENT_QUOTES, 'UTF-8') ?>' alt=''>
            </div>
            <div class='button_block'>
                    <a class='button open tooltip-trigger' 
                       data-tooltip='Открыть' 
                       rel='external' 
                       href='<?= htmlspecialchars($object->$name ?? '', ENT_QUOTES, 'UTF-8') ?>'></a>
                      <button type='button' 
                        class='button delete image_delete tooltip-trigger' 
                        data-id='<?= (int)$object->id ?>'
                        data-className='<?= htmlspecialchars(get_class($object), ENT_QUOTES, 'UTF-8') ?>' 
                        data-field='image'
                        data-tooltip='Удалить'></button>

                <input type='hidden' 
                       name='image_preview_del[]' 
                       class='image_preview_del' 
                       value='0'>
                <input type='hidden' 
                       name='image_preview_class[]' 
                       value='<?= htmlspecialchars(get_class($object), ENT_QUOTES, 'UTF-8') ?>'>
                <input type='hidden' 
                       name='image_preview_id[]' 
                       value='<?= (int)$object->id ?>'>
            </div>
        </div>
    <?php endif; ?>
</fieldset>