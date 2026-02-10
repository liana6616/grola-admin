// Управление модальными окнами
function showModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Закрытие модальных окон при клике вне их
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Подтверждение действия
function confirmAction(message) {
    return confirm(message || 'Вы уверены, что хотите выполнить это действие?');
}

// Выделение всех таблиц в модальном окне
function selectAllTables(modalId) {
    const containerId = modalId === 'createSeedsModal' ? 'createSeedsTables' : 'deleteSeedsTables';
    const checkboxes = document.querySelectorAll(`#${containerId} input[type="checkbox"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Снятие выделения всех таблиц в модальном окне
function deselectAllTables(modalId) {
    const containerId = modalId === 'createSeedsModal' ? 'createSeedsTables' : 'deleteSeedsTables';
    const checkboxes = document.querySelectorAll(`#${containerId} input[type="checkbox"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}