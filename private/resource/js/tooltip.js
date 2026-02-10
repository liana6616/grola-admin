$(document).ready(function() {
  let tooltipTimeout;
  let isTooltipVisible = false;
  let currentTooltipElement = null;
  const tooltip = $('#tooltip');
  
  // Позиционирование подсказки
  function positionTooltip(element, tooltipText) {
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
      if (isTooltipVisible) {
        positionTooltip(this, $(this).data('tooltip'));
      }
    });
  
  // Обновляем позицию при изменении размера окна
  $(window).on('resize', function() {
    if (isTooltipVisible && currentTooltipElement) {
      const tooltipText = $(currentTooltipElement).data('tooltip');
      if (tooltipText) {
        positionTooltip(currentTooltipElement, tooltipText);
      }
    }
  });
});