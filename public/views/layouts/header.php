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

    <link rel="preload" href="./public/fonts/Aeonik-Pro-Bold.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/public/fonts/Aeonik-Pro-Medium.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/public/fonts/Aeonik-Pro-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/public/fonts/Gordita-Medium.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/public/fonts/Gordita-Regular.woff2" as="font" type="font/woff2" crossorigin>

		    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    
		
  <link itemprop="cssSelector" rel="stylesheet" href="/public/src/css/lib/swiper-bundle.min.css">
	<link itemprop="cssSelector" rel="stylesheet" href="/public/src/css/_normalize.css">
	<link itemprop="cssSelector" rel="stylesheet" href="/public/src/css/style.css?v=<?= time() ?>">
	<link itemprop="cssSelector" rel="stylesheet" href="/public/src/css/media.css?v=<?= time() ?>">

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

	  <header class="header">
        <a href="/index.php" class="header__logo-mob">
            <img src="/public/images/icons/logo-dark.svg" alt="Иконка лого">
        </a>
        <div class="header__wrapper-block">
            <div class="header__wrapper header__wrapper-mob">
                <a href="/index.php" class="header__logo">
                    <img src="/public/images/icons/logo-dark.svg" alt="Иконка лого">
                </a>
                <ul class="header__list">
                    <li class="header__item header__item-mob">
                        <a class="header__link" href="/index.php">Главная</a>
                    </li>
                    <li class="header__item">
                        <a class="header__link" href="/catalog.php">Каталог</a>
                    </li>
                    <li class="header__item header__item-link">
                        <a class="header__link filter" href="/about.php">О компании</a>
                    </li>
                    <li class="header__item header__item-link">
                        <a class="header__link filter" href="/contacts.php">Контакты</a>
                    </li>
                </ul>
            </div>

            <div class="footer__column-contacts footer__column-contacts-menu">
                <a class="footer__phone" href="tel:+78129509077">+7 (812) 950-90-77</a>
                <a class="footer__mail" href="mailto:grola@mail.ru">grola@mail.ru</a>
                <address class="footer__address">Санкт-Петербург, Складской проезд, д.4</address>
            </div>
            <div class="header__wrapper">
                <a class="header__soc-tel filter" href="tel:88129509077"> <span>+7 (812) 950-90-77</span>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.38567 1.04166H6.5115C6.63863 1.04166 6.76274 1.08042 6.86726 1.15279C6.97178 1.22515 7.05176 1.32766 7.0965 1.44666L8.30984 4.67166C8.34983 4.77831 8.35991 4.89386 8.339 5.00582L7.73067 8.26582C8.47817 10.0225 9.71234 11.2033 11.7582 12.2625L14.979 11.6375C15.0935 11.6154 15.2118 11.6258 15.3207 11.6675L18.5557 12.9008C18.6739 12.9459 18.7757 13.0258 18.8476 13.1299C18.9195 13.2341 18.958 13.3576 18.9582 13.4842V16.4708C18.9582 17.8258 17.7648 18.925 16.3682 18.6208C13.824 18.0675 9.10984 16.66 5.80817 13.3583C2.64484 10.1958 1.58484 5.82749 1.229 3.46666C1.02484 2.11832 2.10567 1.04166 3.38567 1.04166Z" fill="#2B2E3A" />
                    </svg>
                </a>

                <div class="header__wrapper-soc">
                    <a class="filter" href="#">
                        <svg class="header__soc-tg" width="19" height="16" viewBox="0 0 19 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.409341 7.64148L4.67925 9.09164L14.8165 2.8942C14.9634 2.80432 15.114 3.0039 14.9873 3.12056L7.31262 10.1847L7.02722 14.1396C7.00548 14.4404 7.36789 14.6079 7.58297 14.3964L9.94599 12.0728L14.2658 15.3429C14.7314 15.6955 15.4057 15.447 15.5313 14.8766L18.5392 1.21917C18.7108 0.440054 17.9473 -0.217389 17.2023 0.0679336L0.387615 6.50671C-0.139856 6.70871 -0.125489 7.45987 0.409341 7.64148Z" fill="#2B2E3A" />
                        </svg>
                    </a>
                    <a class="filter" href="#">
                        <svg class="header__soc-vk" width="20" height="13" viewBox="0 0 20 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.8931 13C4.0599 13 0.162399 8.12012 0 0H3.42286C3.53529 5.95996 6.05865 8.48448 8.0574 9.005V0H11.2805V5.14014C13.2543 4.91892 15.3277 2.57658 16.0273 0H19.2503C18.7132 3.17518 16.4646 5.51752 14.8656 6.48048C16.4646 7.26126 19.0256 9.3043 20 13H16.4521C15.6901 10.5275 13.7914 8.61461 11.2805 8.35435V13H10.8931Z" fill="#2B2E3A" />
                        </svg>
                    </a>
                </div>
                <button class="header__button-sum" onclick="openModal()" type="button">Рассчитать</button>
            </div>
        </div>

        <button class="header__button-menu" onclick="this.classList.toggle('active')">
            <svg class="header__button-menu-open" width="22" height="19" viewBox="0 0 22 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20.9336 15.9893C21.5223 15.9893 22 16.467 22 17.0557C21.9999 17.6443 21.5222 18.1211 20.9336 18.1211H9.86719C9.27855 18.1211 8.80089 17.6443 8.80078 17.0557C8.80078 16.467 9.27848 15.9893 9.86719 15.9893H20.9336ZM20.791 7.85254C21.458 7.85261 21.9988 8.39356 21.999 9.06055C21.999 9.72773 21.4582 10.2695 20.791 10.2695H1.20801C0.540833 10.2695 0 9.72773 0 9.06055C0.000224777 8.39355 0.540972 7.8526 1.20801 7.85254H20.791ZM20.791 0C21.4581 6.9415e-05 21.999 0.540874 21.999 1.20801C21.999 1.87519 21.4582 2.41595 20.791 2.41602H1.20801C0.540833 2.41596 0 1.8752 0 1.20801C5.47555e-05 0.540867 0.540867 5.77561e-05 1.20801 0H20.791Z" fill="#457FCA" />
            </svg>
            <svg class="header__button-menu-close" width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="1.41422" width="30" height="2" rx="1" transform="rotate(45 1.41422 0)" fill="#457FCA" />
                <rect y="21.2132" width="30" height="2" rx="1" transform="rotate(-45 0 21.2132)" fill="#457FCA" />
            </svg>
        </button>
            
    </header>