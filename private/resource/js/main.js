$(document).ready(function() {
  $('select').on('change', function() {
    $(this).trigger('blur');
  });
    
    // Основной способ отслеживания изменений для Chosen
    $('.type-select').on('change', function(evt, params) {
        toggleDirectoryField();
    });
    
    function toggleDirectoryField() {
        var selectedType = $('.type-select select').val();
        
        if (selectedType == 1 || selectedType == '1') {
            $('#directory-field').slideDown(300);
            // Обновляем Chosen для селекта справочника если он есть
            $('#directory-field select').trigger('chosen:updated');
        } else {
            $('#directory-field').slideUp(300, function() {
                // Очищаем значение при скрытии
                var directorySelect = $('#directory-field select');
                if (directorySelect.length) {
                    directorySelect.val('').trigger('chosen:updated');
                }
            });
        }
    }
});

// Замена target='_blank' на rel='external'
function externalLinks() {
    if (!document.getElementsByTagName) return;
    var anchors = document.getElementsByTagName("a");
for (var i=0; i < anchors.length; i++) {
    if (anchors[i].getAttribute("href") &&

    anchors[i].getAttribute("rel") == "external") {
        anchors[i].target = "_blank";
    }
}
}
window.onload = externalLinks;


$('body').on('submit', '#login_form', function(){
    
    var form = $(this);
    let link = $('.visualteam').attr('data-i');
    
    $.ajax({
        url: '/'+link+'/ajax',
        type: 'POST',
        data: form.serialize(),
        success: function(response) {
            //console.log(response.success);
            if (response.success === true) {
                // Успешная авторизация
                window.location.reload();
            } else {
                // Ошибка авторизации
                //$('#login_error').html(response).show();

                let i = modalAlert(response.error.message);

                setTimeout(function(){
                    closeModal(i);
                },1500);
            }
        },
        error: function(response) {

            var errorResponse = JSON.parse(response.responseText);
            // Получаем сообщение об ошибке
            var errorMessage = errorResponse.error.message;

            //console.log(errorMessage);

            let i = modalAlert(errorMessage);

            setTimeout(function(){
                closeModal(i);
            },1500);

            //$('#login_error').html('Ошибка соединения').show();
        }
    });
    
    return false;
});

$('body').on('click', '.logout', function(){

    let link = $('.visualteam').attr('data-i');

    $.ajax({
        url: '/'+link+'/ajax',
        type: 'POST',
        data: {
              'action': 'adminLogout'
        }
        , success: function(html){
            top.location.href='/visualteam';
        }
    });
    return false;
});

$('body').on('click', '.nav_item.open_block', function(){

    if ($(this).hasClass('active')) {
        $(this).removeClass('active');
    } else {
        $(this).addClass('active');
    }

    return false;
});

$('body').on('click', '.nav_item2', function(){

    let link = $(this).attr('href');
    top.location.href=link;

    return false;
});

$('body').on('change', '.per_page_select', function(){
    let i = $(this).val();
    let cl = $(this).attr('data-class');
    let link = $('.visualteam').attr('data-i');

    $.ajax({
        url: '/'+link+'/ajax',
        type: 'POST',
        data: {
              'action': 'perPageSelect'
            , 'class' : cl
            , 'i' : i
        }
        , success: function(html){
            top.location.reload();
        }
    });
    return false;
});

// Скрывать/показывать по кнопке глаза
$('body').on('click', '.action_show', function () {

    let id = $(this).attr('data-id');
    let className = $(this).attr('data-className');
    let show;

    if ($(this).hasClass('active')) {
        $(this).removeClass('active');
        show = 0;
    } else {
        $(this).addClass('active');
        show = 1;
    }

    let link = $('.visualteam').attr('data-i');

    $.ajax({
        url: '/'+link+'/ajax',
        type: 'POST',
        data: {
            'action': 'adminShow',
            'id': id,
            'className': className,
            'show': show
        },
        success: function (html) {
            console.log(html);
        }
    });
});

// --- Chosen --- //

function chosen_init(){
    if ($('.chosen').length > 0) {
        $('.chosen').chosen({
            no_results_text: "Ничего не найдено...",
            width:'100%',
            search_contains: true
        });
    }
}
chosen_init();

// --- // --- //


$(document).ready(function() {
    // Обработка вкладок в редактировании/добавлении
    $('.edit_tab_nav').on('click', function() {
        var targetTab = $(this).data('tab');
        
        // Убираем активный класс у всех кнопок и контента
        $('.edit_tab_nav').removeClass('active');
        $('.edit_tab_content').removeClass('active');
        
        // Добавляем активный класс текущей кнопке и соответствующему контенту
        $(this).addClass('active');
        $('#tab_' + targetTab).addClass('active');
    });

    // Нажатие кнопки сохранить в правом верхнем углу
    $('body').on('click', '.save', function(){
        $('button[type=submit]').click();
    });

    // Обработчик для кнопки публикации
    $('body').on('click', '.btn_publish', function(e) {
        e.preventDefault();
        $('#publish').val(1);
        $('.save').click();
    });

    if($('.btn_publish.active').length) {
        $('.btn_publish.dop').removeClass('none');
    }

    // Сортировка галереи
    $(".sortbox").sortable({
        handle: '.handler',
        helper: function(e, item) {
            // Создаем клон элемента с правильными стилями
            var helper = item.clone();
            
            // Добавляем класс для стилизации
            helper.addClass('ui-sortable-helper');
            
            return helper;
        },
        deactivate: function(event, ui) {
            let i = $('.rate').length;
            $('.rate').each(function(){
                $(this).val(i);
                i--;
            });
            
            if($('.nums').length) {
                i = 1;
                $('.nums').each(function(){
                    $(this).html(i);
                    i++;
                });
            }
        }
    });
    
    $(".sortbox").disableSelection();

    // Сортировка в списках элементов админки
    $('.sortbox-items').sortable({
        handle: '.handler',
        deactivate: function( event, ui ) {
            let items = [];
            let i = 0;
            let className = '';
            $('.sortbox-items .table_row').each(function(){
                items[i] = $(this).attr('data-id');
                className = $(this).attr('data-class');
                i++;
            });
            let link = $('.visualteam').attr('data-i');
            $.ajax({
                url: '/'+link+'/ajax',
                type: 'POST',
                data: {
                      'action': 'sortbox'
                    , 'items' : items
                    , 'className' : className
                }
                , success: function(html){
                    console.log(html);
                }
            });
        }
    });
    $('.sortbox-items').disableSelection();

    // Показ/Скрытие фотографий в галерее и файлах
    $('body').on('click','.gallery_show, .files_show',function(){
        if($(this).hasClass('active')) {
            $(this).removeClass('active');
            $('input',this).val(0);
        }
        else {
            $(this).addClass('active');
            $('input',this).val(1);
        }
    });

    // Выбор файла в инпуте
    $('input[type=file]').change(function () {

        let item = $(this);
        let block = item.closest('.input_block');
        
        
        let nm = $('label',block).attr('data-v');

        let count = this.files.length;
        let file = declOfNum(count, ['файл', 'файла', 'файлов']);
        let fileCount = 'Выбрано';

        if (count === 1) {
            fileCount = 'Выбран';
        }

        if(count > 0) {
            nm = fileCount + ' ' + count + ' ' + file;
            $('label',block).addClass('set');
        }
        else $('label',block).removeClass('set');

        $('label',block).text(nm);
    });

    if($('#YMapsID').length)
    {
        ymaps.ready(init);

        function init() {

            let lat = parseFloat($('#YMapsID').attr('data-lat'));
            let lng = parseFloat($('#YMapsID').attr('data-lng'));
            let myPlacemark;

            if(lat == '0' || lng == '0')
            {
                lat = parseFloat('55.7558');
                lng = parseFloat('37.6173');
            }

            let myMap = new ymaps.Map('YMapsID', {
                center: [lat,lng],
                zoom: 15,
                controls: []
            });

            myMap.controls.add('zoomControl');
            myMap.behaviors.disable('scrollZoom');

            let placemark = new ymaps.Placemark([lat,lng],{},
            {
                iconLayout: 'default#image',
                iconImageHref: '/private/src/images/marker.svg',
                iconImageSize: [45, 45],
                iconImageOffset: [-22, -40],
                iconContentOffset:[0, 0]
            });
            myMap.geoObjects.add(placemark);

            myMap.events.add('click', function (e) {
                var coords = e.get('coords');

                $('#YMapsID').attr('data-lat',coords[0]);
                $('#YMapsID').attr('data-lng',coords[1]);

                let c = coords[0]+','+coords[1];
                $('input[name=coords]').val(c);

                if (myPlacemark) {
                    myPlacemark.geometry.setCoordinates(coords);
                }
                else {
                    myPlacemark = createPlacemark(coords);
                    myMap.geoObjects.add(myPlacemark);
                    myPlacemark.events.add('dragend', function () {
                        getAddress(myPlacemark.geometry.getCoordinates());
                    });
                }
            });
        }
        function createPlacemark(coords) {
            return new ymaps.Placemark(coords, {
                iconCaption: coords
            }, {
                preset: 'islands#redDotIconWithCaption',
                draggable: true
            });
        }
    }

    // Переключение вкладок
    $('.admin_tab_nav').on('click', function() {
        var tabId = $(this).data('tab');
        
        // Убираем активный класс у всех вкладок
        $('.admin_tab_nav').removeClass('active');
        $('.admin_tab_content').removeClass('active');
        
        // Добавляем активный класс текущей вкладке
        $(this).addClass('active');
        $('#tab_' + tabId).addClass('active');
    });





    $('.mask_phone').mask('+7 (999) 999-99-99');

    $('body').on('click', '.button_alert, #black', function () {
        $('.notice').hide();
        $('#black').hide();
    });

    if ($('.notice #alert').length) {
        $('.notice #alert').addClass('anime-mod-show');
        if (!$('.notice').hasClass('error')) {
            setTimeout(function () {
                $('.notice').fadeOut(150);
            }, 1200);
        }
    }
});


function declOfNum(number, titles) {
    let cases = [2, 0, 1, 1, 1, 2];
    return titles[(number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5]];
}

/* --- Input number --- */

$('body').on('keyup', '.input_number', function () {
    if (this.value.match(/[^0-9.]/g)) {
        this.value = this.value.replace(/[^0-9.]/g, '');
    }
});

/* --- // --- */


// Добавление стоимости по весу в каталоге товаров
$('body').on('click', '.catalogPriceAdd', function () {
    let btn = $(this);
    let i = parseInt(btn.attr('data-index'));
    
    btn.attr('disabled', true).addClass('loading');

    let link = $('.visualteam').attr('data-i');

    $.ajax({
        url: '/'+link+'/ajax',
        type: 'POST',
        dataType: 'json',
        data: {
            'action': 'catalogPriceAdd',
            'index': i
        },
        success: function (response) {
            if (response.success && response.html) {
                $('#prices-container').append(response.html);
                i++;
                btn.attr('data-index', i);
            } else {
                alert('Ошибка при добавлении цены');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX ошибка:', error);
            alert('Произошла ошибка при выполнении запроса');
        },
        complete: function () {
            btn.attr('disabled', false).removeClass('loading');
        }
    });

    return false;
});

// Удаление стоимости по весу в каталоге товаров
$('body').on('click', '.catalogPriceRemove', function () {
    let block = $(this).closest('.catalogPriceCard');
    block.remove();
});

// Подтверждение удаления
$('body').on('click', '.action.icon_delete', function () {
    let href = $(this).attr('href');
    modalConfirm('Подтвердить удаление?',function(){
        top.location.href=href;
    },function(){});

    return false;
});

$('body').on('click', '.image_delete', function () {
    let item = $(this).closest('.image_card');
    if(!item.length) item = $(this).closest('.file_card');
    modalConfirm('Подтвердить удаление?',function(){
        item.remove();
    },function(){});

    return false;
});

$('body').on('click', '.gallery_delete', function () {
    let item = $(this).closest('.image_card');
    let i = $(this).data('id');
    modalConfirm('Подтвердить удаление?',function(){
        item.hide();
        $('.gallery_del',item).val(i);
    },function(){});

    return false;
});

$('body').on('click', '.files_delete', function () {
    let item = $(this).closest('.file_card');
    let i = $(this).data('id');
    modalConfirm('Подтвердить удаление?',function(){
        item.hide();
        $('.files_del',item).val(i);
    },function(){});

    return false;
});


















$(document).ready(function() {
  // Создаем кнопку меню
  const menuToggle = `
    <div class="mobile-menu-toggle">
      <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g opacity="0.7">
          <path d="M0.75 13.3311H15.75" stroke="#202227" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M0.75 7.04053H15.75" stroke="#202227" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M0.75 0.75H15.75" stroke="#202227" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </g>
      </svg>
    </div>
  `;
  
  // Создаем оверлей для меню
  const menuOverlay = `<div class="sidebar-overlay"></div>`;
  
  // Добавляем элементы на страницу
  if($(window).width() < 768) {
      $('body').append(menuOverlay);
      $('.header').prepend(menuToggle);
  }
  
  // Обработчик клика по кнопке меню
  $('.mobile-menu-toggle').on('click', function(e) {
    e.stopPropagation();
    $('.sidebar').addClass('active');
    $('.sidebar-overlay').addClass('active');
    $('body, .content').css('overflow', 'hidden');
    $('.content').css('height', 'calc(100vh - 62px)');
  });
  
  // Закрытие меню по клику на оверлей или вне меню
  $('.sidebar-overlay').on('click', function() {
    closeMobileMenu();
  });
  
  $(document).on('click', function(e) {
    if ($(window).width() <= 767 && !$(e.target).closest('.sidebar').length && 
        !$(e.target).closest('.mobile-menu-toggle').length && $('.sidebar').hasClass('active')) {
      closeMobileMenu();
    }
  });
  
  // Закрытие меню по кнопке ESC
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('.sidebar').hasClass('active')) {
      closeMobileMenu();
    }
  });
  
  // Функция закрытия меню
  function closeMobileMenu() {
    $('.sidebar').removeClass('active');
    $('.sidebar-overlay').removeClass('active');
    $('body, .content').css('overflow', 'auto');
    $('.content').css('height', 'auto');
  }
  
  // Закрытие меню при клике на пункт меню (для мобильных)
  $('.sidebar .nav_item, .sidebar .nav_item2').on('click', function() {
    if ($(window).width() <= 767) {
      //closeMobileMenu();
    }
  });
  
  // Адаптация таблиц для мобильных
  function adaptTables() {
    if ($(window).width() <= 767) {
      $('.table_container').each(function() {
        if (!$(this).hasClass('mobile-adapted')) {
          $(this).addClass('mobile-adapted');
          $(this).wrap('<div class="table-mobile-wrapper"></div>');
        }
      });
    } else {
      $('.table_container').removeClass('mobile-adapted');
      $('.table-mobile-wrapper').contents().unwrap();
    }
  }
  
  // Инициализация при загрузке и изменении размера окна
  adaptTables();
  $(window).on('resize', adaptTables);
  
  // Улучшение UX для мобильных устройств
  if ('ontouchstart' in window || navigator.maxTouchPoints) {
    // Увеличиваем область клика для кнопок на мобильных
    $('.btn, .nav_item, .nav_item2').css('min-height', '44px');
    $('.btn, .nav_item, .nav_item2').css('min-width', '44px');
    
    // Добавляем активные состояния для касаний
    $('.btn, .nav_item, .nav_item2').on('touchstart', function() {
      $(this).addClass('touch-active');
    }).on('touchend touchcancel', function() {
      $(this).removeClass('touch-active');
    });
  }
});