<?php
/**
 * Шаблон для множественной загрузки файлов
 * @var string $title Заголовок поля
 * @var string $name Имя поля
 * @var array $objects Массив объектов с файлами (опционально)
 * @var bool $required Обязательное поле
 * @var string $accept Разрешенные типы файлов
 */
?>

<fieldset class='input_block file_block'>
    
    <?php if(!empty($title)): ?>
        <legend><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>:</legend>
    <?php endif; ?>

    <input id='file_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
           type='file' 
           name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>[]' 
           multiple='true' 
           min='1' 
           max='999' 
           accept='<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>' 
           <?= $required ? 'required' : '' ?>>
    <label for='file_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>'>Выбрать файлы</label>

    <?php if (!empty($objects)): ?>
        <div class='files_block sortbox'>
            <?php foreach($objects as $object): ?>
                <div class='file_card'>
                    <div class='button handler tooltip-trigger' data-tooltip='Перетащите для сортировки'></div>
                    <input type='text' 
                       name='files_name[]' 
                       value='<?= $object->filename ?>'
                       data-tooltip='Название файла' 
                       class='file_name tooltip-trigger'>
                    <a class='button open tooltip-trigger' 
                       data-tooltip='Открыть' 
                       rel='external' 
                       href='<?= htmlspecialchars($object->filename, ENT_QUOTES, 'UTF-8') ?>'>
                    </a>
                    <button type='button' 
                            class='button show files_show tooltip-trigger<?= !empty($object->show)?' active':'' ?>' data-tooltip='Показывать на сайте'>
                        <input type='hidden' 
                           name='files_show[]' 
                           value='<?= (int)$object->show ?>'>
                    </button>
                    <button type='button' 
                        class='button delete files_delete tooltip-trigger' 
                        data-id='<?= (int)$object->id ?>'
                        data-className='<?= htmlspecialchars(get_class($object), ENT_QUOTES, 'UTF-8') ?>' 
                        data-field='file'
                        data-tooltip='Удалить'></button>
                    <input type='hidden' 
                       name='files_id[]' 
                       value='<?= (int)$object->id ?>'>
                    <input type='hidden' 
                       name='files_rate[]' 
                       class='rate' 
                       value='<?= (int)($object->rate ?? 0) ?>'>
                    <input type='hidden' 
                       name='files_del[]' 
                       id='file_del<?= (int)$object->id ?>' 
                       value='0' class='files_del'>
                </div>
           <?php endforeach; ?>
       </div>
    <?php endif; ?>
</fieldset>