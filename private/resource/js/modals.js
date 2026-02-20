const $body = $('body');
const $window = $(window);

// Глобальная переменная для отслеживания типов модальных окон
let modalTypes = {};

// Функция создания модальных элементов
function createModal(i, type = 'default') {
    const $black = $('<div>', {
        class: 'black black' + i,
        css: { 'z-index': i + 100 },
        'data-i': i,
        'data-type': type
    });
    
    const $mod = $('<div>', {
        class: 'mod mod' + i,
        css: { 'z-index': i + 101 },
        'data-i': i,
        'data-type': type
    });
    
    const $modbox = $('<div>', {
        class: 'modbox modbox' + i,
        'data-i': i,
        'data-type': type
    });
    
    const $close = $('<div>', {
        class: 'close close' + i,
        'data-i': i
    });
    
    $mod.append($close, $modbox);
    $body.append($black, $mod);
    
    // Сохраняем тип модального окна
    modalTypes[i] = type;
    
    return { $black, $mod, $modbox, $close };
}

// Функция закрытия модального окна
function closeModal(i) {
    $(`.black${i}, .mod${i}`).remove();
    // Удаляем информацию о типе
    delete modalTypes[i];
    // Проверяем, есть ли еще открытые модальные окна
    if ($('.black').length === 0) {
        $body.removeClass('hiddens');
    }
}

// Закрытие по крестику
$body.on('click', '.close', function() {
    const $mod = $(this).closest('.mod');
    const i = $mod.data('i');
    const type = $mod.data('type');
    
    // Проверяем, можно ли закрывать по крестику
    if (type === 'alert' || type === 'confirm') {
        return; // Не закрываем alert и confirm по крестику
    }
    
    closeModal(i);
});

// Закрытие по кнопке "нет"
$body.on('click', '.no', function() {
    const $modbox = $(this).closest('.modbox');
    const i = $modbox.data('i');
    const type = $modbox.data('type');
    
    // Проверяем, можно ли закрывать
    if (type === 'alert' || type === 'confirm') {
        return; // Не закрываем alert и confirm по обычной кнопке "нет"
    }
    
    closeModal(i);
});

// Открытие диалога
$body.on('click', '.dialog', function() {

    const i = $('.black').length + 1;
    const modal = createModal(i, 'dialog');
    
    modal.$modbox.html('<img src="/priv/src/images/loading.gif" alt="" />');
    modal.$close.hide();
    win_auto(i);

    let link = $('.visualteam').attr('data-i');
    
    $.ajax({
        url: '/'+link+'/ajax',
        type: 'POST',
        data: {
            action: 'dialogLoad',
            class: $(this).data('fn'),
            type: $(this).data('t'),
            tp: $(this).data('tp'),
            p: $(this).data('p'),
            i: $(this).data('i')
        },
        success: function(html) {
            modal.$modbox.html(html);
            modal.$close.show();
            setTimeout(function() {
                win_auto(i);
            }, 100);
            if (typeof externalLinks === 'function') externalLinks();
        }
    });
    
    return false;
});

// Автоматическое позиционирование
function win_auto(i) {
    const $mod = $('.mod' + i);
    const $modbox = $('.modbox' + i);
    const h = $mod.height();
    
    if (h > ($window.height() - 50)) {
        const w = $mod.width();
        const w1 = w / 2 - w;
        const h1 = $window.height() - 50;
        
        $mod.css({
            'margin-left': w1,
            'height': h1 + 'px',
            'margin-top': '25px',
            'top': '0px'
        });
        
        $modbox.css({
            'height': h1 + 'px',
            'overflow-y': 'scroll'
        });
    } else {
        $mod.css({
            'height': 'auto',
            'top': '50%'
        });
        
        $modbox.css({
            'height': 'auto',
            'overflow-y': 'inherit'
        });
        
        const w = $mod.width();
        const w1 = w / 2 - w;
        //$mod.css('margin-left', w1);
        
        const h = $mod.height();
        const h1 = h / 2 - h;
        //$mod.css('margin-top', h1);
    }
}

// Обработка ресайза
$window.on('resize', function() {
    if ($('.modbox').length) {
        $('.modbox').each(function() {
            win_auto($(this).data('i'));
        });
    }
});

// =============== ДОБАВЛЕННЫЙ КОД ===============

// Закрытие модальных окон по клавише Esc с проверкой типа
$(document).on('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        // Закрываем последнее открытое модальное окно
        const modals = $('.mod');
        if (modals.length > 0) {
            const lastModal = modals.last();
            const i = lastModal.data('i');
            const type = lastModal.data('type');
            
            // Проверяем тип модального окна
            if (type === 'alert' || type === 'confirm') {
                // Не закрываем alert и confirm по Esc
                return;
            }
            
            closeModal(i);
        }
    }
});

// Функция для alert-окон (с кнопкой OK)
function modalAlert(message, callback) {
    const i = $('.black').length + 1;
    const modal = createModal(i, 'alert');
    
    const html = `
        <div class="alert-modal">
            <div class="alert-message">${message}</div>
            <div class="alert-buttons">
                <button class="alert-ok btn" data-i="${i}">OK</button>
            </div>
        </div>
    `;
    
    modal.$modbox.html(html);
    modal.$close.hide(); // Скрываем крестик для alert
    win_auto(i);
    
    // Обработчик кнопки OK
    modal.$modbox.on('click', '.alert-ok', function() {
        closeModal(i);
        if (typeof callback === 'function') {
            callback();
        }
    });
    
    return i;
}

// Функция для confirm-окон (с кнопками Да/Нет)
function modalConfirm(message, confirmCallback, cancelCallback) {
    const i = $('.black').length + 1;
    const modal = createModal(i, 'confirm');
    
    const html = `
        <div class="confirm-modal">
            <div class="confirm-message">${message}</div>
            <div class="confirm-buttons">
                <button class="confirm-yes btn" data-i="${i}">Да</button>
                <button class="confirm-no btn" data-i="${i}">Нет</button>
            </div>
        </div>
    `;
    
    modal.$modbox.html(html);
    modal.$close.hide(); // Скрываем крестик для confirm
    win_auto(i);
    
    // Обработчики кнопок
    modal.$modbox.on('click', '.confirm-yes', function() {
        closeModal(i);
        if (typeof confirmCallback === 'function') {
            confirmCallback();
        }
    });
    
    modal.$modbox.on('click', '.confirm-no', function() {
        closeModal(i);
        if (typeof cancelCallback === 'function') {
            cancelCallback();
        }
    });
    
    return i;
}

$(document).ready(function(){
    if($('.notice').length) {
        let html = $('.notice').html();

        const i = modalAlert(html);

        setTimeout(function(){
            closeModal(i);
        },1500);
    }
});