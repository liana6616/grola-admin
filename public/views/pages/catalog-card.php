<?= $this->include('layouts/header'); ?>

<main>
    <div class="card">
        <?= $this->include('components/breadCrumbs'); ?>

      

      <div class="card__name-wrapper">
        <div class="swiper-wrapper-card-btn">
          <div class="swiper mySwiperCardMini">
            
          <?
          if(!empty($this->gallery)): ?>
              <div class="card__mini-swiper-wrapper swiper-wrapper">
                  <? foreach($this->gallery AS $item): ?>
                      <? if(!empty($item->image)): ?>
                          <div class="card__mini-swiper-slide swiper-slide">
                              <img class="catalog-card__img" 
                                  src="<?= htmlspecialchars($item->image) ?>" 
                                  alt="<?= htmlspecialchars($item->alt ?? '') ?>">
                          </div>
                      <? endif; ?>
                  <? endforeach; ?>
              </div>
          <? endif; ?>

          </div>
          <div class="swiper-button-wrapper-card">
              <div class="swiper-button-next-card swiper-button-next-product swiper-button-next"></div>
              <div class="swiper-button-prev-card swiper-button-prev-product swiper-button-prev"></div>
          </div>
        </div>

        <div class="swiper mySwiperCard">
          <span class="catalog-card__cta catalog-card__cta-action">Акция</span>


          <? if(!empty($this->gallery)): ?>
              <div class="card-swiper swiper-wrapper">
                  <? foreach($this->gallery AS $item): ?>
                      <div class="swiper-slide">
                          <img class="catalog-card__img"  src="<?= htmlspecialchars($item->image) ?>">
                      </div>
                  <? endforeach; ?>
              </div>
          <? endif; ?>

          <div class="swiper-button-wrapper-card swiper-button-wrapper-card-mob">
                <div class="swiper-button-next-card swiper-button-next-product swiper-button-next"></div>
                <div class="swiper-button-prev-card swiper-button-prev-product swiper-button-prev"></div>
          </div>
          <div class="swiper-pagination swiper-pagination-alt swiper-pagination-catalog-card"></div>

        </div>

        <div class="card__name-text">
          
            <?php
              $paramValue = \app\Models\CatalogParams::findWhere("WHERE catalog_id = " . $this->product->id);
              $articleValue = '';
              foreach($paramValue AS $catalogParam):
                  $paramName = \app\Models\Params::findById($catalogParam->param_id);
                  if($paramName->name == 'Артикул:'):
                      $articleValue = $catalogParam->value;
                      break;
                  endif;
              endforeach;
            ?>
            <span class="card__text-art">Артикул <?= $articleValue ?></span>
            <h1 class="card__title"><?= htmlspecialchars($this->product->name ?? '') ?></h1>
            <span class="card__text-sum">от <?= htmlspecialchars($this->product->price ?? '') ?> ₽</span>

            <ul class="card__list">
              <li><?= $this->product->textshort ?? '' ?></li>
            </ul>

            <button class="card__button-submit button-dark" type="button" onclick="openModalWithArticle()">
                Оформить заказ
            </button>
        </div>
      </div>
    </div>

    <div class="tabs">
      <div class="tabs__nav">
          <button class="tabs__btn active" type="button" onclick="showTab('about', this)">О товаре</button>
          <button class="tabs__btn" type="button" onclick="showTab('specs', this)">Характеристики</button>
          <button class="tabs__btn" type="button" onclick="showTab('docs', this)">Документация</button>
      </div>

      <div class="tabs__content">
        <div id="about" class="tabs__pane active">
            <div class="tabs__text"><?= nl2br($this->product->text ?? '') ?></div>
        </div>
        <div id="specs" class="tabs__pane">
            <div class="tabs__text"><?= nl2br($this->product->text2 ?? '') ?></div>
        </div>


      <? if(!empty($this->file)): ?>
          <div id="docs" class="tabs__pane">
              <? foreach($this->file AS $item): ?>
                  <? 
                  if(isset($item->ids) && $item->ids == $this->product->id 
                    && isset($item->type) && $item->type == 'catalog'): 
                  ?>
                      <a class="tabs__pane-pdf" href="<?= htmlspecialchars($item->file) ?>" target="_blank">
                          <?= htmlspecialchars($item->filename ?: 'Документ') ?>
                      </a>
                      <br>
                  <? endif; ?>
              <? endforeach; ?>
          </div>
      <? endif; ?>
    </div>
  </div>

<section class="catalog-card">
    <h2 class="catalog-card__title title">Похожие товары</h2>
    <div class="catalog-card__swiper swiper" id="swiper-catalog-card">
        <?php if(!empty($this->similar_products)): ?>
          <ul class="catalog-card__list swiper-wrapper">
              <?php foreach($this->similar_products AS $item): ?>
                  <li class="catalog-card__item swiper-slide swiper-slide-catalog-card" data-category-id="<?= $item->category_id ?>">
                      <a href="/catalog/<?= $this->childs[0]->url ?? '' ?>/<?= $item->url ?>">  
                          <?php 
                          if($item->action == 1): ?>
                              <span class="catalog-card__cta catalog-card__cta-action">Акция</span>
                          <?php elseif($item->popular == 1): ?>
                              <span class="catalog-card__cta catalog-card__cta-hit">Хит</span>
                          <?php elseif($item->new == 1): ?>
                              <span class="catalog-card__cta catalog-card__cta-new">Новинка</span>
                          <?php endif; ?>                           
                          <div class="catalog-card__wrapper-img">
                              <img class="catalog-card__img" src="<?= $item->image_preview ?>" alt="<?= htmlspecialchars($item->name) ?>">
                          </div>

                            <h3 class="catalog-card__title title-small"><?= $item->name ?></h3>

                            <div class="catalog-card__wrapper-parameters">
                                <span class="catalog-card__name">Габаритные размеры:</span>
                                <span class="catalog-card__parameters">500х600х400 мм.</span>
                            </div>
                            <div class="catalog-card__wrapper-parameters">
                                <span class="catalog-card__name">Грузоподъемность:</span>
                                <span class="catalog-card__parameters">400 кг.</span>
                            </div>

                            <span class="catalog-card__sum">от <?= $item->price ?> ₽</span>
                      </a>
                  </li>
              <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="no-products">Нет похожих товаров</p>
        <?php endif; ?>
      
      <div class="swiper-pagination swiper-pagination-alt swiper-pagination-catalog-card"></div>

      <div class="swiper-button-wrapper swiper-button-wrapper-alt swiper-button-wrapper-catalog-card">
        <div class="swiper-button-product swiper-button-next-product swiper-button-next"></div>
        <div class="swiper-button-product swiper-button-prev-product swiper-button-prev"></div>
      </div>

    </div>
    <a class="catalog-card__button-catalog filter" href="/catalog">Смотреть весь каталог</a>
</section>
</main>

<?= $this->include('layouts/footer'); ?>