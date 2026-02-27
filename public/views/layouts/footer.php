	<footer  class="footer">
    <div class="footer__top">
      <div class="footer__left-wrapper">
        <div class="footer__wrapper-logo">
            <img src="/public/src/images/icons/logo-light.svg" width="160" height="51" alt="Иконка лого">
            <span class="footer__text footer__text-desktop">© grola.ru 2025</span>
        </div>
        <ul class="footer__list">
          <li><a class="filter" href="/about">О нас</a></li>
          <li><a class="filter" href="/#galery">Галерея</a></li>
          <li><a class="filter" href="/catalog">Каталог</a></li>
          <li><a class="filter" href="/#why">Почему мы</a></li>
          <li><a class="filter" href="/contacts">Контакты</a></li>
        </ul>
      </div>
      
      <div class="footer__column">
        <div class="footer__column-contacts">
          <a class="footer__phone filter" href="tel:<?= preg_replace('/[^0-9+]/', '', $this->settings->phone) ?>"><?= $this->settings->phone ?></a>
          <a class="footer__mail filter" href="<?= $this->settings->email ?>"><?= $this->settings->email ?></a>
          <address class="footer__address filter"><?= $this->settings->city ?> <?= $this->settings->address ?></address>
        </div>

        <div class="footer__column-social">
          <span class="footer__column-text">Подпишитесь на наши соцсети</span>
          <ul class="footer__contacts">
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
      </div>
    </div>

    <div class="footer__bottom">
      <div class="footer__bottom-left">
        <span class="footer__text footer__text-mob">© grola.ru 2025</span>
        <a class="footer__polity filter" href="/policy">Политика конфиденциальности</a>
        <div class="footer__bottom__wrapper">
          <a class="filter" href="#">Публичная оферта</a>
          <a class="filter" href="/public-offer">Согласие на обработку персональных данных</a>
        </div>
      </div>
      <a class="footer__development filter" href="https://visualteam.ru/">Разработка: Visualteam</a>
    </div>
  
  </footer>

  <!-- Модальное окно - ВНЕ основного контента, обычно в конце body -->
  <div id="modal-overlay" class="modal-overlay">
    <div class="modal-container">
      <button class="modal-close" onclick="closeModal()">
        <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path opacity="0.5" d="M10.9925 8.46401L16.7415 14.2131L14.1986 16.7559L8.44959 11.0069L2.65675 16.7997L-0.000830652 14.1421L5.79201 8.34929L0.0565079 2.6138L2.59937 0.0709304L8.33487 5.80643L14.1413 -6.43872e-06L16.7989 2.65758L10.9925 8.46401Z" fill="#2B2E3A" />
        </svg>
      </button>
      
      <img class="modal__img" src="/public/src/images/modal.jpg">

      <form class="form__wrapper form__wrapper-modal" id="feedbackForm">
          <?php
            $articleValue = '';

            if (isset($this->product) && is_object($this->product) && isset($this->product->id)) {
                                try {
                    $paramValue = \app\Models\CatalogParams::findWhere("WHERE catalog_id = " . (int)$this->product->id);
                    
                    if (!empty($paramValue)) {
                        foreach($paramValue as $catalogParam) {
                            if (!isset($catalogParam->param_id)) continue;
                            
                            $paramName = \app\Models\Params::findById($catalogParam->param_id);
                            
                            if ($paramName && isset($paramName->name) && $paramName->name == 'Артикул:') {
                                $articleValue = $catalogParam->value ?? '';
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log('Ошибка получения артикула: ' . $e->getMessage());
                    $articleValue = '';
                }
            }
          ?>
          <h2 class="form__mod-title title">Оставить заявку</h2>
          <input type="hidden" name="article" value="<?= $articleValue ?>">
          <div class="form__wrapper-hidden-article">

            <span class="form__article">Артикул <?= $articleValue ?></span>
            <span  class="form__article-name">Контейнер КГ-04</span>
          </div>
          <div class="form__group form__group-mod">
              <label for="name"></label>
              <input class="form__group-text" type="text" id="name" name="name" placeholder="Ваше имя">
          </div>
          <div class="form__group form__group-mod">
              <label for="phone"></label>
              <input class="form__group-text" type="tel" id="phone" name="phone" placeholder="Телефон">
          </div>
          <div class="form__group form__group-mod">
              <label for="mail"></label>
              <input class="form__group-text" type="mail" id="mail" name="mail" required placeholder="E-mail*">
          </div>
          <div class="form__group form__group-mod">
              <label for="question"></label>
              <textarea class="form__group-text" id="question" name="question" placeholder="Ваше сообщение"></textarea>
          </div>

          <div class="form__checkbox-group form__checkbox-group-mod form__checkbox-group-login">
              <input type="checkbox" id="agreement" name="agreement" required>
              <label for="agreement">
                  <p class="form__checkbox-group-text form__checkbox-group-text-mod">Я принимаю условия <a href="/public-offer">Публичной оферты</a></p> 
              </label>
          </div>

          <div class="form__checkbox-group form__checkbox-group-mod form__checkbox-group-login">
              <input type="checkbox" id="agreement-polity" name="agreement-polity" required>
              <label for="agreement-polity">
                  <p class="form__checkbox-group-text form__checkbox-group-text-mod">Ознакомлен с <a href="/policy">Политикой в отношении обработки персональных данных</a> и даю Согласие на их обработку и распространение</p>
              </label>
          </div>

          <button class="form__button button-dark" type="submit" class="submit-btn">Отправить запрос</button>
      </form>


    </div>
  </div>

  <!-- cookies -->
  <div class="cookies">
    <div class="cookies-win" id="cookiesWin">
      <button class="cookies-close">
        <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M8.2458 6.34803L12.5576 10.6598L10.6504 12.567L6.33866 8.25518L1.99403 12.5998L0.000841349 10.6066L4.34547 6.26199L0.043846 1.96037L1.95099 0.0532172L6.25262 4.35484L10.6074 1.37387e-05L12.6006 1.9932L8.2458 6.34803Z" fill="#2B2727" />
        </svg>
      </button>
      <p class="cookies-win-text">
          Мы используем cookie-файлы для улучшения предоставляемых услуг. <br>
          Продолжая навигацию по сайту,  вы соглашаетесь с правилами использования  <a href="/policy.php">cookie-файлов</a>.
      </p>
      <button class="cookies-win-button" type="button" id="acceptCookies" class="button__button font-size--16" onclick="document.getElementById('cookiesWin').style.display='none';">Ok</button>
      <a class="cookies-win-link" href="/policy" target="_blank">Политика конфиденциальности</a>
    </div>
  </div>

    <!-- jQuery ДОЛЖЕН БЫТЬ ПЕРВЫМ -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- jQuery Cookie (работает после jQuery) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>


  <script src="/public/src/js/lib/jquery.min.js"></script>

  <!-- jQuery UI - добавляем -->
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <script src="/public/src/js/lib/jquery.form.plugin.js"></script>
  <script src="/public/src/js/lib/swiper-bundle.min.js"></script>
  <script src="/public/src/js/lib/maskedinput.min.js"></script>
  <script src="/public/src/js/lib/jquery.ui.touch-punch.min.js"></script>
  <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=0ac7ca37-a7d0-424c-a7d3-130ebbc4b580"></script>
  <!-- <script src="/public/src/js/app.js?v=<?= rand() ?>"></script> -->
  <script type="module" src="/public/src/js/index.js?<?= rand() ?>"></script>
  <script type="module" src="/public/src/js/form.js"></script>

	<? include_once VIEWS.'/schema/organization.php' ?>

</body>

</html>