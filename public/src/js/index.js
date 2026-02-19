
document.addEventListener('DOMContentLoaded', function() {
  // Функция для создания пагинации с 4 точками
  function createFourBulletsPagination(swiper, current, total) {
      let bullets = '';
      const maxBullets = 4;
      
      // Рассчитываем текущую позицию для 4 точек
      const currentPosition = ((current - 1) % maxBullets) + 1;
      
      for (let i = 1; i <= maxBullets; i++) {
          const isActive = i === currentPosition;
          bullets += `<span class="swiper-pagination-bullet ${isActive ? 'swiper-pagination-bullet-active' : ''}" data-slide="${i}"></span>`;
      }
      return bullets;
  }

  // swiper-catalog-card
  const swiperCatalogCard = new Swiper('#swiper-catalog-card', {
      loop: false,
      slidesPerView: 2,
      spaceBetween: 10,
      pagination: {
          el: '.swiper-pagination-catalog-card',
          type: 'custom',
          renderCustom: createFourBulletsPagination,
          clickable: true
      },
      navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
      },
      breakpoints: {
          360: {
              slidesPerView: 2,
              allowTouchMove: true,
          },
          768: {
              slidesPerView: 3,
              allowTouchMove: true,
          },
          1440: {
              slidesPerView: 4,
              allowTouchMove: true,
              spaceBetween: 0,
          }
      }
  });

  // swiper-categories (только на мобильных)
  if (window.innerWidth <= 1199) {
      const swiperCategories = new Swiper('#swiper-categories', {
          loop: true,
          slidesPerView: 1.5,
          spaceBetween: 0,
          slidesPerGroup: 1, 
          slidesOffsetBefore: 0,
          slidesOffsetAfter: 0,
          pagination: { 
              el: '.swiper-pagination-categories',
              type: 'custom',
              renderCustom: createFourBulletsPagination,
              clickable: true,
          },
          breakpoints: {
              360: { slidesPerView: 1.5 },
              768: { slidesPerView: 1.5 }
          }
      });
      
      window.addEventListener('resize', function() {
          if (window.innerWidth > 1199) {
              swiperCategories.destroy();
          }
      });
  }

  // swiper-photo
  const swiperPhoto = new Swiper('#swiper-photo', {
      loop: true,
      spaceBetween: 5,
      slidesPerView: 3,
      navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
      },
      pagination: {
          el: '.swiper-pagination-photo',
          type: 'custom',
          renderCustom: createFourBulletsPagination,
          clickable: true,
      },
      breakpoints: {
          360: {
              slidesPerView: 1.6,
              allowTouchMove: true,
          },
          768: {
              slidesPerView: 2.5,
              allowTouchMove: true,
              spaceBetween: 5,
          },
          980: {
              slidesPerView: 3.5,
              allowTouchMove: true,
              spaceBetween: 15,
          },
          1440: {
              slidesPerView: 3,
              allowTouchMove: true,
              spaceBetween: 15,
          }
      }
  });

  // swiper-scheme (только для схемы)
  let swiperScheme = null;

  function initSwiperScheme() {
      const container = document.querySelector('#swiper-scheme');
      if (!container) return;
      
      // Уничтожаем Swiper если существует
      if (swiperScheme) {
          swiperScheme.destroy(true, true);
          swiperScheme = null;
      }
      
      // На больших экранах не инициализируем Swiper
      if (window.innerWidth >= 1440) return;
      
      // Инициализируем Swiper только на маленьких экранах
      swiperScheme = new Swiper('#swiper-scheme', {
          loop: true,
          slidesPerView: 1.5,
          spaceBetween: 15,
          allowTouchMove: true,
          pagination: { 
              el: '.swiper-pagination-scheme',
              type: 'custom',
              renderCustom: createFourBulletsPagination,
              clickable: true,
          },
          navigation: {
              nextEl: '.swiper-button-next',
              prevEl: '.swiper-button-prev',
          },
          breakpoints: {
              768: {
                  slidesPerView: 2,
                  spaceBetween: 27,
              },
              1024: {
                  slidesPerView: 3,
                  spaceBetween: 40,
              }
          }
      });
  }

  // Инициализация при загрузке
  if (document.querySelector('#swiper-scheme')) {
      initSwiperScheme();
      
      let resizeTimer;
      window.addEventListener('resize', function() {
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(initSwiperScheme, 250);
      });
  }

  // swiper-partners
  const swiperPartners = new Swiper('#swiper-partners', {
      loop: true,
      slidesPerView: 8,
      spaceBetween: 20,
      allowTouchMove: true,
      watchOverflow: true,
      pagination: { 
          el: '.swiper-pagination-partners',
          type: 'custom',
          renderCustom: createFourBulletsPagination,
          clickable: true,
      },
      navigation: {
          nextEl: '.swiper-button-next-product',
          prevEl: '.swiper-button-prev-product',
      },
      breakpoints: {
          0: {
              slidesPerView: 3.5,
              spaceBetween: 10,
          },
          766: {
              slidesPerView: 8,
              spaceBetween: 20,
          }
      },
  });

  // Вертикальный мини-свайпер для карточки товара
  var swiperThumbs = new Swiper(".mySwiperCardMini", {
      loop: true,
      slidesPerView: 4,
      freeMode: true,
      watchSlidesProgress: true,
      direction: "vertical",
      navigation: {
          nextEl: ".swiper-button-next-card",
          prevEl: ".swiper-button-prev-card",
      }
  });

  // Основной свайпер для карточки товара
  var swiperCardMain = new Swiper(".mySwiperCard", {
      loop: true,
      thumbs: {
          swiper: swiperThumbs,
      },
      pagination: {
          el: '.swiper-pagination-catalog',
          type: 'custom',
          renderCustom: createFourBulletsPagination,
          clickable: true,
      },
      navigation: {
          nextEl: ".swiper-button-next-card",
          prevEl: ".swiper-button-prev-card",
      },
      breakpoints: {
        0: {
            allowTouchMove: true,
            touchRatio: 1,
            touchAngle: 45,
            grabCursor: true,
        },
        768: {
            allowTouchMove: false,
        }
    }
  });
});

// меню переключение 
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const linkItems = document.querySelectorAll('.header__item-link');

    // Всегда сначала убираем active со всех
    linkItems.forEach(item => item.classList.remove('active'));

    // Если на нужной странице - добавляем active
    if (currentPath === '/about.php' || currentPath === '/contacts.php') {
        const activeItem = document.querySelector(`.header__item-link .header__link[href="${currentPath}"]`);
        if (activeItem && activeItem.parentElement) {
        activeItem.parentElement.classList.add('active');
        }
    }
});

// карта
if (document.getElementById('map')) {
  ymaps.ready(init);
}

function init() {
  let center = [59.843638, 30.440321];
  
  let map = new ymaps.Map('map', {
      center: center,
      zoom: 16,
      controls: []
  });

  let placemark = new ymaps.Placemark(center, {
      hintContent: 'Адрес офис',
      balloonContent: 'Санкт-Петербург, Складской проезд, д.4'
  }, {
      iconLayout: 'default#image',
      iconImageHref: '/public/images/icons/map.svg',
      iconImageSize: [53, 69],
      iconImageOffset: [-140, -130]
  });

  // УДАЛЯЕМ ВСЕ элементы управления для уверенности
  map.controls.remove('geolocationControl');      // геолокация ✓
  map.controls.remove('searchControl');           // поиск ✓
  map.controls.remove('trafficControl');          // пробки ✓
  map.controls.remove('typeSelector');            // тип карты (схема/спутник) ✓
  map.controls.remove('fullscreenControl');       // полноэкранный режим ✓
  map.controls.remove('zoomControl');             // масштабирование ✓ (раскомментировано!)
  map.controls.remove('rulerControl');            // линейка ✓
  
  // Также можно удалить другие возможные контроллеры
  map.controls.remove('routeButtonControl');      // кнопка маршрута
  map.controls.remove('routePanelControl');       // панель маршрутов
  
  map.geoObjects.add(placemark);
}


(function() {
    'use strict';
    
    let isModalOpen = false;
    
    // Функция открытия модального окна
    function openModal() {
        const modal = document.getElementById('modal-overlay');
        if (!modal) return;
        
        // Убираем display: none если он есть в инлайн-стилях
        modal.style.display = '';
        
        // Даем браузеру время применить display
        setTimeout(() => {
            modal.classList.add('active');
            isModalOpen = true;
        }, 10);
        
        // Блокируем скролл страницы
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
        
        // Скрываем статью при открытии обычного модального окна
        const articleElement = document.querySelector('.form__wrapper-hidden-article');
        if (articleElement) {
            articleElement.style.display = 'none';
        }
    }
    
    // Функция закрытия модального окна
    function closeModal() {
        const modal = document.getElementById('modal-overlay');
        if (!modal) return;
        
        modal.classList.remove('active');
        isModalOpen = false;
        
        // Возвращаем скролл
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
        
        // Скрываем статью при закрытии модального окна
        const articleElement = document.querySelector('.form__wrapper-hidden-article');
        if (articleElement) {
            articleElement.style.display = 'none';
        }
        
        // Через время скрываем элемент (для CSS анимаций)
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    // Функция открытия модального окна с показом статьи
    function openModalWithArticle() {
        // Открываем модальное окно
        openModal();
        
        // Показываем элемент .form__wrapper-hidden-article
        const articleElement = document.querySelector('.form__wrapper-hidden-article');
        if (articleElement) {
            articleElement.style.display = 'flex';
        }
    }
    
    // Маска для телефона
    function setupPhoneMask() {
        const phoneInput = document.getElementById('modal-phone');
        if (!phoneInput) return;
        
        phoneInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            
            if (!value) return;
            
            if (value[0] === '7' || value[0] === '8') {
                value = value.substring(1);
            }
            
            if (value.length > 0) {
                value = '+7 ' + value;
            }
            
            if (value.length > 4) value = value.substring(0, 4) + ' ' + value.substring(4);
            if (value.length > 8) value = value.substring(0, 8) + ' ' + value.substring(8);
            if (value.length > 12) value = value.substring(0, 12) + ' ' + value.substring(12);
            if (value.length > 15) value = value.substring(0, 15);
            
            this.value = value;
        });
    }
    
    // // Обработка отправки формы
    // function setupForm() {
    //     const form = document.getElementById('feedbackForm');
    //     if (!form) return;
        
    //     form.addEventListener('submit', function(e) {
    //         e.preventDefault();
            
    //         // Простая валидация телефона
    //         const phone = document.getElementById('modal-phone')?.value;
    //         const phoneRegex = /^[\+]?[78][-\s]?\(?\d{3}\)?[-\s]?\d{3}[-\s]?\d{2}[-\s]?\d{2}$/;
            
    //         if (phone && !phoneRegex.test(phone)) {
    //             alert('Пожалуйста, введите корректный номер телефона');
    //             return;
    //         }
            
    //         // Проверка чекбоксов
    //         const agreement1 = document.getElementById('modal-agreement');
    //         const agreement2 = document.getElementById('modal-agreement-polity');
                
    //         // Если чекбоксы есть на странице, проверяем их
    //         if (agreement1 && agreement2) {
    //             if (!agreement1.checked || !agreement2.checked) {
    //                 alert('Пожалуйста, примите условия соглашений');
    //                 return;
    //             }
    //         }
            
    //         // Здесь будет отправка на сервер
    //         // Покажем сообщение об успехе
    //         setTimeout(() => {
    //             alert('Ваша заявка отправлена! Мы свяжемся с вами в ближайшее время.');
    //             form.reset();
    //             closeModal();
    //         }, 500);
    //     });
    // }
    
    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Находим кнопку "Рассчитать" и вешаем обработчик
        const calculateBtn = document.querySelector('.header__button-sum');
        if (calculateBtn) {
            calculateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        }
        
        // 2. Настраиваем модальное окно
        const modal = document.getElementById('modal-overlay');
        if (modal) {
            // Закрытие по клику на оверлей
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
            
            // Закрытие по крестику
            const closeBtn = modal.querySelector('.modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }
            
            // 3. Настраиваем маску телефона
            setupPhoneMask();
            
            // // 4. Настраиваем форму
            // setupForm();
        }
        
        // 5. Закрытие по клавише ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isModalOpen) {
                closeModal();
            }
        });
        
        // Делаем функции глобальными (на всякий случай)
        window.openModal = openModal;
        window.closeModal = closeModal;
        window.openModalWithArticle = openModalWithArticle;
    });
    
  })();

  /* cookies */
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?a(require("jquery")):a(jQuery)}(function(a){function b(a){return h.raw?a:encodeURIComponent(a)}function c(a){return h.raw?a:decodeURIComponent(a)}function d(a){return b(h.json?JSON.stringify(a):String(a))}function e(a){0===a.indexOf('"')&&(a=a.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\"));try{return a=decodeURIComponent(a.replace(g," ")),h.json?JSON.parse(a):a}catch(b){}}function f(b,c){var d=h.raw?b:e(b);return a.isFunction(c)?c(d):d}var g=/\+/g,h=a.cookie=function(e,g,i){if(void 0!==g&&!a.isFunction(g)){if(i=a.extend({},h.defaults,i),"number"==typeof i.expires){var j=i.expires,k=i.expires=new Date;k.setTime(+k+864e5*j)}return document.cookie=[b(e),"=",d(g),i.expires?"; expires="+i.expires.toUTCString():"",i.path?"; path="+i.path:"",i.domain?"; domain="+i.domain:"",i.secure?"; secure":""].join("")}for(var l=e?void 0:{},m=document.cookie?document.cookie.split("; "):[],n=0,o=m.length;o>n;n++){var p=m[n].split("="),q=c(p.shift()),r=p.join("=");if(e&&e===q){l=f(r,g);break}e||void 0===(r=f(r))||(l[q]=r)}return l};h.defaults={},a.removeCookie=function(b,c){return void 0===a.cookie(b)?!1:(a.cookie(b,"",a.extend({},c,{expires:-1})),!a.cookie(b))}});

$(document).ready(function () {
  $(function() {

    if (!$.cookie('hideCookieWin')) {
      var delay_popup = 1000;
      setTimeout("document.getElementById('cookiesWin').style.display='block'", delay_popup);
    }
  });

  $('body').on('click', '#acceptCookies', function(){
    $.cookie('hideCookieWin', true, {
      expires: 7
    });
  });
});

$('body').on('click', '.cookies-close', function(){
  $('#cookiesWin').hide(); // Просто скрываем без сохранения
});

// catalog cards pagination
document.addEventListener('DOMContentLoaded', function() {
    const CARDS_PER_PAGE = 12;
    const cards = document.querySelectorAll('.catalog-card__item');
    const total = cards.length;
    const pages = Math.ceil(total / CARDS_PER_PAGE);
    let current = 1;
    
    // Получаем контейнер с карточками для прокрутки
    const catalogContainer = document.querySelector('.catalog-card__list') || 
                             document.querySelector('.catalog-container') ||
                             document.querySelector('.catalog-cards');
    
    // Если карточек 12 или меньше - скрываем пагинацию
    if (total <= CARDS_PER_PAGE) {
        const pagination = document.getElementById('pagination');
        if (pagination) {
            pagination.style.display = 'none';
        }      
        return;
    }
    
    // Глобальная функция для показа страницы
    function showPage(page) {
        current = page;
        
        cards.forEach((card, index) => {
            const start = (page - 1) * CARDS_PER_PAGE;
            const end = start + CARDS_PER_PAGE;
            card.style.display = (index >= start && index < end) ? 'flex' : 'none';
        });
        
        updatePagination(page);
        scrollToCatalogTop();
    }
    
    // Функция обновления пагинации - ВСЕГДА показываем предыдущую страницу
    function updatePagination(page) {
        const pagination = document.getElementById('pagination');
        let html = '';
        
        // Всегда показываем первую страницу, кроме случаев когда мы на 1 или 2 странице
        // (она и так будет показана как предыдущая)
        
        if (page === 1) {
            // Страница 1: [1] 2 3 ... последняя
            html += `<span class="pagination__sum active" data-page="1">1</span>`;
            html += `<span class="pagination__sum" data-page="2">2</span>`;
            html += `<span class="pagination__sum" data-page="3">3</span>`;
            
            if (pages > 3) {
                html += `<span class="pagination__dots">...</span>`;
                html += `<span class="pagination__sum" data-page="${pages}">${pages}</span>`;
            }
        } 
        else if (page === 2) {
            // Страница 2: 1 [2] 3 4 ... последняя
            html += `<span class="pagination__sum" data-page="1">1</span>`;
            html += `<span class="pagination__sum active" data-page="2">2</span>`;
            html += `<span class="pagination__sum" data-page="3">3</span>`;
            html += `<span class="pagination__sum" data-page="4">4</span>`;
            
            if (pages > 4) {
                html += `<span class="pagination__dots">...</span>`;
                html += `<span class="pagination__sum" data-page="${pages}">${pages}</span>`;
            }
        } 
        else if (page === 3) {
            // Страница 3: 1 2 [3] 4 ... последняя
            html += `<span class="pagination__sum" data-page="1">1</span>`;
            html += `<span class="pagination__sum" data-page="2">2</span>`;
            html += `<span class="pagination__sum active" data-page="3">3</span>`;
            html += `<span class="pagination__sum" data-page="4">4</span>`;
            
            if (pages > 4) {
                html += `<span class="pagination__dots">...</span>`;
                html += `<span class="pagination__sum" data-page="${pages}">${pages}</span>`;
            }
        } 
        else if (page >= 4 && page <= pages - 3) {
            // Страницы 4-7 (для 10 страниц): 1 ... prev current next ... последняя
            html += `<span class="pagination__sum" data-page="1">1</span>`;
            html += `<span class="pagination__dots">...</span>`;
            html += `<span class="pagination__sum" data-page="${page - 1}">${page - 1}</span>`; // предыдущая
            html += `<span class="pagination__sum active" data-page="${page}">${page}</span>`; // текущая
            html += `<span class="pagination__sum" data-page="${page + 1}">${page + 1}</span>`; // следующая
            html += `<span class="pagination__dots">...</span>`;
            html += `<span class="pagination__sum" data-page="${pages}">${pages}</span>`; // последняя
        } 
        else if (page === pages - 2) {
            // Предпоследняя-2 страница (8 для 10): 1 ... 7 [8] 9 10
            html += `<span class="pagination__sum" data-page="1">1</span>`;
            html += `<span class="pagination__dots">...</span>`;
            html += `<span class="pagination__sum" data-page="${page - 1}">${page - 1}</span>`;
            html += `<span class="pagination__sum active" data-page="${page}">${page}</span>`;
            html += `<span class="pagination__sum" data-page="${page + 1}">${page + 1}</span>`;
            html += `<span class="pagination__sum" data-page="${pages}">${pages}</span>`;
        } 
        else if (page === pages - 1) {
            // Предпоследняя страница (9 для 10): 1 ... 8 [9] 10
            html += `<span class="pagination__sum" data-page="1">1</span>`;
            html += `<span class="pagination__dots">...</span>`;
            html += `<span class="pagination__sum" data-page="${page - 1}">${page - 1}</span>`;
            html += `<span class="pagination__sum active" data-page="${page}">${page}</span>`;
            html += `<span class="pagination__sum" data-page="${pages}">${pages}</span>`;
        } 
        else if (page === pages) {
            // Последняя страница (10 для 10): 1 ... 8 9 [10]
            html += `<span class="pagination__sum" data-page="1">1</span>`;
            html += `<span class="pagination__dots">...</span>`;
            html += `<span class="pagination__sum" data-page="${pages - 2}">${pages - 2}</span>`;
            html += `<span class="pagination__sum" data-page="${pages - 1}">${pages - 1}</span>`;
            html += `<span class="pagination__sum active" data-page="${pages}">${pages}</span>`;
        }
        
        pagination.innerHTML = html;
    }
    
    // Функция для плавной прокрутки к началу каталога
    function scrollToCatalogTop() {
      const targetElement = catalogContainer || 
                           document.querySelector('.catalog') ||
                           document.querySelector('.catalog-section') ||
                           document.documentElement;
      
      const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
      const offsetPosition = elementPosition - 200;
      
      window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
      });
    }
    
    // ДЕЛЕГИРОВАНИЕ СОБЫТИЙ - ОДИН РАЗ ПРИ ЗАГРУЗКЕ
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('pagination__sum')) {
            const pageNum = parseInt(e.target.getAttribute('data-page'));
            if (pageNum !== current) {
                scrollToCatalogTop();
                showPage(pageNum);
            }
        }
    });
    
    // Инициализация
    showPage(1);
});

$(function() {
    var slider = $(".catalog__filter-weight").slider({
        min: 0,
        max: 5000,
        values: [1000, 4000],
        range: true,
        // Добавляем поддержку touch-событий
        touch: true, // если поддерживается плагином
        create: function() {
            // Расчет позиций: левый от начала, правый от конца
            var leftPos = (1000 / 5000 * 100) + '%'; // 20%
            var rightPos = 100 - (4000 / 5000 * 100) + '%'; // 20% от конца = 80% от начала
            
            $('.catalog__filter-polzunok').append(
                '<div class="tooltip left-tooltip" style="left: ' + leftPos + '">1000 ₽</div>' +
                '<div class="tooltip right-tooltip" style="right: ' + rightPos + '">4000 ₽</div>'
            );
            
            // Добавляем touch-события вручную
            addTouchSupport();
        },
        slide: function(event, ui) {
            var tooltips = $('.catalog__filter-polzunok .tooltip');
            
            // Левый ползунок: рассчитываем от начала
            var leftPosPercent = (ui.values[0] / 5000 * 100);
            tooltips.eq(0).text(ui.values[0] + ' ₽').css('left', leftPosPercent + '%');
            
            // Правый ползунок: рассчитываем от конца
            // Если хотим, чтобы 69% от правого края = 31% от левого края
            var rightPosFromEnd = 69; // или другой процент, например 31%
            var rightPosPercent = 100 - (ui.values[1] / 5000 * 100 * (100 / rightPosFromEnd));
            
            // Альтернативный расчет: фиксированный отступ 69% от правого края
            var rightPosFromStart = 100 - (ui.values[1] / 5000 * 100 * 0.69);
            
            tooltips.eq(1).text(ui.values[1] + ' ₽').css({
                'right': (100 - (ui.values[1] / 5000 * 100)) + '%', // от правого края
                'left': 'auto' // сбрасываем left
            });
            
            // ИЛИ если хотите использовать конкретное значение 69%:
            // tooltips.eq(1).css('right', '31%'); // 100% - 69% = 31%
        }
    });
    
    // Функция для добавления touch-поддержки
    function addTouchSupport() {
        var sliderElement = $(".catalog__filter-weight");
        var handles = sliderElement.find('.ui-slider-handle');
        
        handles.each(function() {
            var handle = $(this);
            
            // Добавляем touch-события
            handle.on('touchstart', function(e) {
                e.preventDefault();
                var touch = e.originalEvent.touches[0];
                triggerMouseEvent(handle, 'mousedown', touch);
            });
            
            handle.on('touchmove', function(e) {
                e.preventDefault();
                var touch = e.originalEvent.touches[0];
                triggerMouseEvent(handle, 'mousemove', touch);
            });
            
            handle.on('touchend', function(e) {
                e.preventDefault();
                var touch = e.originalEvent.changedTouches[0];
                triggerMouseEvent(handle, 'mouseup', touch);
            });
        });
        
        // Также добавляем touch-события для самого слайдера
        sliderElement.on('touchstart', function(e) {
            if (!$(e.target).hasClass('ui-slider-handle')) {
                e.preventDefault();
                var touch = e.originalEvent.touches[0];
                triggerMouseEvent($(this), 'mousedown', touch);
            }
        });
    }
    
    function triggerMouseEvent(element, eventType, touch) {
        var mouseEvent = new MouseEvent(eventType, {
            clientX: touch.clientX,
            clientY: touch.clientY,
            bubbles: true,
            cancelable: true,
            view: window
        });
        element[0].dispatchEvent(mouseEvent);
    }
  });

// список/плитка 
document.addEventListener('DOMContentLoaded', function() {
    const tileBtn = document.querySelector('.catalog__buttons-filter-sort-tile');
    const listBtn = document.querySelector('.catalog__buttons-filter-sort-list');
    const catalogList = document.querySelector('.catalog-card__list');
    
    if (!tileBtn || !listBtn || !catalogList) return;
    
    // Получаем только ВИДИМЫЕ карточки
    function getVisibleCards() {
        return Array.from(document.querySelectorAll('.catalog-card__item')).filter(card => 
            card.style.display !== 'none' && window.getComputedStyle(card).display !== 'none'
        );
    }
    
    function switchView(isListView) {
        const visibleCards = getVisibleCards();
        
        // Сброс анимаций только для видимых карточек
        visibleCards.forEach(item => {
            item.style.animation = 'none';
            item.style.opacity = '0';
            item.style.transform = isListView ? 'translateX(-20px)' : 'scale(0.9)';
        });
        
        // Небольшая задержка для браузера
        setTimeout(() => {
            // Изменяем layout
            if (isListView) {
                catalogList.classList.add('catalog-card__list-alt');
                listBtn.classList.add('active');
                tileBtn.classList.remove('active');
            } else {
                catalogList.classList.remove('catalog-card__list-alt');
                tileBtn.classList.add('active');
                listBtn.classList.remove('active');
            }
            
            // Принудительный reflow для анимации
            catalogList.offsetHeight;
            
            // Запуск анимации только для видимых карточек
            visibleCards.forEach((item, index) => {
                item.style.animation = `${isListView ? 'listAppear' : 'tileAppear'} 0.3s ease forwards`;
                item.style.animationDelay = `${Math.min(index * 0.02, 0.2)}s`;
            });
        }, 30);
    }
    
    // Используем .onclick для предотвращения дублирования обработчиков
    tileBtn.onclick = () => switchView(false);
    listBtn.onclick = () => switchView(true);
});

// подкатегория каталог
const text = document.querySelector('.catalog__dropdown-text');
const buttons = document.querySelectorAll('.catalog__button-category');

buttons.forEach(btn => btn.onclick = () => {
  buttons.forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  if(text) text.textContent = btn.textContent;
  if(window.innerWidth <= 768 && categoryToggle) categoryToggle.checked = false;
});


// tab-card
window.showTab = function(tabId, btn) {
  // Находим контейнер табов
  const tabs = btn.closest('.tabs');
  if (!tabs) return;
  
  // Убираем active у всех в этом контейнере
  tabs.querySelectorAll('.tabs__btn, .tabs__pane').forEach(el => {
      el.classList.remove('active');
  });
  
  // Добавляем active
  btn.classList.add('active');
  document.getElementById(tabId)?.classList.add('active');
};

// Вертикальный мини-свайпер
var swiperThumbs = new Swiper(".mySwiperCardMini", {
  loop: true,
  // spaceBetween: 10,
  slidesPerView: 4,
  freeMode: true,
  watchSlidesProgress: true,
  direction: "vertical",
  // slidesOffsetBefore: -75,
  navigation: {
      nextEl: ".swiper-button-next-card",
      prevEl: ".swiper-button-prev-card",
  }
});

var swiper = new Swiper(".mySwiperCard", {
  // spaceBetween: 10,
  loop: true,
  thumbs: {
      swiper: swiperThumbs,
  },
  pagination: {
      el: '.swiper-pagination-catalog',
      clickable: true,
  },
  navigation: {
    nextEl: ".swiper-button-next-card",
    prevEl: ".swiper-button-prev-card",
  }
});
