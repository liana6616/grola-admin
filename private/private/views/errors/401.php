<?php
header("HTTP/1.0 401 Unauthorized");
$this->body_class = 'body-error-401';
$this->place_header = true;
$this->header_active_all = false;

$error_title = $this->error_title ?? 'Требуется авторизация';
$error_message = $this->error_message ?? 'Для доступа к этой странице необходимо войти в систему.';
?>
<?php include_once VIEWS.'/layouts/header.php' ?>

	<main>
	    <div class="container">
	        <div class="error-page columns">
	            <div class="error-left">
	                401
	            </div>
	            <div class="error-right">
	                <h1 class="error-title"><?= htmlspecialchars($error_title) ?></h1>
	                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
	                
	                <div class="error-actions">
	                    <?php if (strpos($_SERVER['REQUEST_URI'], '/'.ADMIN_LINK) === 0): ?>
	                    <a href="/<?= ADMIN_LINK ?>" class="btn btn-primary">Войти в админку</a>
	                    <?php else: ?>
	                    <a href="/login" class="btn btn-primary">Войти</a>
	                    <a href="/register" class="btn btn-outline">Зарегистрироваться</a>
	                    <?php endif; ?>
	                    <a href="/" class="btn btn-link">На главную</a>
	                </div>
	            </div>
	        </div>
	    </div>
	</main>

<?php include_once VIEWS.'/layouts/footer.php' ?>