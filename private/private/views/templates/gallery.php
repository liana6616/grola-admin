<?php
/**
 * Шаблон для галереи изображений
 * @var string $title Заголовок поля
 * @var string $name Имя поля
 * @var array $gallerys Массив объектов галереи (опционально)
 */
?>
<fieldset class='input_block image_block'>
    
    <?php if(!empty($title)): ?>
      <legend><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>:</legend>
    <?php endif; ?>

    <input id='gallery_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>' 
           type='file' 
           name='<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>[]' 
           multiple='true' 
           min='1' 
           max='999' 
           accept='image/jpeg,image/png,image/jpg'>
    <label for='gallery_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>'>Выбрать файлы</label>

      <?php if(!empty($gallerys)): ?>
          <div class='gallery_block sortbox'>
              <?php foreach($gallerys as $gallery): ?>

                  <div class='image_card'>
                      <div class='image_transparent'>
                          <img src='<?= htmlspecialchars($gallery->image_small ?? '', ENT_QUOTES, 'UTF-8') ?>' alt=''>
                      </div>
                      <div class='button_block'>
                          <div class='button handler tooltip-trigger' data-tooltip='Перетащите для сортировки'></div>
                          <div class='buttons'>
                              <a class='button open tooltip-trigger' 
                                 data-tooltip='Открыть' 
                                 rel='external' 
                                 href='<?= htmlspecialchars($gallery->image ?? '', ENT_QUOTES, 'UTF-8') ?>'></a>
                              <button type='button' 
                                      class='button show gallery_show tooltip-trigger<?= !empty($gallery->show)?' active':'' ?>' data-tooltip='Показывать на сайте'>
                                  <input type='hidden' 
                                     name='gallery_show[]' 
                                     value='<?= (int)$gallery->show ?>'>
                              </button>
                              <button type='button' 
                                      class='button delete gallery_delete tooltip-trigger' 
                                      data-id='<?= (int)$gallery->id ?>'
                                      data-className='<?= htmlspecialchars(get_class($gallery), ENT_QUOTES, 'UTF-8') ?>' 
                                      data-field='image'
                                      data-tooltip='Удалить'></button>
                          </div>

                          <input type='hidden' 
                                 name='gallery_id[]' 
                                 value='<?= (int)$gallery->id ?>'>
                          <input type='hidden' 
                                 name='gallery_rate[]' 
                                 class='rate' 
                                 value='<?= (int)($gallery->rate ?? 0) ?>'>
                          
                          <input type='hidden' 
                                 name='image_gallery_del[]' 
                                 id='image_gallery_del<?= (int)$gallery->id ?>' 
                                 value='0' class='gallery_del'>
                          
                      </div>
                      <input type='text' 
                         name='gallery_alt[]' 
                         class='alt tooltip-trigger' 
                         value='<?= htmlspecialchars($gallery->alt ?? '', ENT_QUOTES, 'UTF-8') ?>' 
                         placeholder='Название (Alt)' data-tooltip='Название (Alt)'>
                  </div>

              <?php endforeach; ?>
          </div>
      <?php endif; ?>
</fieldset>