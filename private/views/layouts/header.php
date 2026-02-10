<!doctype html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>Панель управления сайтом</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='shortcut icon' href='/private/src/images/favicon.ico'>
    <link href="https://fonts.googleapis.com/css?family=Golos&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Onest&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Aeonik+Pro&display=swap" rel="stylesheet">

    <link rel='stylesheet' href='/private/src/css/chosen.css?v=<?= rand() ?>'>
    <link rel='stylesheet' href='/private/src/css/main.css?v=<?= rand() ?>'>
</head>
<body>

<? if (!empty($_SESSION['notice'])) : ?>
    <div class='notice'><?= $_SESSION['notice'] ?></div>
    <?php $_SESSION['notice'] = '';
endif; ?>


<? if (!empty($_SESSION['error'])) : ?>
    <div class='notice'><?= $_SESSION['error'] ?></div>
    <?php $_SESSION['error'] = '';
endif; ?>
