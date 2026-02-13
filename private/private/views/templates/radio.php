<?php
/**
 * Шаблон для радиокнопок
 * @var string $title Заголовок поля
 * @var string $name Имя поля
 * @var array $items Массив элементов [значение => метка]
 * @var string|null $checked Выбранное значение
 * @var string $class Дополнительные CSS классы
 * @var bool $horizontal Горизонтальное расположение
 * @var string $size Размер (small, normal, large)
 * @var bool $disabled Отключить поле (НОВОЕ)
 */
$radioClasses = 'radio_block';
if (!empty($class)) {
    $radioClasses .= ' ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
}
if (isset($horizontal) && $horizontal) {
    $radioClasses .= ' horizontal';
}
if (!empty($size) && in_array($size, ['small', 'large'])) {
    $radioClasses .= ' ' . $size;
}
?>
<div class='input_block'>

    <?php if(!empty($title)): ?>
        <span class='title'><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>:</span>
    <?php endif; ?>
    
    <div class='<?= $radioClasses ?>'>
        <?php 
        $i = 1;
        foreach ($items as $value => $label): ?>
            <input type='radio' 
                   name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
                   id='radio_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>_<?= $i ?>'
                   value='<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>' 
                   <?= $value == $checked ? 'checked' : '' ?>
                   <?= $disabled ? 'disabled' : '' ?>>
            <label for='radio_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>_<?= $i ?>'>
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            </label>
            <?php 
            $i++;
        endforeach; ?>
    </div>
</div>