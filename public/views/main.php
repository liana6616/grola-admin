<?= $this->include('layouts/header'); ?>

  <main>
    <section class="hero">
      <div class="hero__wrapper-block">
        <div>
          <h1 class="hero__big-title hero__big-title--alt"><?= $this->page->name ?></h1>
          <span class="hero__text">Любой сложности</span>
        </div>
        <ul class="hero__list">
          <li class="hero__item">
            <a class="hero__link" href="#">Двери</a>
          </li>
          <li class="hero__item">
            <a class="hero__link" href="#">Шкафы металлические</a>
          </li>
          <li class="hero__item">
            <a class="hero__link" href="/catalog-category.php">Для склада</a>
          </li>
          <li class="hero__item">
            <a class="hero__link" href="#">Для производства</a>
          </li>
          <li class="hero__item">
            <a class="hero__link" href="#">Индивидуальные проекты из металла</a>
          </li>
        </ul>
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
<!--          <li class="categories__item swiper-slide">-->
<!--            <h3 class="categories__title title-small">В сжатые  <br>  сроки</h3>-->
<!--            <p class="categories__text">От чертежа до отгрузки — без&nbsp;задержек и посредников</p>-->
<!--          </li>-->
<!--          <li class="categories__item swiper-slide">-->
<!--            <h3 class="categories__title title-small">Прочность <br> и долговечность</h3>-->
<!--            <p class="categories__text">Изделия, рассчитанные на&nbsp;интенсивную эксплуатацию</p>-->
<!--          </li>-->
<!--          <li class="categories__item swiper-slide">-->
<!--            <h3 class="categories__title title-small">Индивидуальный <br> подход</h3>-->
<!--            <p class="categories__text">Работаем как с типовыми, так и&nbsp;с&nbsp;эксклюзивными проектами</p>-->
<!--          </li>-->
<!--          <li class="categories__item swiper-slide">-->
<!--            <h3 class="categories__title title-small">Технические <br> инновации</h3>-->
<!--            <p class="categories__text">Развитие технологий в&nbsp;области&nbsp;металлов</p>-->
<!--          </li>-->
<!--        </ul>-->




        <div class="swiper-pagination swiper-pagination-alt swiper-pagination-categories"></div>
      </div>
    </section>

    <section class="catalog-card">
      <h2 class="catalog-card__title title">Популярные товары</h2>
      <div class="catalog-card__swiper swiper" id="swiper-catalog-card">
        <ul class="catalog-card__list swiper-wrapper">
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
        </ul>
        
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

        <p class="form__text">Приобретая Ларь для овощей ЛО-1 Если готовые <br> решения не подходят — мы создадим идеальное. <br> Берёмся за проекты любой сложности: от архитектурных элементов до промышленного оборудования
          <span>Работаем по вашим чертежам или разрабатываем их сами. Подберём материалы и технологию, чтобы воплотить вашу идею в прочное и функциональное изделие.</span>
        </p>
      </div>
      <form class="form__wrapper" id="feedbackForm">
          <h2 class="visually-hidden">Обратная связь</h2>
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
                <input class="form__group-text" type="mail" id="mail" name="mail" required placeholder="E-mail*">
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

          <button class="form__button button-dark" type="submit" class="submit-btn">Отправить</button>
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
          <!-- <ul class="photo__list swiper-wrapper">
            <li class="photo__item swiper-slide swiper-slide-photo">
              <img src="/public/images/photo-1.jpg" width="470" height="505">

              <a class="photo__wrapper-text" href="/catalog-category.php">
                <div>
                  <h3 class="photo__title-small title-small">Ларь для овощей ЛО-1</h3>
                  <p class="photo_popular__name popular__name">Приобретая Ларь для овощей ЛО-1
                    c артикулом 5070, вы получаете не только прочное и надежное решение для Ваших задач, но и гарантию долговечности и качества.
                  </p>
                </div>

                <div class="photo__wrapper-parameters">
                  <div>
                    <span class="photo_popular__name popular__name">Срок выполнения:</span>
                    <span class="photo__parameters">3 дня</span>
                  </div>

                  <div>
                    <span class="photo_popular__name popular__name">Габаритные размеры:</span>
                    <span class="photo__parameters">500х600х400 мм.</span>
                  </div>
                </div>
              </a>
            </li>
            <li class="photo__item swiper-slide swiper-slide-photo">
              <img src="/public/images/photo-2.jpg" width="470" height="505">

              <a class="photo__wrapper-text" href="#">
                <div>
                  <h3 class="photo__title-small title-small">Ларь для овощей ЛО-1</h3>
                  <p class="photo_popular__name popular__name">Приобретая Ларь для овощей ЛО-1
                    c артикулом 5070, вы получаете не только прочное и надежное решение для Ваших задач, но и гарантию долговечности и качества.
                  </p>
                </div>

                <div class="photo__wrapper-parameters">
                  <div>
                    <span class="photo_popular__name popular__name">Срок выполнения:</span>
                    <span class="photo__parameters">3 дня</span>
                  </div>

                  <div>
                    <span class="photo_popular__name popular__name">Габаритные размеры:</span>
                    <span class="photo__parameters">500х600х400 мм.</span>
                  </div>
                </div>
              </a>
            </li>
            <li class="photo__item swiper-slide swiper-slide-photo">
              <img src="/public/images/photo-3.jpg" width="470" height="505">

              <a class="photo__wrapper-text" href="#">
                <div>
                  <h3 class="photo__title-small title-small">Ларь для овощей ЛО-1</h3>
                  <p class="photo_popular__name popular__name">Приобретая Ларь для овощей ЛО-1
                    c артикулом 5070, вы получаете не только прочное и надежное решение для Ваших задач, но и гарантию долговечности и качества.
                  </p>
                </div>

                <div class="photo__wrapper-parameters">
                  <div>
                    <span class="photo_popular__name popular__name">Срок выполнения:</span>
                    <span class="photo__parameters">3 дня</span>
                  </div>

                  <div>
                    <span class="photo_popular__name popular__name">Габаритные размеры:</span>
                    <span class="photo__parameters">500х600х400 мм.</span>
                  </div>
                </div>
              </a>
            </li>
            <li class="photo__item swiper-slide swiper-slide-photo">
              <img src="/public/images/photo-4.jpg" width="470" height="505">

              <a class="photo__wrapper-text" href="#">
                <div>
                  <h3 class="photo__title-small title-small">Ларь для овощей ЛО-1</h3>
                  <p class="photo_popular__name popular__name">Приобретая Ларь для овощей ЛО-1
                    c артикулом 5070, вы получаете не только прочное и надежное решение для Ваших задач, но и гарантию долговечности и качества.
                  </p>
                </div>

                <div class="photo__wrapper-parameters">
                  <div>
                    <span class="photo_popular__name popular__name">Срок выполнения:</span>
                    <span class="photo__parameters">3 дня</span>
                  </div>

                  <div>
                    <span class="photo_popular__name popular__name">Габаритные размеры:</span>
                    <span class="photo__parameters">500х600х400 мм.</span>
                  </div>
                </div>
              </a>
            </li>
            <li class="photo__item swiper-slide swiper-slide-photo">
              <img src="/public/images/photo-1.jpg" width="470" height="505">

              <a class="photo__wrapper-text" href="#">
                <div>
                  <h3 class="photo__title-small title-small">Ларь для овощей ЛО-1</h3>
                  <p class="photo_popular__name popular__name">Приобретая Ларь для овощей ЛО-1
                    c артикулом 5070, вы получаете не только прочное и надежное решение для Ваших задач, но и гарантию долговечности и качества.
                  </p>
                </div>

                <div class="photo__wrapper-parameters">
                  <div>
                    <span class="photo_popular__name popular__name">Срок выполнения:</span>
                    <span class="photo__parameters">3 дня</span>
                  </div>

                  <div>
                    <span class="photo_popular__name popular__name">Габаритные размеры:</span>
                    <span class="photo__parameters">500х600х400 мм.</span>
                  </div>
                </div>
              </a>
            </li>
          </ul> -->
          
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
            <? foreach($this->scheme_work AS $item): ?>
              <li class="scheme__item swiper-slide">
                <span class="scheme__number">01</span>
                <div class="scheme__content">
                  <h3 class="scheme__item-title title-small"><?= $item->name ?></h3>
                  <p class="scheme__text"><?= $item->text?></p>
                </div>
              </li>
            <? endforeach; ?>
          </ul>
        <? endif; ?>
        <!-- <ol class="scheme__list swiper-wrapper">
          <li class="scheme__item swiper-slide">
            <span class="scheme__number">01</span>
            <div class="scheme__content">
              <h3 class="scheme__item-title title-small">Заявка</h3>
              <p class="scheme__text">Вы оставляете заявку по телефону, лично или на сайте, менеджер уточняет с вами детали и удобное время встречи</p>
            </div>
          </li>
          
          <li class="scheme__item swiper-slide">
            <span class="scheme__number">02</span>
            <div class="scheme__content">
              <h3 class="scheme__item-title title-small">Замер</h3>
              <p class="scheme__text">К вам приезжает наш мастер, делает <br> все необходимые расчеты, показывает образцы и каталог моделей</p>
            </div>
          </li>
          
          <li class="scheme__item swiper-slide">
            <span class="scheme__number">03</span>
            <div class="scheme__content">
              <h3 class="scheme__item-title title-small">Договор</h3>
              <p class="scheme__text">Заключаем договор, в котором четко прописаны все сроки, цены и условия</p>
            </div>
          </li>
          
          <li class="scheme__item swiper-slide">
            <span class="scheme__number">04</span>
            <div class="scheme__content">
              <h3 class="scheme__item-title title-small">Изготовление</h3>
              <p class="scheme__text">Мы изготавливаем дверь без предоплаты на собственном производстве, устанавливаем необходимые комплектующие</p>
            </div>
          </li>
          
          <li class="scheme__item swiper-slide">
            <span class="scheme__number">05</span>
            <div class="scheme__content">
              <h3 class="scheme__item-title title-small">Доставка</h3>
              <p class="scheme__text">В согласованный срок доставляем <br> вашу дверь собственным <br> транспортом и производим <br> профессиональный монтаж</p>
            </div>
          </li>
          
          <li class="scheme__item swiper-slide">
            <span class="scheme__number">06</span>
            <div class="scheme__content">
              <h3 class="scheme__item-title title-small">Оплата</h3>
              <p class="scheme__text">Вы платите только после того, как принимаете все работы</p>
            </div>
          </li>
        </ol> -->
          
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
      <!-- <ul class="choose__list">
        <li class="choose__item">
          <div class="choose__svg">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10.3004 10.9149L10.3004 6.46404L12.9468 6.48339C12.9468 6.48339 13.051 11.089 12.9677 11.4954C12.8843 11.9018 12.8427 12.8113 11.8007 12.9274C10.7588 13.0435 9.77944 12.9855 9.77944 12.9855C9.77944 12.9855 9.82112 11.7212 9.80028 10.9149H10.3004Z" fill="white" />
              <path d="M12.5718 2.0519L12.6135 0.0199911C12.6135 0.0199911 5.13258 -0.0767659 3.02792 0.155451C0.923264 0.387669 0.402272 2.7292 0.193911 3.38715C-0.0144504 4.0451 -0.118624 8.36048 0.214775 9.46352C0.548175 10.5666 1.54838 12.2501 3.19461 12.6759C4.84084 13.1016 9.1335 12.9855 9.1335 12.9855L9.15434 10.9149C9.15434 10.9149 5.73688 10.9536 4.61162 10.8375C3.48636 10.7214 2.757 9.59898 2.5903 9.11519C2.42359 8.6314 2.4236 5.74803 2.52779 4.78045C2.63198 3.81288 2.96541 2.40022 3.92396 2.18736C4.88252 1.97449 12.5718 2.0519 12.5718 2.0519Z" fill="white" />
            </svg>
          </div>
          <h3 class="scheme__title title-small">Индивидуальный <br> проект</h3>
          <p class="scheme__text">Никакой шаблонной штамповки: изготавливаем двери Соблюдаем строительные нормы и подгоняем дверь под проем с точностью до сантиметров.</p>
        </li>
        <li class="choose__item">
          <div class="choose__svg">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10.3004 10.9149L10.3004 6.46404L12.9468 6.48339C12.9468 6.48339 13.051 11.089 12.9677 11.4954C12.8843 11.9018 12.8427 12.8113 11.8007 12.9274C10.7588 13.0435 9.77944 12.9855 9.77944 12.9855C9.77944 12.9855 9.82112 11.7212 9.80028 10.9149H10.3004Z" fill="white" />
              <path d="M12.5718 2.0519L12.6135 0.0199911C12.6135 0.0199911 5.13258 -0.0767659 3.02792 0.155451C0.923264 0.387669 0.402272 2.7292 0.193911 3.38715C-0.0144504 4.0451 -0.118624 8.36048 0.214775 9.46352C0.548175 10.5666 1.54838 12.2501 3.19461 12.6759C4.84084 13.1016 9.1335 12.9855 9.1335 12.9855L9.15434 10.9149C9.15434 10.9149 5.73688 10.9536 4.61162 10.8375C3.48636 10.7214 2.757 9.59898 2.5903 9.11519C2.42359 8.6314 2.4236 5.74803 2.52779 4.78045C2.63198 3.81288 2.96541 2.40022 3.92396 2.18736C4.88252 1.97449 12.5718 2.0519 12.5718 2.0519Z" fill="white" />
            </svg>
          </div>
          <h3 class="scheme__title title-small">Высокая <br> взломостойкость</h3>
          <p class="scheme__text">Изготавливаем по-настоящему <br> прочные двери , которые <br> не режутся киличным ножом и не вскрываются отмычками. </p>
        </li>
        <li class="choose__item">
          <div class="choose__svg">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10.3004 10.9149L10.3004 6.46404L12.9468 6.48339C12.9468 6.48339 13.051 11.089 12.9677 11.4954C12.8843 11.9018 12.8427 12.8113 11.8007 12.9274C10.7588 13.0435 9.77944 12.9855 9.77944 12.9855C9.77944 12.9855 9.82112 11.7212 9.80028 10.9149H10.3004Z" fill="white" />
              <path d="M12.5718 2.0519L12.6135 0.0199911C12.6135 0.0199911 5.13258 -0.0767659 3.02792 0.155451C0.923264 0.387669 0.402272 2.7292 0.193911 3.38715C-0.0144504 4.0451 -0.118624 8.36048 0.214775 9.46352C0.548175 10.5666 1.54838 12.2501 3.19461 12.6759C4.84084 13.1016 9.1335 12.9855 9.1335 12.9855L9.15434 10.9149C9.15434 10.9149 5.73688 10.9536 4.61162 10.8375C3.48636 10.7214 2.757 9.59898 2.5903 9.11519C2.42359 8.6314 2.4236 5.74803 2.52779 4.78045C2.63198 3.81288 2.96541 2.40022 3.92396 2.18736C4.88252 1.97449 12.5718 2.0519 12.5718 2.0519Z" fill="white" />
            </svg>
          </div>
          <h3 class="scheme__title title-small">Заботливый <br> сервис</h3>
          <p class="scheme__text">Работаем 24/7  и решаем любые проблемы с дверью. Не берем предоплату и выезжаем на замер.</p>
        </li>
      </ul> -->
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