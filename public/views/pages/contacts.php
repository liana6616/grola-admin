<?= $this->include('layouts/header'); ?>

<main>

  <section class="contacts">
    <ul class="breadcrumps__wrapper">
      <li class="breadcrumps">
          <a href="index.php">Главная</a>
      </li>
        <li class="breadcrumps">
            <span><?= $this->page->name ?></span>
        </li>
    </ul>
    <h1 class="contacts__big-title">Контакты</h1>
    <div class="contacts__block">
      <ul class="contacts__address-list">
        <li class="contacts__address-item">
          <span class="contacts__address-name">Сервисная служба:</span>
          <a class="contacts__phone filter" href="tel:<?= preg_replace('/[^0-9+]/', '', $this->settings->phone) ?>"><?= $this->settings->phone ?></a>
        </li>
        <li class="contacts__address-item">
          <span class="contacts__address-name">E-mail</span>
          <a class="contacts__text-mail filter" href="mailto:<?= $this->settings->email ?>"><?= $this->settings->email ?></a>
        </li>
        <li class="contacts__address-item">
          <span class="contacts__address-name">Адрес офиса</span>
          <address class="contacts__address-text"><?= $this->settings->city ?> <?= $this->settings->address ?></address>
        </li>
        <li class="contacts__address-item">
          <span class="contacts__address-name">Время работы офиса:</span>
          <p class="contacts__time">
            <?= $this->settings->time_job ?>
          </p>
        </li>
      </ul>


      <ul class="contacts__soc">
            <li>
              <a class="filter" href="#">
                <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2.27424 14.8542L7.86489 16.7529L21.1377 8.63854C21.3301 8.52086 21.5273 8.78217 21.3613 8.93492L11.3128 18.1841L10.9391 23.3623C10.9107 23.7562 11.3852 23.9755 11.6668 23.6986L14.7607 20.6562L20.4167 24.9378C21.0263 25.3994 21.9091 25.0741 22.0736 24.3273L26.0119 6.4454C26.2365 5.42529 25.237 4.56449 24.2615 4.93807L2.24579 13.3684C1.55517 13.6329 1.57398 14.6164 2.27424 14.8542Z" fill="#457FCA" />
                </svg>
              </a>
            </li>
            <li> 
              <a class="filter" href="#">
                <svg width="26" height="16" viewBox="0 0 26 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M14.1611 16C5.27787 16 0.211118 9.99399 0 0H4.44972C4.59588 7.33533 7.87625 10.4424 10.4746 11.0831V0H14.6647V6.32633C17.2306 6.05405 19.9261 3.17117 20.8355 0H25.0254C24.3271 3.90791 21.404 6.79079 19.3253 7.97598C21.404 8.93694 24.7333 11.4515 26 16H21.3877C20.3971 12.957 17.9289 10.6026 14.6647 10.2823V16H14.1611Z" fill="#457FCA" />
                </svg>
              </a>
            </li>
          </ul>
    </div>
    <div class="contacts__map" id="map"></div>

  </section>

  <section class="form form__contacts">
    <div class="form__wrapper-text form__wrapper-text-contacts">
      <h2 class="form__title form__title-contacts title">Не нашли то, что искали?</h2>
      <span class="form__contacts-text">Оставьте свои контактные данные, наши специалисты свяжутся с вами</span>
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
        <div class="form__group ">
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
                <p class="form__checkbox-group-text">Я принимаю условия  <a href="/policy.php">Публичной оферты</a></p> 
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
    <img class="form__contacts-img" src="/public/src/images/contacts-form.jpg" width="420" height="456" alt="Картинка рабочего.">
  </section>


  <section class="details">
    <h2 class="details__title title">Реквизиты</h2>

    <div class="details__item">
      <?= $this->settings->requisites ?>
      <!-- <li class="details__item">ООО «Грола»</li>
      <li class="details__item">Юридический адрес: 347913, Ростовская область, г. Таганрог, ул. Химическая, д. 9</li>
      <li class="details__item">ИНН 6154062128 КПП 615401001 (основной номер)</li>
      <li class="details__item">КПП 997550001 (для первичных документов)</li> -->
    </div>
    <!-- <ul class="details__list">
      <li class="details__item">Р/с 40702810501850001753</li>
      <li class="details__item">в АО "АЛЬФА-БАНК", Москва</li>
      <li class="details__item">БИК 044525593</li>
      <li class="details__item">к/с 30101810200000000593 в ГУ БАНКА РОССИИ ПО ЦФО</li>
    </ul> -->
  </section>
</main>

<?= $this->include('layouts/footer'); ?>