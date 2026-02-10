/* --- Галерея --- */

jQuery.fn.spbrogatka = function() {
    var spbrogatkagal = $(this);
    var count = spbrogatkagal.length;
    var $window = $(window);
    var $body = $('body');
    var $html = $('html');
    
    // SVG иконки
    var svgIcons = {
        close: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40"><path fill="white" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>',
        closeHover: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40"><path fill="#ff6b6b" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>',
        prev: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40"><path fill="white" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>',
        prevHover: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40"><path fill="#4ecdc4" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>',
        next: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40"><path fill="white" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>',
        nextHover: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40"><path fill="#4ecdc4" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>',
        loading: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="50" height="50"><circle cx="50" cy="50" r="45" fill="none" stroke="white" stroke-width="8" stroke-linecap="round" stroke-dasharray="70 188"><animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="1s" repeatCount="indefinite"/></circle></svg>'
    };
    
    // Предзагруженные изображения
    var preloadedImages = {};
    var preloadQueue = [];

    // Предзагрузка изображений
    function preloadImages(startIndex) {
        // Очищаем предыдущую очередь
        preloadQueue = [];
        
        // Предзагружаем предыдущее, текущее и следующее изображения
        var indices = [
            (startIndex - 1 + count) % count,
            startIndex,
            (startIndex + 1) % count
        ];
        
        indices.forEach(function(index) {
            if (!preloadedImages[index]) {
                var element = spbrogatkagal.eq(index);
                var src = element.attr('href');
                
                var img = new Image();
                img.onload = function() {
                    preloadedImages[index] = {
                        img: this,
                        src: src,
                        title: element.attr('title') || ''
                    };
                };
                img.src = src;
                preloadQueue.push(img);
            }
        });
    }

    // Создание оверлея
    function createOverlay() {
        return $('<div>', {
            'class': 'gallery-overlay closegallery',
            css: {
                'z-index': 1111,
                position: 'fixed',
                top: 0,
                left: 0,
                display: 'block',
                width: '100%',
                height: '100%',
                'background-color': '#000',
                opacity: 0,
                cursor: 'pointer',
                transition: 'opacity 0.3s ease'
            }
        });
    }

    // Создание контейнера галереи
    function createGalleryContainer() {
        return $('<div>', {
            'class': 'gallery-container blockgallery',
            css: {
                'z-index': 4444,
                position: 'fixed',
                display: 'none',
                top: '50%',
                left: '50%',
                transform: 'translate(-50%, -50%) scale(0.9)',
                'background-color': '#000',
                'background-image': 'url(\'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="50" height="50"><circle cx="50" cy="50" r="45" fill="none" stroke="white" stroke-width="8" stroke-linecap="round" stroke-dasharray="70 188"><animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="1s" repeatCount="indefinite"/></circle></svg>\')',
                'background-position': 'center center',
                'background-repeat': 'no-repeat',
                'background-size': '50px 50px',
                width: '320px',
                height: '240px',
                opacity: 0,
                transition: 'all 0.3s ease',
                'max-width': '90vw',
                'max-height': '90vh'
            }
        });
    }

    // Настройка эффектов при наведении
    function setupHoverEffects(closeBtnId) {
        $('.closegallery')
            .hover(
                function() { 
                    $('#' + closeBtnId + ' .close-icon').html(svgIcons.closeHover); 
                },
                function() { 
                    $('#' + closeBtnId + ' .close-icon').html(svgIcons.close); 
                }
            );
        
        $('#prev')
            .hover(
                function() { 
                    $(this).find('.nav-icon').html(svgIcons.prevHover); 
                },
                function() { 
                    $(this).find('.nav-icon').html(svgIcons.prev); 
                }
            );
        
        $('#next')
            .hover(
                function() { 
                    $(this).find('.nav-icon').html(svgIcons.nextHover); 
                },
                function() { 
                    $(this).find('.nav-icon').html(svgIcons.next); 
                }
            );
    }

    // Обновление изображения с масштабированием
    function updateImage(img, element, container, navDiv, contentDiv, animate) {
        var imgwidth = img.width;
        var imgheight = img.height;
        var windowWidth = $window.width();
        var windowHeight = $window.height();
        
        // Для мобильных устройств
        var isMobile = windowWidth < 768;
        var padding = isMobile ? 20 : 50;
        var maxWidthOffset = isMobile ? 40 : 200;
        var maxHeightOffset = isMobile ? 100 : 50;
        
        // Масштабирование при превышении размеров окна
        if (imgwidth > windowWidth - maxWidthOffset) {
            var maxWidth = windowWidth - maxWidthOffset;
            var ratio = imgwidth / maxWidth;
            imgwidth = maxWidth;
            imgheight = imgheight / ratio;
        }
        
        if (imgheight > windowHeight - maxHeightOffset) {
            var maxHeight = windowHeight - maxHeightOffset;
            var ratio = imgheight / maxHeight;
            imgwidth = imgwidth / ratio;
            imgheight = maxHeight;
        }
        
        // Для очень маленьких изображений
        if (imgwidth < 100) imgwidth = 100;
        if (imgheight < 100) imgheight = 100;
        
        var title = element.attr('title') ? 
            '<div class="gal_text">' + element.attr('title') + '</div>' : '';
        
        var cssOptions = {
            width: imgwidth + 'px',
            height: imgheight + 'px',
            'max-width': '100%',
            'max-height': '100%'
        };
        
        // Применение стилей с анимацией
        if (animate) {
            container.css({
                'transition': 'all 0.3s ease',
                'opacity': 0
            });
            
            setTimeout(function() {
                navDiv.css(cssOptions);
                contentDiv.css(cssOptions);
                container.css($.extend({
                    'background-image': 'none',
                    'opacity': 1
                }, cssOptions));
                
                // Вставка изображения
                contentDiv.html(
                    '<img src="' + element.attr('href') + '" alt="" ' +
                    'class="gallery-image" style="width:100%; height:100%;" />' + title
                );
            }, 150);
        } else {
            navDiv.css(cssOptions);
            contentDiv.css(cssOptions);
            container.css($.extend({
                'background-image': 'none',
                'opacity': 1
            }, cssOptions));
            
            contentDiv.html(
                '<img src="' + element.attr('href') + '" alt="" ' +
                'class="gallery-image" style="width:100%; height:100%;" />' + title
            );
        }
    }

    // Обработка клавиатурной навигации
    function setupKeyboardNavigation(currentIndex, container, background, closeBtn, prevNav, navDiv, contentDiv) {
        var keyHandler = function(e) {
            switch(e.key) {
                case 'Escape':
                case 'Esc':
                    closeGallery();
                    break;
                case 'ArrowLeft':
                    navigateTo((currentIndex - 1 + count) % count);
                    break;
                case 'ArrowRight':
                    navigateTo((currentIndex + 1) % count);
                    break;
            }
        };
        
        function navigateTo(newIndex) {
            if (preloadedImages[newIndex]) {
                currentIndex = newIndex;
                var element = spbrogatkagal.eq(currentIndex);
                
                // Используем предзагруженное изображение
                var img = preloadedImages[newIndex].img;
                updateImage(img, element, container, navDiv, contentDiv, true);
                
                // Предзагружаем следующие изображения
                preloadImages(currentIndex);
            }
        }
        
        function closeGallery() {
            $('.blockgallery, .closegallery, #prev, #closegallery, .touch-swipe-area').remove();
            $(document).off('keydown', keyHandler);
            $html.removeClass('gallery-open');
        }
        
        $(document).on('keydown', keyHandler);
        
        return {
            navigateTo: navigateTo,
            close: closeGallery,
            currentIndex: currentIndex
        };
    }

    // Адаптивные стили для мобильных
    function addMobileStyles() {
        if ($('#gallery-mobile-styles').length === 0) {
            var styles = `
                <style id="gallery-mobile-styles">
                    @media (max-width: 767px) {
                        .gallery-container {
                            border-radius: 8px;
                            overflow: hidden;
                            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                        }
                        
                        .gal_text {
                            font-size: 14px;
                            padding: 10px;
                            text-align: center;
                            background: rgba(0,0,0,0.7);
                            color: white;
                            position: absolute;
                            bottom: 0;
                            left: 0;
                            right: 0;
                        }
                        
                        #prev {
                            background: none !important;
                        }
                        
                        #prev .nav-icon {
                            position: absolute !important;
                            left: 20px !important;
                            top: 50% !important;
                            transform: translateY(-50%) !important;
                        }
                        
                        #closegallery {
                            width: 60px !important;
                            height: 60px !important;
                            background: none !important;
                        }
                        
                        #closegallery .close-icon {
                            width: 30px !important;
                            height: 30px !important;
                        }
                        
                        .touch-swipe-area {
                            position: absolute;
                            top: 0;
                            width: 30%;
                            height: 100%;
                            z-index: 5555;
                        }
                        
                        .touch-swipe-left {
                            left: 0;
                        }
                        
                        .touch-swipe-right {
                            right: 0;
                        }
                        
                        .nav-button {
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                        }
                    }
                    
                    @media (max-width: 480px) {
                        .gallery-container {
                            max-width: 95vw !important;
                            max-height: 95vh !important;
                        }
                        
                        #prev .nav-icon,
                        #next .nav-icon {
                            width: 30px !important;
                            height: 30px !important;
                        }
                        
                        #closegallery .close-icon {
                            width: 25px !important;
                            height: 25px !important;
                        }
                    }
                    
                    /* Анимации */
                    .gallery-open {
                        overflow: hidden !important;
                    }
                    
                    .gallery-image {
                        animation: fadeIn 0.3s ease;
                    }
                    
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    
                    @keyframes scaleIn {
                        from { transform: translate(-50%, -50%) scale(0.9); opacity: 0; }
                        to { transform: translate(-50%, -50%) scale(1); opacity: 1; }
                    }
                    
                    .gallery-container.visible {
                        animation: scaleIn 0.3s ease forwards;
                    }
                    
                    /* Стили для SVG иконок */
                    .nav-icon {
                        width: 40px;
                        height: 40px;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    
                    .close-icon {
                        width: 40px;
                        height: 40px;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    
                    /* Навигационные кнопки */
                    .nav-button {
                        position: fixed;
                        top: 50%;
                        transform: translateY(-50%);
                        width: 60px;
                        height: 60px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        z-index: 3333;
                        background: rgba(0, 0, 0, 0.3);
                        border-radius: 50%;
                        transition: background-color 0.3s ease;
                    }
                    
                    .nav-button:hover {
                        background: rgba(0, 0, 0, 0.5);
                    }
                    
                    #prev {
                        left: 20px;
                    }
                    
                    #next {
                        right: 20px;
                    }
                    
                    #next .nav-icon {
                        transform: rotate(180deg);
                    }
                    
                    #closegallery {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: rgba(0, 0, 0, 0.3);
                        border-radius: 50%;
                        transition: background-color 0.3s ease;
                    }
                    
                    #closegallery:hover {
                        background: rgba(0, 0, 0, 0.5);
                    }
                </style>
            `;
            $body.append(styles);
        }
    }

    // Настройка свайпов для мобильных
    function setupTouchNavigation(container, navigateCallback) {
        var startX = 0;
        var startY = 0;
        var isSwiping = false;
        var minSwipeDistance = 50;
        
        container.on('touchstart', function(e) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isSwiping = true;
        });
        
        container.on('touchmove', function(e) {
            if (!isSwiping) return;
            
            var currentX = e.touches[0].clientX;
            var currentY = e.touches[0].clientY;
            var diffX = startX - currentX;
            var diffY = startY - currentY;
            
            // Если вертикальный свайп больше горизонтального - это скролл, не свайп
            if (Math.abs(diffY) > Math.abs(diffX)) {
                isSwiping = false;
                return;
            }
            
            e.preventDefault();
        });
        
        container.on('touchend', function(e) {
            if (!isSwiping) return;
            
            var endX = e.changedTouches[0].clientX;
            var diffX = startX - endX;
            
            if (Math.abs(diffX) > minSwipeDistance) {
                if (diffX > 0) {
                    // Свайп влево - следующее изображение
                    navigateCallback(1);
                } else {
                    // Свайп вправо - предыдущее изображение
                    navigateCallback(-1);
                }
            }
            
            isSwiping = false;
        });
    }

    // Закрытие галереи
    function closeGalleryElements() {
        $('.gallery-overlay').css('opacity', 0);
        $('.gallery-container').css({
            'opacity': 0,
            'transform': 'translate(-50%, -50%) scale(0.9)'
        });
        
        setTimeout(function() {
            $('.blockgallery, .closegallery, #prev, #closegallery, .touch-swipe-area, #next').remove();
            $(document).off('keydown.gallery');
            $html.removeClass('gallery-open');
        }, 300);
    }

    // Обработчик клика по элементу галереи
    spbrogatkagal.click(function(e) {
        e.preventDefault();
        
        var element = $(this);
        var currentIndex = spbrogatkagal.index(element);
        var src = element.attr('href');
        
        // Добавляем адаптивные стили
        addMobileStyles();
        
        // Предзагрузка изображений
        preloadImages(currentIndex);
        
        // Создание элементов
        var background = createOverlay();
        var container = createGalleryContainer();
        
        // Навигационные элементы
        var navDiv = $('<div>', {
            css: {
                'z-index': 4444,
                position: 'absolute',
                top: '0px',
                left: '0px',
                background: 'none',
                cursor: 'pointer',
                width: '100%',
                height: '100%'
            },
            'id': 'next',
            'class': 'gallerynav'
        });
        
        var contentDiv = $('<div>', {
            css: {
                'z-index': 3333,
                position: 'absolute',
                top: '0px',
                left: '0px',
                background: 'none',
                cursor: 'pointer',
                width: '100%',
                height: '100%'
            }
        });
        
        // Кнопка "Предыдущее"
        var prevNav = $('<div>', {
            'id': 'prev',
            'class': 'nav-button gallerynav',
            css: {
                cursor: 'pointer',
                position: 'fixed',
                top: '50%',
                left: '20px',
                transform: 'translateY(-50%)',
                'z-index': 3333,
                background: 'none'
            }
        }).html('<div class="nav-icon">' + svgIcons.prev + '</div>');
        
        // Кнопка "Следующее"
        var nextNav = $('<div>', {
            'id': 'next-btn',
            'class': 'nav-button gallerynav',
            css: {
                cursor: 'pointer',
                position: 'fixed',
                top: '50%',
                right: '20px',
                transform: 'translateY(-50%)',
                'z-index': 3333,
                background: 'none'
            }
        }).html('<div class="nav-icon">' + svgIcons.next + '</div>');
        
        // Кнопка закрытия
        var closeBtn = $('<div>', {
            'class': 'closegallery',
            'id': 'closegallery',
            css: {
                cursor: 'pointer',
                position: 'fixed',
                top: '20px',
                right: '20px',
                'z-index': 4444,
                width: '60px',
                height: '60px',
                background: 'none',
                display: 'flex',
                'align-items': 'center',
                'justify-content': 'center'
            }
        }).html('<div class="close-icon">' + svgIcons.close + '</div>');
        
        // Области для свайпа на мобильных
        var swipeLeftArea = $('<div>', {
            'class': 'touch-swipe-area touch-swipe-left'
        });
        
        var swipeRightArea = $('<div>', {
            'class': 'touch-swipe-area touch-swipe-right'
        });
        
        // Сборка структуры
        container.append(navDiv).append(contentDiv);
        
        // Добавление в DOM
        $body.append(background)
            .append(container)
            .append(prevNav)
            .append(nextNav)
            .append(closeBtn)
            .append(swipeLeftArea)
            .append(swipeRightArea);
        
        // Анимация появления
        setTimeout(function() {
            background.css('opacity', 0.7);
            container.css({
                'display': 'block',
                'opacity': 1,
                'transform': 'translate(-50%, -50%) scale(1)'
            }).addClass('visible');
        }, 10);
        
        // Блокируем скролл страницы
        $html.addClass('gallery-open');
        
        // Настройка событий
        setupHoverEffects('closegallery');
        
        // Настройка клавиатурной навигации
        var keyboardNav = setupKeyboardNavigation(
            currentIndex, container, background, closeBtn, prevNav, navDiv, contentDiv
        );
        
        // Загрузка и отображение изображения
        var img = new Image();
        img.onload = function() {
            updateImage(img, element, container, navDiv, contentDiv, false);
            preloadedImages[currentIndex] = {
                img: img,
                src: src,
                title: element.attr('title') || ''
            };
        };
        img.src = src;
        
        // Обработчик навигации
        function handleNavigation(direction) {
            var newIndex = (keyboardNav.currentIndex + direction + count) % count;
            var newElement = spbrogatkagal.eq(newIndex);
            
            if (preloadedImages[newIndex]) {
                keyboardNav.currentIndex = newIndex;
                var preloadedImg = preloadedImages[newIndex].img;
                updateImage(preloadedImg, newElement, container, navDiv, contentDiv, true);
                
                // Предзагружаем следующие изображения
                preloadImages(newIndex);
            }
        }
        
        // Обработчики кликов по кнопкам навигации
        $('.gallerynav').click(function() {
            var $this = $(this);
            if ($this.attr('id') === 'prev' || $this.attr('id') === 'next-btn') {
                var direction = $this.attr('id') === 'prev' ? -1 : 1;
                handleNavigation(direction);
            }
        });
        
        // Обработчики свайпов для мобильных
        if ($window.width() < 768) {
            setupTouchNavigation(container, handleNavigation);
            
            // Также делаем области для свайпа кликабельными
            swipeLeftArea.click(function() {
                handleNavigation(-1);
            });
            
            swipeRightArea.click(function() {
                handleNavigation(1);
            });
        }
        
        // Обработчик закрытия
        $('.closegallery').click(closeGalleryElements);
        
        // Добавляем обработчик клавиш с неймспейсом
        $(document).off('keydown.gallery').on('keydown.gallery', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                closeGalleryElements();
            } else if (e.key === 'ArrowLeft') {
                handleNavigation(-1);
            } else if (e.key === 'ArrowRight') {
                handleNavigation(1);
            }
        });
        
        return false;
    });
};

// Инициализация галереи
$(document).ready(function() {
    var selectors = [
        '[rel=gallery]',
        '.gallery'
    ];
    
    $(selectors.join(',')).spbrogatka();
});
/* --- // --- */