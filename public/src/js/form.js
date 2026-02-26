document.addEventListener('DOMContentLoaded', function() {
    
    // Используем делегирование событий - вешаем обработчик на весь документ
    document.addEventListener('submit', function(e) {
        
        // Проверяем, что отправляется именно наша форма
        if (e.target && e.target.id === 'feedbackForm') {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            formData.append('action', 'formsAdd');
            formData.append('url', window.location.pathname);
            
            // Отладка
            console.log('Отправка формы из модального окна');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ':', pair[1]);
            }
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';
            
            fetch('/user/ajax', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Ответ:', data);
                if (data.success) {
                    alert('✓ ' + data.message);
                    form.reset();
                    
                    // Закрываем модальное окно
                    const modal = document.getElementById('modal-overlay');
                    if (modal) {
                        modal.style.display = 'none';
                        // или modal.classList.remove('active') - в зависимости от вашего кода
                    }
                } else {
                    alert('✗ ' + (data.message || 'Ошибка при отправке'));
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Ошибка при отправке. Попробуйте еще раз.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }
    });
    
});