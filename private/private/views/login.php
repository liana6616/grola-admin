<?= $this->includePrivate('layouts/header'); ?>

<div class="login_page">
    <div class='login_modal'>

        <div class="visualteam logo_visualteam" data-i="<?= ADMIN_LINK ?>"></div>

        <h1>Авторизация</h1>
        <form action='/<?= ADMIN_LINK ?>' method='post' id='login_form'>
            <input type='hidden' name='action' value='adminLogin'>
            <input type='text' name='login' placeholder='Логин'>
            <input type='password' name='password' placeholder='Пароль'>
            <button type='submit' name='submit'>Войти</button>
        </form>
    </div>
</div>

<?= $this->includePrivate('layouts/footer'); ?>
