<?= $this->include('layouts/header'); ?>

<main>
    <div class="card">
      <ul class="breadcrumps__wrapper">
        <li class="breadcrumps">
            <a href="index.php">Главная</a>
        </li>
        <li class="breadcrumps">
            <a href="">Каталог</a>
        </li>
        <li class="breadcrumps">
            <a href="">Для склада</a>
        </li>
        <li class="breadcrumps">
            <a href="">Поддоны и подставки для бочек металлические</a>
        </li>
        <li class="breadcrumps">
            <a href="">Контейнер КГ-04</a>
        </li>
      </ul>

      <div class="card__name-wrapper">
        <div class="swiper-wrapper-card-btn">
          <div class="swiper mySwiperCardMini">
              <div class="card__mini-swiper-wrapper swiper-wrapper">
                  <div class="card__mini-swiper-slide swiper-slide">
                    <img class="catalog-card__img" src="/public/images/catalog/card-1.png">
                  </div>
                  <div class="card__mini-swiper-slide swiper-slide">
                    <img class="catalog-card__img" src="/public/images/catalog/card-2.png">
                  </div>
                  <div class="card__mini-swiper-slide swiper-slide">
                    <img class="catalog-card__img" src="/public/images/catalog/card-3.png">
                  </div>
                  <div class="card__mini-swiper-slide swiper-slide">
                    <img class="catalog-card__img" src="/public/images/catalog/card-7.png">
                  </div>
                  <div class="card__mini-swiper-slide swiper-slide">
                    <img class="catalog-card__img" src="/public/images/catalog/card-10.png">
                  </div>
                  <div class="card__mini-swiper-slide swiper-slide">
                    <img class="catalog-card__img" src="/public/images/catalog/card-9.png">
                  </div>
              </div>


          </div>
          <div class="swiper-button-wrapper-card">
              <div class="swiper-button-next-card swiper-button-next-product swiper-button-next"></div>
              <div class="swiper-button-prev-card swiper-button-prev-product swiper-button-prev"></div>
          </div>
        </div>

        <div class="swiper mySwiperCard">
          <span class="catalog-card__cta catalog-card__cta-action">Акция</span>

          <div class="card-swiper swiper-wrapper">
              <div class="swiper-slide">
                <img class="catalog-card__img" src="/public/images/catalog/card-1.png">
              </div>
              <div class="swiper-slide">
                <img class="catalog-card__img" src="/public/images/catalog/card-2.png">
              </div>
              <div class="swiper-slide">
                <img class="catalog-card__img" src="/public/images/catalog/card-3.png">
              </div>
              <div class="swiper-slide">
                <img class="catalog-card__img" src="/public/images/catalog/card-7.png">
              </div>
              <div class="swiper-slide">
                <img class="catalog-card__img" src="/public/images/catalog/card-10.png">
              </div>
              <div class="swiper-slide">
                <img class="catalog-card__img" src="/public/images/catalog/card-9.png">
              </div>
          </div>
          <div class="swiper-button-wrapper-card swiper-button-wrapper-card-mob">
                <div class="swiper-button-next-card swiper-button-next-product swiper-button-next"></div>
                <div class="swiper-button-prev-card swiper-button-prev-product swiper-button-prev"></div>
          </div>
          <div class="swiper-pagination swiper-pagination-alt swiper-pagination-catalog-card"></div>

        </div>

        <div class="card__name-text">
            <span class="card__text-art">Артикул: 422790A</span>
            <h1 class="card__title">Контейнер КГ-04</h1>
            <span class="card__text-sum">от 12 900 ₽</span>

            <ul class="card__list">
              <li><span>—</span>   Габариты (ВШГ): 500х600х400 мм.</li>
              <li><span>—</span>    4 ячейки без дверей размером 500х600х400 мм каждая.</li>
              <li><span>—</span>    Металлический шкаф для противогазов на 4 ячейки</li>
              <li><span>—</span>    Аналог шкафа с дверьми: артикул <span class="card__art-text">036091</span>.</li>
              <li><span>—</span>    Шкаф для противогазов окрашен порошковой краской. </li>
              <li><span>—</span>    Металлический шкаф для противогазов.</li>
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
              <p class="tabs__text">Приобретая Контейнер КГ-04 c артикулом 422790A, вы получаете не только прочное и надежное решение для Ваших задач, но и гарантию долговечности и качества. Наш каждый товар в категории "Тара производственная и контейнеры" проходит тщательный технический контроль перед отгрузкой.
                  <br><br>
                  <span>Мы сертифицируем нашу продукцию и обеспечиваем каждое изделие паспортом. Мы готовы учесть ваши предпочтения и изменить габаритные размеры по вашему желанию.</span>
              </p>
          </div>
          <div id="specs" class="tabs__pane">
              <p class="tabs__text">Приобретая Контейнер КГ-04 c артикулом 422790A, вы получаете не только прочное и надежное решение для Ваших задач, но и гарантию долговечности и качества. Наш каждый товар в категории "Тара производственная и контейнеры" проходит тщательный технический контроль перед отгрузкой.
                  <br><br><br>
                  <span>Мы сертифицируем нашу продукцию и обеспечиваем каждое изделие паспортом. Мы готовы учесть ваши предпочтения и изменить габаритные размеры по вашему желанию.</span>
              </p>
          </div>
          <div id="docs" class="tabs__pane">
            <a class="tabs__pane-pdf" href="#">Макет (pdf)</a>
            <a class="tabs__pane-pdf" href="#">Паспорт (pdf)</a>
          </div>
      </div>
  </div>

    <div class="catalog-card">
      <h2 class="catalog-card__title title">Похожие товары</h2>
      <div class="catalog-card__swiper swiper" id="swiper-catalog-card">
          <ul class="catalog-card__list swiper-wrapper">
              <li class="catalog-card__item swiper-slide swiper-slide-catalog-card">
                <a href="/catalog-card.php">
                  <span class="catalog-card__cta catalog-card__cta-action">Акция</span>
                  <div class="catalog-card__wrapper-img">
                    <img class="catalog-card__img" src="/public/images/catalog/card-1.png">
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
      </div>
  </div>
</main>

<?= $this->include('layouts/footer'); ?>