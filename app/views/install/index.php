<?php
namespace octopus\app\views\install;
use octopus\core\Router;
?>

<div class="login" style="">
    <div class="login-screen">
        <div class="login-icon">
            <img src="<?= Router::generate( 'img/icons/svg/clipboard.svg' );?>" alt="Welcome to Mail App">
            <h4><?= $appname?><small>Installation</small></h4>
        </div>

        <p class="lead">Configuration de la base de données</p>
        <p>
            Entrez ci-dessous les détails de connexions à votre base de données.
            Si vous ne les connaissez pas avec certitude, contactez votre hébergeur.
        </p>
        <div class="login-form">
            <form action="<?= Router::generate( 'install/database' );?>" method="post">

                <div class="form-group">
                    <input name="dbname" type="text" class="form-control login-field" value="<?= isset($dbname)?$dbname:'' ?>" placeholder="Nom de la base de données" id="login-name">
                    <label class="login-field-icon fui-list" for="login-name"></label>
                </div>

                <div class="form-group">
                    <input name="hostname" type="text" class="form-control login-field" value="<?= isset($hostname)?$hostname:'' ?>" placeholder="Hôte de la base de données" id="login-host">
                    <label class="login-field-icon fui-link" for="login-host"></label>
                </div>

                <div class="form-group">
                    <input name="login" type="text" class="form-control login-field" value="<?= isset($login)?$login:'' ?>" placeholder="Identifiant" id="login-login">
                    <label class="login-field-icon fui-user" for="login-login"></label>
                </div>

                <div class="form-group">
                    <input name="pass" type="password" class="form-control login-field" value="" placeholder="Mot de passe" id="login-pass">
                    <label class="login-field-icon fui-lock" for="login-pass"></label>
                </div>

                <input type="submit" class="btn btn-primary btn-lg btn-block" value="Lancer l'installation">
            </form>
        </div>
    </div>
</div>




