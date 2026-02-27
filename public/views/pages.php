<?= $this->include('layouts/header'); ?>

    <main class="main">        
        <div class="model">
            <?= $this->include('components/breadCrumbs'); ?>

            <h1><?= $this->page->name ?></h1>

            <ul class="model__list">
                <li class="model__item">
                    <div class="model__text">
                        <?= $this->page->text ?>
                    </div>
                </li>
            </ul>

        </div>

    </main>

<?= $this->include('layouts/footer'); ?>