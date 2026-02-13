<?php
/**
 * Шаблон для многострочного текстового поля
 * @var string $title Заголовок поля (опционально)
 * @var string $name Имя поля
 * @var string $value Значение поля
 * @var int|null $height Высота поля в пикселях (опционально)
 * @var bool $disabled Отключить поле (НОВОЕ)
 * @var string $class CSS классы (опционально)
 */
?>
<div class='textarea_block'>
    <?php if (!empty($title)): ?>
        <span class='title'><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>:</span>
    <?php endif; ?>
    
    <textarea name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
          class='input <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8') ?>' 
          <?= $disabled ? 'disabled' : '' ?>
          style='<?= !empty($height) ? 'height: ' . (int)$height . 'px' : '' ?>'><?= htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
</div>