<?php
/**
 * Шаблон для множественного выбора чекбоксами
 * @var string $title Заголовок поля
 * @var string $name Имя поля
 * @var array $object Массив объектов для выбора
 * @var string $value Строка с выбранными ID через '|'
 * @var string $info Дополнительная информация (не используется)
 */
?>
<fieldset class='input_block multiple'>
    
    <?php if(!empty($title)): ?>
        <legend><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>:</legend>
    <?php endif; ?>

    <?php
    // Разбиваем строку с выбранными значениями
    $selectedIds = !empty($value) ? explode('|', trim($value, '|')) : [];
    ?>

    <?php foreach ($object as $item): ?>
        <?php
        $itemId = 'multiple_checkbox_' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '_' . (int)$item->id;
        $checked = in_array($item->id, $selectedIds) ? 'checked="checked"' : '';
        $parentClass = !empty($item->parent) ? 'checkbox_child' : '';
        $parentClass .= !empty($item->parent_main) ? ' checkbox_child2' : '';
        ?>

        <div class='checkbox_block <?= $parentClass ?>'>
            <input class="multiple-checkbox" 
                   type='checkbox'
                   id='<?= $itemId ?>' 
                   name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>[]'
                   value='<?= (int)$item->id ?>' 
                   <?= $checked ?>
                   data-id="<?= (int)$item->id ?>" 
                   data-parent="<?= (int)($item->parent ?? 0) ?>" 
                   data-parent_main="<?= (int)($item->parent_main ?? 0) ?>">
            <label for='<?= $itemId ?>'>
                ID: <?= (int)$item->id ?> | <?= htmlspecialchars($item->name ?? '', ENT_QUOTES, 'UTF-8') ?>
            </label>
        </div>
    <?php endforeach; ?>
</fieldset>