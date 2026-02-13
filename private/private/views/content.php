<?= $this->includePrivate('layouts/header'); ?>

    <div class="container">
        
        <?= $this->includePrivate('components/sidebar'); ?>

        <main class="main_content">

            <div class="header">
                <div class="logo logo_lindera"></div>
                <div class="header_actions">

                    <? if(isset($_GET['add']) || isset($_GET['edit']) || isset($_GET['copy']) || in_array(str_replace('/'.ADMIN_LINK.'/','',$_SERVER['REQUEST_URI']),['settings','director_quotes'])): ?>

                        <?php if (app\Models\Admins::canPublish()): ?>
                            <button type="button" name="publish" class="btn btn_green btn_publish dop none">
                                Опубликовать
                            </button>
                        <?php endif; ?>

                        <button class='btn btn_red save'>Сохранить</button>
                    <? endif; ?>

                    <a href="/" class="btn btn_gray" rel="external">
                        Перейти на сайт
                    </a>
                    <button class="btn btn_transparent logout">
                        Выйти
                    </button>
                </div>
            </div>

            <div class="content">
                <?= ($this->module) ?: 'Данный модуль не существует' ?>
            </div>
        </main>
    </div>

<?= $this->includePrivate('layouts/footer'); ?>