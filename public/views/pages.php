<?= $this->include('layouts/header'); ?>

<main class="main">
    <ul class="breadcrumps__wrapper">
        <li class="breadcrumps">
            <a href="index.php">Главная</a>
        </li>
        <li class="breadcrumps">
            <a href="">Политика в отношении обработки персональных данных</a>
        </li>
    </ul>
    
    <div class="policy">

        <h1><?= $this->page->name ?></h1>

        <? if(!empty($this->page->text)): ?>
            <div class="text" itemprop="articleBody">
                <?= $this->page->text ?>
            </div>
        <? endif; ?>

    </div>

</main>

<?= $this->include('layouts/footer'); ?>