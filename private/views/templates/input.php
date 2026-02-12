<?php
/**
 * Шаблон для текстового поля ввода
 * @var string $title Заголовок поля (опционально)
 * @var string $name Имя поля
 * @var string|null $value Значение поля
 * @var bool $required Обязательное поле
 * @var string $type Тип поля (text, number, password и т.д.)
 * @var bool $disabled Отключить поле (было string $dis)
 * @var string $class CSS классы (опционально)
 */
?>
<div class='input_block'>
    <?php if(!empty($title)): ?>
        <span class='title'><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>:</span>
    <?php endif; ?>
    
    <input type='<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>' 
           name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
           value='<?= htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8') ?>'
           <?= $required ? 'required' : '' ?>
           <?= $disabled ? 'disabled' : '' ?>
           <?= !empty($class) ? 'class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
           autocomplete='<?= $name === 'password' ? 'new-password' : 'off' ?>' 
           maxlength='255'
           <?= $type === 'number' ? ' step=".01"' : '' ?>>
</div>