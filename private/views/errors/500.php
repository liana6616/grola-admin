<?php
header("HTTP/1.0 500 Internal Server Error");
$this->body_class = 'body-error-500';
$this->place_header = true;
$this->header_active_all = false;

$error_title = $this->error_title ?? 'Ошибка сервера';
$error_message = $this->error_message ?? 'Произошла внутренняя ошибка сервера.';
$show_tech_info = isset($this->technical_info) && (isset($_SESSION['admin']) || $_SERVER['REMOTE_ADDR'] == '127.0.0.1');
?>
<?php include_once VIEWS.'/layouts/header.php' ?>

	<main>
	    <div class="container">
	        <div class="error-page columns">
	            <div class="error-left">
	                500
	            </div>
	            <div class="error-right">
	                <h1 class="error-title"><?= htmlspecialchars($error_title) ?></h1>
	                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
	                <p class="error-solution">
	                    Пожалуйста, попробуйте обновить страницу позже.<br>
	                    Если проблема повторяется, свяжитесь с администратором.
	                </p>
	                
	                <div class="error-actions">
	                    <a href="/" class="btn btn-primary">На главную</a>
	                    <button onclick="location.reload()" class="btn btn-outline">Обновить страницу</button>
	                    <button onclick="history.back()" class="btn btn-link">Вернуться назад</button>
	                </div>
	                
	                <?php if ($show_tech_info): ?>
	                <div class="error-tech-info">
	                    <details>
	                        <summary>Техническая информация</summary>
	                        <pre><?= htmlspecialchars($this->technical_info) ?></pre>
	                    </details>
	                </div>
	                <?php endif; ?>
	            </div>
	        </div>
	    </div>
	</main>

<?php include_once VIEWS.'/layouts/footer.php' ?>