// Переключение табов
$('.migrator .tab-btn').click(function() {
    const tabId = $(this).data('tab');
    
    $('.migrator .tab-btn').removeClass('active');
    $(this).addClass('active');
    
    $('.migrator .tab-content').removeClass('active');
    $('.migrator #tab-' + tabId).addClass('active');
});


$(document).ready(function() {
    // Инициализация
    initMigratorScripts();
});

function initMigratorScripts() {
    // Обработчик для форм с выбором миграции
    $(document).on('submit', 'form[data-migration-selector]', function(e) {
        e.preventDefault();
        const form = $(this);
        const action = form.data('migration-selector');
        
        showMigrationSelector(form, action).then(function(shouldSubmit) {
            if (shouldSubmit) {
                form.off('submit').submit();
            }
        });
    });
}

/**
 * Показывает модальное окно с выбором миграции
 */
function showMigrationSelector(form, action) {
    return new Promise(function(resolve, reject) {
        const pendingMigrations = window.pendingMigrations || [];
        const appliedMigrations = window.appliedMigrations || [];
        const allMigrations = [...pendingMigrations, ...appliedMigrations].sort();
        
        if (allMigrations.length === 0) {
            showAlert('Нет доступных миграций', 'warning');
            resolve(false);
            return;
        }
        
        // Создаём модальное окно
        const modalId = 'migrationSelectorModal';
        createMigrationSelectorModal(modalId, allMigrations, pendingMigrations, appliedMigrations);
        
        // Показываем модальное окно
        $('#' + modalId).modal('show');
        
        // Обработчик кнопки подтверждения
        $('#' + modalId).on('click', '.btn-confirm', function() {
            const selectedMigration = $('#' + modalId + ' select').val();
            if (!selectedMigration) {
                showAlert('Пожалуйста, выберите миграцию', 'warning');
                return;
            }
            
            // Добавляем скрытое поле с выбранной миграцией
            $('<input>').attr({
                type: 'hidden',
                name: 'migration',
                value: selectedMigration
            }).appendTo(form);
            
            // Закрываем модальное окно
            $('#' + modalId).modal('hide');
            
            // Удаляем модальное окно из DOM
            setTimeout(function() {
                $('#' + modalId).remove();
            }, 500);
            
            resolve(true);
        });
        
        // Обработчик отмены
        $('#' + modalId).on('click', '.btn-cancel', function() {
            $('#' + modalId).modal('hide');
            setTimeout(function() {
                $('#' + modalId).remove();
            }, 500);
            resolve(false);
        });
    });
}

/**
 * Создаёт модальное окно для выбора миграции
 */
function createMigrationSelectorModal(modalId, allMigrations, pendingMigrations, appliedMigrations) {
    // Удаляем существующее модальное окно, если есть
    $('#' + modalId).remove();
    
    // Создаём HTML для модального окна
    let html = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Выбор миграции</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="migrationSelect">Выберите миграцию:</label>
                            <select id="migrationSelect" class="form-control" size="10" style="min-height: 200px;">
    `;
    
    // Добавляем опции
    allMigrations.forEach(function(migration) {
        const isPending = pendingMigrations.includes(migration);
        const isApplied = appliedMigrations.includes(migration);
        let status = '';
        
        if (isPending) status = ' (ожидает)';
        if (isApplied) status = ' (применена)';
        
        html += `<option value="${migration}">${migration}${status}</option>`;
    });
    
    html += `
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-cancel" data-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-primary btn-confirm">Выбрать</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Добавляем в DOM
    $('body').append(html);
    
    // Инициализируем Bootstrap модальное окно
    $('#' + modalId).modal({
        backdrop: 'static',
        keyboard: false
    });
}

/**
 * Показывает всплывающее уведомление
 */
function showAlert(message, type = 'info') {
    const alertId = 'migratorAlert_' + Date.now();
    const alertClass = type === 'warning' ? 'alert-warning' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'success' ? 'alert-success' : 'alert-info';
    
    const html = `
        <div id="${alertId}" class="alert ${alertClass} alert-dismissible fade show" role="alert" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    $('body').append(html);
    
    // Автоматически скрываем через 5 секунд
    setTimeout(function() {
        $('#' + alertId).alert('close');
    }, 5000);
}

/**
 * Подтверждение действия
 */
function confirmAction(message) {
    return confirm(message);
}

/**
 * Показывает модальное окно
 */
function showModal(modalId) {
    $('#' + modalId).modal('show');
}

/**
 * Закрывает модальное окно
 */
function closeModal(modalId) {
    $('#' + modalId).modal('hide');
}

/**
 * Выделяет все таблицы в чекбоксах
 */
function selectAllTables(modalId) {
    $('#' + modalId + ' .checkbox-item input[type="checkbox"]').prop('checked', true);
}

/**
 * Снимает выделение со всех таблиц в чекбоксах
 */
function deselectAllTables(modalId) {
    $('#' + modalId + ' .checkbox-item input[type="checkbox"]').prop('checked', false);
}

/**
 * Обновляет статус миграций (AJAX)
 */
function updateMigrationStatus() {
    $.ajax({
        url: '/migrator/get-status',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                window.pendingMigrations = response.pending || [];
                window.appliedMigrations = response.applied || [];
                
                // Обновляем UI если нужно
                if (typeof updateMigrationUI === 'function') {
                    updateMigrationUI(response);
                }
            }
        },
        error: function() {
            console.error('Failed to update migration status');
        }
    });
}

// Экспортируем функции для глобального использования
window.showMigrationSelector = showMigrationSelector;
window.confirmAction = confirmAction;
window.showModal = showModal;
window.closeModal = closeModal;
window.selectAllTables = selectAllTables;
window.deselectAllTables = deselectAllTables;
window.updateMigrationStatus = updateMigrationStatus;
window.showAlert = showAlert;