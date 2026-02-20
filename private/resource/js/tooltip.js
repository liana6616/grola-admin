$(document).ready(function() {
  let tooltipTimeout;
  let isTooltipVisible = false;
  let currentTooltipElement = null;
  const tooltip = $('#tooltip');
  
  // Проверка, находится ли элемент внутри absolute-контейнера
  function isInAbsoluteContainer(element) {
    const $element = $(element);
    
    // Проверяем сам элемент и его родителей на position: absolute
    if ($element.css('position') === 'absolute') {
      return true;
    }
    
    // Проверяем родителей до .table_container
    return $element.parents().filter(function() {
      return $(this).css('position') === 'absolute' && 
             !$(this).hasClass('custom-tooltip'); // игнорируем сам тултип
    }).length > 0;
  }
  
  // Проверка, нужно ли отключить тултип
  function shouldDisableTooltip(element) {
    // Отключаем на мобильном разрешении (< 1600px) для элементов в absolute контейнерах
    if (window.innerWidth < 1600) {
      if (isInAbsoluteContainer(element)) {
        return true;
      }
      
      // Также отключаем для specific классов, которые позиционированы абсолютно
        const $element = $(element);
      if ($element.closest('.handler').length || 
          $element.closest('.actions').length ||
          $element.hasClass('handler') || 
          $element.hasClass('actions')) {
        return true;
      }
    }
    return false;
  }
  
  // Позиционирование подсказки
  function positionTooltip(element, tooltipText) {
    // Если тултип должен быть отключен — не позиционируем
    if (shouldDisableTooltip(element)) {
      return;
    }
    
    const $element = $(element);
    const elementRect = $element[0].getBoundingClientRect();
    const tooltipWidth = tooltip.outerWidth();
    const tooltipHeight = tooltip.outerHeight();
    
    // Минимальные отступы от краев экрана
    const margin = 10;
    const arrowSize = 12;
    
    // Рассчитываем возможные позиции
    const positions = {
      top: {
        top: elementRect.top - tooltipHeight - arrowSize,
        left: elementRect.left + (elementRect.width / 2) - (tooltipWidth / 2),
        class: 'top',
        fits: function() {
          return this.top > margin && 
                 this.left > margin && 
                 this.left + tooltipWidth < window.innerWidth - margin;
        }
      },
      bottom: {
        top: elementRect.bottom + arrowSize,
        left: elementRect.left + (elementRect.width / 2) - (tooltipWidth / 2),
        class: 'bottom',
        fits: function() {
          return this.top + tooltipHeight < window.innerHeight - margin && 
                 this.left > margin && 
                 this.left + tooltipWidth < window.innerWidth - margin;
        }
      },
      left: {
        top: elementRect.top + (elementRect.height / 2) - (tooltipHeight / 2),
        left: elementRect.left - tooltipWidth - arrowSize,
        class: 'left',
        fits: function() {
          return this.left > margin && 
                 this.top > margin && 
                 this.top + tooltipHeight < window.innerHeight - margin;
        }
      },
      right: {
        top: elementRect.top + (elementRect.height / 2) - (tooltipHeight / 2),
        left: elementRect.right + arrowSize,
        class: 'right',
        fits: function() {
          return this.left + tooltipWidth < window.innerWidth - margin && 
                 this.top > margin && 
                 this.top + tooltipHeight < window.innerHeight - margin;
        }
      }
    };
    
    // Ищем первую подходящую позицию
    let bestPosition = positions.top;
    for (let pos in positions) {
      if (positions[pos].fits()) {
        bestPosition = positions[pos];
        break;
      }
    }
    
    // Корректируем позицию по горизонтали для top и bottom
    if (bestPosition.class === 'top' || bestPosition.class === 'bottom') {
      bestPosition.left = Math.max(margin, bestPosition.left);
      bestPosition.left = Math.min(
        bestPosition.left, 
        window.innerWidth - tooltipWidth - margin
      );
      
      // Смещаем стрелочку, если подсказка сдвинута
      const arrowOffset = elementRect.left + (elementRect.width / 2) - bestPosition.left;
      const maxArrowOffset = tooltipWidth / 2 - arrowSize - 5;
      const clampedArrowOffset = Math.max(-maxArrowOffset, Math.min(arrowOffset, maxArrowOffset));
      
      // Добавляем смещение для стрелочки
      tooltip.css('--arrow-offset', clampedArrowOffset + 'px');
    }
    
    // Устанавливаем позицию
    tooltip
      .removeClass('top bottom left right')
      .addClass(bestPosition.class)
      .css({
        left: bestPosition.left + 'px',
        top: bestPosition.top + 'px'
      })
      .text(tooltipText);
  }
  
  // Показ подсказки
  function showTooltip(element) {
    // Проверяем, нужно ли отключить тултип
    if (shouldDisableTooltip(element)) {
      return;
    }
    
    const tooltipText = $(element).data('tooltip');
    if (!tooltipText) return;
    
    currentTooltipElement = element;
    isTooltipVisible = true;
    
    // Сначала устанавливаем текст, чтобы рассчитать размеры
    tooltip.text(tooltipText);
    
    // Позиционируем подсказку
    positionTooltip(element, tooltipText);
    
    clearTimeout(tooltipTimeout);
    tooltipTimeout = setTimeout(() => {
      tooltip.css({
        'opacity': '1',
        'transform': 'translateY(0)'
      });
    }, 50);
  }
  
  // Скрытие подсказки
  function hideTooltip() {
    isTooltipVisible = false;
    currentTooltipElement = null;
    
    tooltip.css({
      'opacity': '0',
      'transform': 'translateY(-10px)'
    });
    clearTimeout(tooltipTimeout);
  }
  
  // Обработчики событий
  $('.tooltip-trigger')
    .on('mouseenter', function(e) {
      showTooltip(this);
    })
    .on('mouseleave', function() {
      hideTooltip();
    })
    .on('mousemove', function(e) {
      if (isTooltipVisible && !shouldDisableTooltip(this)) {
        positionTooltip(this, $(this).data('tooltip'));
      }
    });
  
  // Обновляем позицию при изменении размера окна
  $(window).on('resize', function() {
    if (isTooltipVisible && currentTooltipElement) {
      // При ресайзе проверяем, не нужно ли отключить тултип
      if (shouldDisableTooltip(currentTooltipElement)) {
        hideTooltip();
      } else {
        const tooltipText = $(currentTooltipElement).data('tooltip');
        if (tooltipText) {
          positionTooltip(currentTooltipElement, tooltipText);
        }
      }
    }
  });
  
  // Дополнительно: отключаем тултипы при скролле на мобильном разрешении
  $(window).on('scroll', function() {
    if (window.innerWidth < 1600 && isTooltipVisible) {
      hideTooltip();
    }
  });
});