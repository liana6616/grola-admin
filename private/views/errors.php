<?php
$error_code = $this->error_code ?? 500;
$error_title = $this->error_title ?? 'Произошла ошибка';
$error_message = $this->error_message ?? 'Что-то пошло не так.';

// Устанавливаем соответствующий HTTP заголовок
if (isset($this->error_code)) {
    header("HTTP/1.0 {$error_code}");
} else {
    header("HTTP/1.0 500 Internal Server Error");
}

$this->body_class = 'body-error-' . $error_code;
$this->place_header = true;
$this->header_active_all = false;
?>
<!doctype html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title><?= $error_code ?> <?= htmlspecialchars($error_title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='shortcut icon' href='/private/src/images/favicon.ico'>
    <link href="https://fonts.googleapis.com/css?family=Golos&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Onest&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Aeonik+Pro&display=swap" rel="stylesheet">

    <link rel='stylesheet' href='/private/src/css/chosen.css?v=<?= rand() ?>'>
    <link rel='stylesheet' href='/private/src/css/main.css?v=<?= rand() ?>'>
</head>
<body>

	<main>
	    <div class="container error">
	        <div class="error-page columns">
	            <div class="error-left">
	                <?= $error_code ?>
	            </div>
	            <div class="error-right">
	                <h1 class="error-title"><?= htmlspecialchars($error_title) ?></h1>
	                <p class="error-message none"><?= htmlspecialchars($error_message) ?></p>
	                
	                <?php if (isset($this->error_solution)): ?>
	                <p class="error-solution"><?= htmlspecialchars($this->error_solution) ?></p>
	                <?php endif; ?>
	                
	                <div class="error-actions">
	                    <a href="/" class="btn btn-primary">На главную</a>
	                    <?php if (isset($_SESSION['admin']) && strpos($_SERVER['REQUEST_URI'], '/'.ADMIN_LINK) !== false): ?>
	                    <a href="/<?= ADMIN_LINK ?>" class="btn btn-outline">В админку</a>
	                    <?php endif; ?>
	                    <button onclick="history.back()" class="btn btn-link">Вернуться назад</button>
	                </div>
	            </div>
	            <?php if (isset($this->error_details) && (isset($_SESSION['admin']) || $_SERVER['REMOTE_ADDR'] == '127.0.0.1')): ?>
                <div class="error-details">
                    <details>
                        <summary>Подробности ошибки</summary>
                        <pre><?= htmlspecialchars($this->error_details) ?></pre>
                    </details>
                </div>
                <?php endif; ?>
	        </div>
	    </div>
	</main>

	<script src="/private/src/js/jquery.min.js" defer></script>
	<script src="/vendor/tinymce/tinymce/tinymce.min.js" defer></script>
	<script src="/private/src/js/main.js?v=<?= rand() ?>" defer></script>

</body>
</html>