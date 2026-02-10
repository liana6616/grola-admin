<?php
header("HTTP/1.0 404 Not Found");
$this->body_class = 'body-error-404';
$this->place_header = true;
$this->header_active_all = false;

$error_title = $this->error_title ?? 'Страница не найдена';
$error_message = $this->error_message ?? 'Вы немного заблудились, но это не беда.';
?>
<?php include_once VIEWS.'/layouts/header.php' ?>

	<main>
	    <div class="container">
	        <div class="error-page columns">
	            <div class="error-left">
	                404
	            </div>
	            <div class="error-right">
	                <h1 class="error-title"><?= htmlspecialchars($error_title) ?></h1>
	                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
	                <p class="error-solution">
	                    Всегда для вас есть <a href="/">главная страница</a>.<br>
	                    Не благодарите!
	                </p>
	                
	                <div class="error-actions">
	                    <a href="/" class="btn btn-primary">На главную</a>
	                    <?php if (isset($_SESSION['admin'])): ?>
	                    <a href="/<?= ADMIN_LINK ?>" class="btn btn-outline">В админку</a>
	                    <?php endif; ?>
	                    <button onclick="history.back()" class="btn btn-link">Вернуться назад</button>
	                </div>
	            </div>
	        </div>
	    </div>
	</main>

<?php include_once VIEWS.'/layouts/footer.php' ?>