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
                <form action="<?= Router::generate( 'connexion/identification' );?>" method="post">
                    <div class="login-form">
                        <div class="form-group">
                            <input name="title" type="text" class="form-control login-field" value="" placeholder="Titre" id="title">
                            <label class="login-field-icon fui-tag" for="title"></label>
                        </div>
                    </div>
                    <div class="login-form">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="form-group">
                                    <input name="question" type="text" class="form-control login-field" value="" placeholder="Question" id="question">
                                    <label class="login-field-icon fui-tag" for="question"></label>
                                </div>
                            </div>
                            <div class="row">

                                <select class="form-control select select-primary select-block mbl">

                                    <option value="0">Question à choix</option>
                                    <option value="1">Question à valeur numérique</option>

                                </select>

                            </div>
                            <div class="row">

                                <div class="tagsinput-primary">
                                    <input name="tagsinput" class="tagsinput" data-role="tagsinput" value="School, Teacher, Colleague" style="display: none;" placeholder="Réponse possible">
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
        </div>
    </div>

</div>