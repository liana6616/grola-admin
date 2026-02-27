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