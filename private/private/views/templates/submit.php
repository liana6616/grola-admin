<?php
/**
 * Шаблон для кнопки отправки формы
 * @var string $buttonType Тип кнопки (add, edit, addGroup, editGroup, addItem, editItem)
 * @var string $id ID элемента
 * @var string $value Значение кнопки
 * @var string $text Текст кнопки
 * @var string $ids Дополнительный идентификатор
 */
?>
<div class='submit_block'>
    <button type='submit' 
            name='<?= $buttonType ?>' 
            value='<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>' 
            class='btn btn_red'>
        <?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>
    </button>
</div>