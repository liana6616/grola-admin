<?php
/**
 * Шаблон для чекбокса
 * @var string $name Имя поля
 * @var string $id ID поля (опционально)
 * @var string $value Значение поля
 * @var bool $checked Состояние чекбокса
 * @var string $title Заголовок чекбокса
 * @var bool $disabled Отключить поле (НОВОЕ)
 */
?>
<div class='checkbox_block'>
    <input type='checkbox' 
           id='checkbox_<?= htmlspecialchars($id ?? $name, ENT_QUOTES, 'UTF-8') ?>' 
           name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>'
           value='<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>' 
           <?= $checked ? 'checked' : '' ?>
           <?= $disabled ? 'disabled' : '' ?>>
    <label for='checkbox_<?= htmlspecialchars($id ?? $name, ENT_QUOTES, 'UTF-8') ?>'>
        <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
    </label>
</div>