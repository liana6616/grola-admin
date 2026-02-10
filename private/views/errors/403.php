<?php
use app\Models\Admins;

header("HTTP/1.0 403 Forbidden");
$this->body_class = 'body-error-403';
$this->place_header = true;
$this->header_active_all = false;

// Получаем дополнительную информацию об ошибке
$error_title = $this->error_title ?? 'Доступ запрещен';
$error_message = $this->error_message ?? 'У вас нет прав для доступа к этой странице';
$show_admin_info = Admins::isSuperAdmin() && isset($this->admin_info);
?>
<?php include_once VIEWS.'/layouts/header.php' ?>

	<main>
	    <div class="container">
	        <div class="error-page columns">
	            <div class="error-left">
	                403
	            </div>
	            <div class="error-right">
	                <h1 class="error-title"><?= htmlspecialchars($error_title) ?></h1>
	                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
	                
	                <div class="error-actions">
	                    <a href="/" class="btn btn-primary">На главную</a>
	                    <a href="/<?= ADMIN_LINK ?>" class="btn btn-outline">В админку</a>
	                    <button onclick="history.back()" class="btn btn-link">Вернуться назад</button>
	                </div>
	                
	                <?php if ($show_admin_info): ?>
	                <div class="error-admin-info">
	                    <small class="text-muted">
	                        <strong>Информация для администратора:</strong><br>
	                        <?= htmlspecialchars($this->admin_info) ?>
	                    </small>
	                </div>
	                <?php endif; ?>
	            </div>
	        </div>
	    </div>
	</main>

<?php include_once VIEWS.'/layouts/footer.php' ?>