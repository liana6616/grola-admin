<?= $this->include('layouts/header'); ?>

    <main>
        <section class="hero-about">
            <div class="hero-about__wrapper-block">
                <ul class="breadcrumps__wrapper">
                    <li class="breadcrumps">
                        <a href="index.php">Главная</a>
                    </li>
                    <li class="breadcrumps">
                        <span><?= $this->page->name ?></span>
                    </li>
                </ul>

                <h1 class="hero__big-title"><?= $this->page->name ?></h1>

                <? if(!empty($this->key_indicators)): ?>
                    <ul class="hero__list-about">
                        <? foreach($this->key_indicators AS $item): ?>
                            <li class="hero__item-about">
                                <span class="hero-about__sum"><?= $item->value ?></span>
                                <span class="hero-about__text"><?= $item->text?></span>
                            </li>
                        <? endforeach; ?>
                    </ul>
                <? endif; ?>
                <!-- <ul class="hero__list-about">
                    <li class="hero__item-about">
                        <span class="hero-about__sum">250</span>
                        <span class="hero-about__text">Специалистов высочайшей <br> квалификации</span>
                    </li>
                    <li class="hero__item-about">
                        <span class="hero-about__sum">12 000</span>
                        <span class="hero-about__text">Тонн перерабатываемого <br> металла в год</span>
                    </li>
                    <li class="hero__item-about">
                        <span class="hero-about__sum">120</span>
                        <span class="hero-about__text">Точек продаж</span>
                    </li>
                    <li class="hero__item-about">
                        <span class="hero-about__sum">100%</span>
                        <span class="hero-about__text">Заказов <br> доставляем в срок</span>
                    </li>
                    <li class="hero__item-about">
                        <span class="hero-about__sum">8 лет</span>
                        <span class="hero-about__text">Развития <br> на Российском рынке</span>
                    </li>
                </ul> -->
            </div>
            <img class="hero-about__img" src="<?= $this->page->image ?>">
            <img class="hero-about__img-mob" src="<?= $this->page->image2 ?>">
        </section>

        <section class="about">
            <h2 class="about__title">О компании и миссия</h2>

            <div class="about__wrapper-text">
                <div class="about__text">   
                    <?= $this->page->text ?>
                </div>
                <!-- <p class="about__text"><?= nl2br($obj->text) ?>
                    Компания «Версия-Центр» была создана в 1993 году. За это время мы выполнили множество проектов различной сложности и приобрели огромный опыт по изготовлению и реализации металлических изделий бытового и промышленного назначения.
                    <br>
                    <br>
                    На данный момент мы предлагаем нашим клиентам не только качественную современную продукцию, но и высокий сервис обслуживания, оптимальные цены и многообразие самых различных изделий из металла.
                    <br>
                    <br>
                    <span class="about__text-alt">
                        «Грола» - стабильная и проверенная временем компания. Большой опыт и производственные возможности позволяют нам быстро реагировать на изменения и потребности рынка. Мы производим как серийную продукцию, так и различные нестандартные изделия. Мы стремимся работать на результат, а самым важным для нас является максимальное удовлетворение требований клиента по качеству продукции, своевременность отгрузки товара и решение важных вопросов в оперативном порядке.
                        <br>
                        <br>
                        Мы готовы воплотить в жизнь любые ваши идеи и предложить оптимальные решения для вашего бизнеса. Свяжитесь с нами, и наши специалисты помогут разработать проект, который будет максимально соответствовать вашим требованиям.
                    </span>
                </p> -->
            </div>

            <img class="about__img" src="/public/src/images/about/about.jpg" width="473" height="474" >

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

        <section class="photo photo-about" id="galery">
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

        <section class="form form__contacts">
            <div class="form__wrapper-text form__wrapper-text-contacts">
                <h2 class="form__title form__title-contacts title">Не нашли то, что искали?</h2>
                <span class="form__contacts-text">Оставьте свои контактные данные, наши специалисты свяжутся с вами</span>
                <div class="form__contacts-wrapper">
                    <span class="form__contacts-wrapper-text">Подписывайтесь на нас в социальных сетях</span>
                    <ul class="footer__contacts">
                        <li>
                            <a class="filter" href="#">
                                <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.27424 14.8542L7.86489 16.7529L21.1377 8.63854C21.3301 8.52086 21.5273 8.78217 21.3613 8.93492L11.3128 18.1841L10.9391 23.3623C10.9107 23.7562 11.3852 23.9755 11.6668 23.6986L14.7607 20.6562L20.4167 24.9378C21.0263 25.3994 21.9091 25.0741 22.0736 24.3273L26.0119 6.4454C26.2365 5.42529 25.237 4.56449 24.2615 4.93807L2.24579 13.3684C1.55517 13.6329 1.57398 14.6164 2.27424 14.8542Z" fill="#ffffff" />
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a class="filter" href="#">
                                <svg width="26" height="16" viewBox="0 0 26 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14.1611 16C5.27787 16 0.211118 9.99399 0 0H4.44972C4.59588 7.33533 7.87625 10.4424 10.4746 11.0831V0H14.6647V6.32633C17.2306 6.05405 19.9261 3.17117 20.8355 0H25.0254C24.3271 3.90791 21.404 6.79079 19.3253 7.97598C21.404 8.93694 24.7333 11.4515 26 16H21.3877C20.3971 12.957 17.9289 10.6026 14.6647 10.2823V16H14.1611Z" fill="#ffffff" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <form class="form__wrapper form__wrapper-contacts" id="feedbackForm">
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
                <div class="form__group">
                    <label for="theme"></label>
                    <input class="form__group-text" type="text" id="theme" name="theme"  placeholder="Тема">
                </div>
                <div class="form__group form__group-contacts">
                    <label for="question"></label>
                    <textarea class="form__group-text form__group-text-area" id="question" name="question" placeholder="Ваше сообщение "></textarea>
                </div>

                <div class="form__checkbox-group form__checkbox-group-login">
                    <input type="checkbox" id="agreement" name="agreement" required>
                    <label for="agreement">
                        <p class="form__checkbox-group-text">Я принимаю условия 1 <a href="/policy.php">Публичной оферты</a></p>
                    </label>
                </div>

                <div class="form__checkbox-group form__checkbox-group-login">
                    <input type="checkbox" id="agreement-polity" name="agreement-polity" required>
                    <label for="agreement-polity">
                        <p class="form__checkbox-group-text">Ознакомлен с <a href="/policy.php">Политикой в отношении обработки персональных данных</a> и даю Согласие на их обработку и распространение</p>
                    </label>
                </div>

                <button class="form__button button-dark" type="submit" class="submit-btn">Отправить</button>
            </form>
            <img class="form__contacts-img" src="/public/src/images/contacts-form.jpg" width="420" height="456" alt="Картинка рабочего.">
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
