<?= $this->include('layouts/header'); ?>

  <main>
    <section class="hero">
      <div class="hero__wrapper-block">
        <div>
          <h1 class="hero__big-title hero__big-title--alt"><?= $this->page->name ?></h1>
          <span class="hero__text">Любой сложности</span>
        </div>
        
        <? if(!empty($this->categories)): ?>
            <ul class="hero__list">
                <? foreach($this->categories AS $item): ?>
                    <? if($item->parent == 0): ?>
                        <li class="hero__item">
                            <a class="hero__link" href="catalog/<?= $item->url ?>"><?= $item->name ?></a>
                        </li>
                    <? endif; ?>
                <? endforeach; ?>
            </ul>
        <? endif; ?>



      </div>
      <img class="hero__img" src="<?= $this->page->image ?>">
      <img class="hero__img-mob" src="<?= $this->page->image2 ?>">
    </section>

    <section class="categories">
      <h2 class="visually-hidden">О компании</h2>
      <div class="categories__swiper swiper" id="swiper-categories">

        <? if(!empty($this->advantages)): ?>
          <ul class="categories__list swiper-wrapper">
            <? foreach($this->advantages AS $item): ?>
                  <li class="categories__item swiper-slide">
                    <img class="categories__item__img" src="<?= $item->image ?>">
                    <h3 class="categories__title title-small"><?= $item->name ?></h3>
                    <p class="categories__text"> <?= $item->text?></p>
                  </li>
            <? endforeach; ?>
          </ul>
        <? endif; ?>

        <div class="swiper-pagination swiper-pagination-alt swiper-pagination-categories"></div>
      </div>
    </section>

    <section class="catalog-card">
      <h2 class="catalog-card__title title">Популярные товары</h2>
      <div class="catalog-card__swiper swiper" id="swiper-catalog-card">
          <? if(!empty($this->popular_catalog)): ?>
            <ul class="catalog-card__list swiper-wrapper">
                <? foreach($this->popular_catalog AS $item): ?>
                    <? if($item->parent == 0): ?>
                        <li class="catalog-card__item swiper-slide swiper-slide-catalog-card"  data-category-id="<?= $item->category_id ?>">
                            <a href="/catalog/<?= $this->childs[0]->url ?? '' ?>/<?= $item->url ?>">                           
                                <div class="catalog-card__wrapper-img">
                                    <img class="catalog-card__img" src="<?= $item->image_preview ?>">
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
                    <? endif; ?>
                <? endforeach; ?>
            </ul>
        <? endif; ?>
        <!-- <ul class="catalog-card__list swiper-wrapper">
            <li class="catalog-card__item swiper-slide swiper-slide-catalog-card">
              <a href="/catalog-card.php">
                <span class="catalog-card__cta catalog-card__cta-action">Акция</span>
                <div class="catalog-card__wrapper-img">
                  <img class="catalog-card__img" src="/public/images/hit-1.png" >
                </div>

                <h3 class="catalog-card__title title-small">Контейнер <br> производства КГ 11</h3>
                <div class="catalog-card__wrapper-parameters">
                  <span class="catalog-card__name">Габаритные размеры:</span>
                  <span class="catalog-card__parameters">500х600х400 мм.</span>
                </div>
                <div class="catalog-card__wrapper-parameters">
                  <span class="catalog-card__name">Грузоподъемность:</span>
                  <span class="catalog-card__parameters">400 кг.</span>
                </div>
                <span class="catalog-card__sum">от 26 438 ₽</span>
              </a>
            </li>

            <li class="catalog-card__item swiper-slide swiper-slide-catalog-card">
              <span class="catalog-card__cta catalog-card__cta-hit">Хит</span>
              <div class="catalog-card__wrapper-img">
                <img class="catalog-card__img" src="/public/images/hit-2.png" >
              </div>

              <h3 class="catalog-card__title title-small">Ларь для белья <br> ЛДБ-1</h3>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Габаритные размеры:</span>
                <span class="catalog-card__parameters">800х580х650-800мм.</span>
              </div>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Толщина материала:</span>
                <span class="catalog-card__parameters">0.7-1 мм.</span>
              </div>
              <span class="catalog-card__sum">от 8 900 ₽</span>
            </li>

            <li class="catalog-card__item swiper-slide swiper-slide-catalog-card">
              <span class="catalog-card__cta catalog-card__cta-action">Акция</span>
              <div class="catalog-card__wrapper-img">
                <img class="catalog-card__img" src="/public/images/hit-3.png">
              </div>

              <h3 class="catalog-card__title title-small">Металлический <br> стеллаж М-18</h3>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Габаритные размеры:</span>
                <span class="catalog-card__parameters">600х400х1600 мм.</span>
              </div>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Материал полок:</span>
                <span class="catalog-card__parameters">Нержавеющая сталь</span>
              </div>
              <span class="catalog-card__sum">от 2 438 ₽</span>
            </li>

            <li class="catalog-card__item swiper-slide swiper-slide-catalog-card">
              <span class="catalog-card__cta catalog-card__cta-action">Акция</span>
              <div class="catalog-card__wrapper-img">
                <img class="catalog-card__img" src="/public/images/hit-4.png" >
              </div>

              <h3 class="catalog-card__title title-small">Металлические <br> двери 678-df12</h3>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Габаритные размеры:</span>
                <span class="catalog-card__parameters">1600х670х650-900мм.</span>
              </div>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Толщина материала:</span>
                <span class="catalog-card__parameters">0.5-4 мм.</span>
              </div>
              <span class="catalog-card__sum">от 56 438 ₽</span>
            </li>

            <li class="catalog-card__item swiper-slide swiper-slide-catalog-card">
              <span class="catalog-card__cta catalog-card__cta-action">Акция</span>
              <div class="catalog-card__wrapper-img">
                <img class="catalog-card__img" src="/public/images/hit-1.png" >
              </div>

              <h3 class="catalog-card__title title-small">Контейнер <br> производства КГ 11</h3>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Габаритные размеры:</span>
                <span class="catalog-card__parameters">500х600х400 мм.</span>
              </div>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Грузоподъемность:</span>
                <span class="catalog-card__parameters">400 кг.</span>
              </div>
              <span class="catalog-card__sum">от 26 438 ₽</span>
            </li>

            <li class="catalog-card__item swiper-slide swiper-slide-catalog-card">
              <span class="catalog-card__cta catalog-card__cta-hit">Хит</span>
              <div class="catalog-card__wrapper-img">
                <img class="catalog-card__img" src="/public/images/hit-2.png" >
              </div>

              <h3 class="catalog-card__title title-small">Ларь для белья <br> ЛДБ-1</h3>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Габаритные размеры:</span>
                <span class="catalog-card__parameters">800х580х650-800мм.</span>
              </div>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Толщина материала:</span>
                <span class="catalog-card__parameters">0.7-1 мм.</span>
              </div>
              <span class="catalog-card__sum">от 8 900 ₽</span>
            </li>

            <li class="catalog-card__item swiper-slide swiper-slide-catalog-card">
              <span class="catalog-card__cta catalog-card__cta-action">Акция</span>
              <div class="catalog-card__wrapper-img">
                <img class="catalog-card__img" src="/public/images/hit-3.png">
              </div>

              <h3 class="catalog-card__title title-small">Металлический <br> стеллаж М-18</h3>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Габаритные размеры:</span>
                <span class="catalog-card__parameters">600х400х1600 мм.</span>
              </div>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Материал полок:</span>
                <span class="catalog-card__parameters">Нержавеющая сталь</span>
              </div>
              <span class="catalog-card__sum">от 2 438 ₽</span>
            </li>

            <li class="catalog-card__item swiper-slide swiper-slide-catalog-card">
              <span class="catalog-card__cta catalog-card__cta-action">Акция</span>
              <div class="catalog-card__wrapper-img">
                <img class="catalog-card__img" src="/public/images/hit-4.png" >
              </div>

              <h3 class="catalog-card__title title-small">Металлические <br> двери 678-df12</h3>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Габаритные размеры:</span>
                <span class="catalog-card__parameters">1600х670х650-900мм.</span>
              </div>
              <div class="catalog-card__wrapper-parameters">
                <span class="catalog-card__name">Толщина материала:</span>
                <span class="catalog-card__parameters">0.5-4 мм.</span>
              </div>
              <span class="catalog-card__sum">от 56 438 ₽</span>
            </li>
        </ul> -->
        
        <div class="swiper-pagination swiper-pagination-alt swiper-pagination-catalog-card"></div>

        <div class="swiper-button-wrapper swiper-button-wrapper-alt swiper-button-wrapper-catalog-card">
          <div class="swiper-button-product swiper-button-next-product swiper-button-next"></div>
          <div class="swiper-button-product swiper-button-prev-product swiper-button-prev"></div>
        </div>

      </div>
      <a class="catalog-card__button-catalog filter" href="/catalog.php">Смотреть весь каталог</a>
    </section>

    <section class="form form--index">
      <div class="form__wrapper-text">
        <h2 class="form__title title">Изготовление металлоизделий на&nbsp;заказ</h2>
          <div class="form__text">   
            <?= $this->page->text ?>
          </div>
      </div>
      <form class="form__wrapper" id="feedbackForm">
          <h2 class="visually-hidden">Обратная связь</h2>
          <input type="hidden" name="type" value="1">
          <div class="form__group">
              <label for="name"></label>
              <input class="form__group-text" type="text" id="name" name="name"  placeholder="Ваше имя">
          </div>
          <div class="form__group">
              <label for="phone"></label>
              <input class="form__group-text" type="tel" id="phone" name="phone"  placeholder="Телефон">
          </div>
          <div class="form__group">
                <label for="mail"></label>
                <input class="form__group-text" type="email" id="mail" name="mail" required placeholder="E-mail*">
            </div>
          <div class="form__group form__group-question">
              <label for="question"></label>
              <textarea class="form__group-text form__group-text-area" id="question" name="question" placeholder="Ваше сообщение "></textarea>
          </div>

          <div class="form__checkbox-group form__checkbox-group-login">
              <input type="checkbox" id="agreement" name="agreement" required>
              <label for="agreement">
                  <p class="form__checkbox-group-text">Я принимаю условия <a href="/policy.php">Публичной оферты</a></p> 
              </label>
          </div>

          <div class="form__checkbox-group form__checkbox-group-login">
              <input type="checkbox" id="agreement-polity" name="agreement-polity" required>
              <label for="agreement-polity">
                  <p class="form__checkbox-group-text">Ознакомлен с  <a href="/policy.php">Политикой в отношении обработки персональных данных</a> и даю Согласие на их обработку и распространение</p>
              </label>
          </div>

          <button class="form__button button-dark" type="submit">Отправить</button>
      </form>

    </section>

    <section class="photo" id="galery">
      <h2 class="photo__title title">Фото выполненных <br> работ</h2>
        <div class="photo__swiper swiper" id="swiper-photo">
          <? if(!empty($this->gallery_works)): ?>
            <ul class="photo__list swiper-wrapper">
              <? foreach($this->gallery_works AS $item): ?>
                <li class="photo__item swiper-slide swiper-slide-photo">
                  <img src="<?= $item->image ?>">

                  <a class="photo__wrapper-text" href="/catalog-category.php">
                    <div>
                      <h3 class="photo__title-small title-small"><?= $item->name ?></h3>
                      <p class="photo_popular__name popular__name"><?= $item->text?></p>
                    </div>

                    <div class="photo__wrapper-parameters">
                      <div>
                        <span class="photo_popular__name popular__name"><?= $item->item1_name?></span>
                        <span class="photo__parameters"><?= $item->item1_text?></span>
                      </div>

                      <div>
                        <span class="photo_popular__name popular__name"><?= $item->item3_name?></span>
                        <span class="photo__parameters"><?= $item->item2_text?></span>
                      </div>
                    </div>
                  </a>
                </li>
              <? endforeach; ?>
            </ul>
          <? endif; ?>
          
          <div class="swiper-pagination swiper-pagination-photo swiper-pagination-alt"></div>

          <div class="swiper-button-wrapper swiper-button-wrapper-alt swiper-button-wrapper-popular">
              <div class="swiper-button-product swiper-button-next-product swiper-button-next"></div>
              <div class="swiper-button-product swiper-button-prev-product swiper-button-prev"></div>
          </div>
        </div>
    </section>

    <section class="scheme">
      <h2 class="scheme__title scheme__title-decorate title">Схема работы с нами</h2>
      
      <div class="scheme__swiper swiper" id="swiper-scheme">
        
      <? if(!empty($this->scheme_work)): ?>
          <ol class="scheme__list swiper-wrapper">
              <? foreach($this->scheme_work AS $key => $item): ?>
                  <li class="scheme__item swiper-slide">
                      <span class="scheme__number"><?= str_pad($key + 1, 2, '0', STR_PAD_LEFT) ?></span>
                      <div class="scheme__content">
                          <h3 class="scheme__item-title title-small"><?= $item->name ?></h3>
                          <p class="scheme__text"><?= $item->text?></p>
                      </div>
                  </li>
              <? endforeach; ?>
          </ol>
      <? endif; ?>
          
        <div class="swiper-pagination swiper-pagination-alt swiper-pagination-scheme"></div>
      </div>
    </section>

    <section class="choose" id="why">
      <div class="choose__block">
        <h2 class="choose__title title">Почему выбирают нас</h2>
      </div>
        <? if(!empty($this->why_choose_us)): ?>
          <ul class="choose__list">
            <? foreach($this->why_choose_us AS $item): ?>
              <li class="choose__item">
                <img class="choose__svg" src="<?= $item->image ?>">
                <h3 class="scheme__title title-small"><?= $item->name ?></h3>
                <p class="scheme__text"><?= $item->text?></p>
              </li>
            <? endforeach; ?>
          </ul>
        <? endif; ?>
    </section>

    <section class="partners" id="partners">
      <h2 class="partners__title title">Наши партнеры</h2>

      <div class="partners__swiper swiper" id="swiper-partners">

        <? if(!empty($this->partners)): ?>
          <ul class="partners__list swiper-wrapper">
            <? foreach($this->partners AS $item): ?>
              <li class="partners__item swiper-slide">
                <img src="<?= $item->image ?>">
              </li>
            <? endforeach; ?>
          </ul>
        <? endif; ?>

        <div class="swiper-pagination swiper-pagination-alt swiper-pagination-partners"></div>

        <div class="swiper-button-wrapper swiper-button-wrapper-alt swiper-button-wrapper-partners">
          <div class="swiper-button-product swiper-button-next-product swiper-button-next"></div>
          <div class="swiper-button-product swiper-button-prev-product swiper-button-prev"></div>
        </div>
      </div>
    </section>
  </main>

<?= $this->include('layouts/footer'); ?>