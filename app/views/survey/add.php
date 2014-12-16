<?php
namespace octopus\app\views\survey;
use octopus\core\Router;
use octopus\core\Controller;
?>
<div class="row"><br/></div>
<div class="row">
    <div class="login">
        <div class="login-screen">
            <div class="login-icon">
                <img src="<?= Router::generate( 'img/icons/svg/mail.svg' );?>" alt="Welcome to Mail App">
                <h4><?= $appname?><small>Nouveau sondage</small></h4>
            </div>

            <p class="lead">Création d'un nouveau sondage</p>

            <?= Controller::getSession()->bag() ?>
                <form action="<?= Router::generate( 'survey/create' );?>" method="post">
                    <div class="login-form">
                        <div class="form-group">
                            <input name="title" type="text" class="form-control login-field" value="" placeholder="Titre" id="title">
                            <label class="login-field-icon fui-tag" for="title"></label>
                        </div>
                        <input type="submit" class="btn btn-primary btn-lg btn-block" value="Créer">
                    </div>
                </form>
        </div>
    </div>

</div>