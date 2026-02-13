<?php
/**
 * Шаблон для текстового редактора (WYSIWYG)
 * @var string $title Заголовок поля
 * @var string $name Имя поля
 * @var string $value Значение поля (HTML)
 * @var string $class CSS классы (не используется, оставлен для совместимости)
 */
?>
<div class='textarea_block'>
    <?php if (!empty($title)): ?>
        <span class='title'><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>:</span>
    <?php endif; ?>
    <textarea name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
          class='editor' 
          id='editor_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>'>
    <?= $value ?? '' ?>
</textarea>
</div>