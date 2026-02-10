<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    	<meta http-equiv="X-UA-Compatible" content="ie=edge">

	<title><?= $this->title ?></title>
	<meta name="description" content="<?= $this->description ?>" >
	<meta name="keywords" content="<?= $this->keywords ?>" >

	<? if (!empty($this->canonical)) : ?>
		<link rel="canonical" href="<?= $this->canonical ?>">
	<? endif; ?>

	<meta name="format-detection" content="telephone=no">

	<meta property="og:type" content="website" />
	<meta property="og:title" content= "<?= $this->title ?>">
	<meta property="og:url" content= "https://<?= $_SERVER['SERVER_NAME'] ?>/<?= URI ?>">
	<meta property="og:description" content= "<?= $this->description ?>">
	<meta property="og:image" content = "https://<?= $_SERVER['SERVER_NAME'] ?>/public/src/images/logo.svg">

    <link itemprop="cssSelector" rel="stylesheet" href="/public/src/css/lib/swiper-bundle.min.css">
	<link itemprop="cssSelector" rel="stylesheet" href="/public/src/css/style.css?v=<?= rand() ?>">

	<link rel="shortcut icon" href="/public/src/images/favicon.svg" type="image/svg+xml">

</head>
<body class="<?= !empty($this->body_class) ? $this->body_class : '' ?>" itemscope="" itemtype="https://schema.org/WebPage">
	<?= $this->edit ?>

	<? if (isset($_SESSION['notice']) && !empty($_SESSION['notice'])): ?>
		<div class="notice none">
			<?= $_SESSION['notice'] ?>
		</div>
		<? unset($_SESSION['notice']); ?>
	<? endif; ?>
