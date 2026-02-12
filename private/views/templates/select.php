<?php
/**
 * Шаблон для выпадающего списка
 * @var string $title Заголовок поля (опционально)
 * @var string $name Имя поля
 * @var array|object $object Данные для списка
 * @var int|null $selectedId Выбранный ID
 * @var bool $null Добавить пустой элемент
 * @var string $nullTitle Заголовок пустого элемента
 * @var string $fieldName Имя поля для отображения
 * @var int $no_obj Тип отображения объектов
 * @var string $class CSS классы
 * @var int $data_id ID данных (для data-атрибута)
 * @var string $form_id ID формы (опционально)
 * @var bool $disabled Отключить поле (НОВОЕ)
 */
?>
<div class='input_block <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8') ?>'>
    
    <?php if(!empty($title)): ?>
        <span class='title'><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>:</span>
    <?php endif; ?>

    <select name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
            id='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
            class='chosen' 
            data-id='<?= (int)$data_id ?>' 
            <?= !empty($form_id) ? 'form="' . htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
            <?= $disabled ? 'disabled' : '' ?>>
        
        <?php if ($null): ?>
            <option value='0'><?= htmlspecialchars($nullTitle, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endif; ?>

        <?php if (!empty($object) && empty($no_obj)): ?>
            <?php foreach ($object as $item): ?>
                <option value='<?= (int)$item->id ?>' <?= $selectedId == $item->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item->$fieldName ?? '', ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        
        <?php elseif($no_obj == 1): ?>
            <?php foreach ($object as $item): ?>
                <option value='<?= (int)$item->id ?>' <?= $selectedId == $item->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item->surname ?? '', ENT_QUOTES, 'UTF-8') ?>&nbsp;
                    <?= htmlspecialchars($item->name ?? '', ENT_QUOTES, 'UTF-8') ?>&nbsp;
                    <?= htmlspecialchars($item->patronymic ?? '', ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        
        <?php elseif($no_obj == 2): ?>
            <?php foreach ($object as $key => $item): ?>
                <option value='<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>' <?= $selectedId == $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>