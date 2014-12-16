<?php
namespace octopus\app\views\user;
use octopus\core\Controller;
use octopus\core\Router;
?>
<div class="container">
    <div class="row"><br/></div>
    <div class="row">
        <div class="col-xs-12">
            <nav class="navbar navbar-inverse navbar-embossed" role="navigation">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-01">
                        <span class="sr-only">Toggle navigation</span>
                    </button>
                    <a class="navbar-brand" href="<?= Router::root( '' );?>">Survey Octopus</a>
                </div>
            </nav><!-- /navbar -->
        </div>
    </div>

    <div class="login">
        <div class="login-screen">
            <div class="login-icon">
                <img src="<?= Router::generate( 'img/icons/svg/pencils.svg' );?>" alt="Welcome to Mail App">
                <h4><?= $appname?><small>Connexion</small></h4>
            </div>

            <p class="lead">Se connecter</p>
            <p>
                Pour vous connecter, remplissez les champs suivant.
            </p>
            <?= Controller::getSession()->bag() ?>
            <div class="login-form">
                <form action="<?= Router::generate( 'connexion/identification' );?>" method="post">

                    <div class="form-group">
                        <input name="login" type="text" class="form-control login-field" value="<?= isset($login)?$login:'' ?>" placeholder="Identifiant" id="login-login">
                        <label class="login-field-icon fui-user" for="login-login"></label>
                    </div>

                    <div class="form-group">
                        <input name="pass" type="password" class="form-control login-field" value="" placeholder="Mot de passe" id="login-pass">
                        <label class="login-field-icon fui-lock" for="login-pass"></label>
                    </div>

                    <input type="submit" class="btn btn-primary btn-lg btn-block" value="Se connecter">
                </form>
            </div>
        </div>
    </div>


</div>