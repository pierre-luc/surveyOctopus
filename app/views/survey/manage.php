<?php
namespace octopus\app\views\survey;
use octopus\core\Router;
use octopus\core\Controller;
?>
<div class="row"><br/></div>
<div class="row">
    <div class="login">
        <div class="login-screen">
            <div class="login-icon" style="position: fixed; top: 70px;">
                <img src="<?= Router::generate( 'img/icons/svg/mail.svg' );?>" alt="Welcome to Mail App">
                <h4><?= $appname?><small>&Eacute;dition d'un sondage</small></h4>
                <img id="manage_preloader" src="<?= Router::generate('img/preloader/barloader.gif') ?>" alt=""/>
                <button id="btnSave" class="btn btn-warning btn-lg btn-block disabled">Sauver</button>
            </div>

            <p class="lead">&Eacute;dition d'un sondage</p>

            <?= Controller::getSession()->bag() ?>
            <form action="<?= Router::generate( 'survey/create' );?>" method="post">
                <div class="login-form alone">
                    <div class="form-group">
                        <input name="title" type="text" class="form-control login-field" value="<?= $sondage->title ?>" placeholder="Titre" id="title">
                        <label class="login-field-icon fui-tag" for="title"></label>
                    </div>

                </div>
            </form>
            <div id="questions" class="container-fluid manage">

            </div>
            <div class="login-form">
                <div class="container-fluid">
                    <div class="row">
                        <p style="color:#2C3E50;">Ajouter une question</p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <button id="btnChoiceQuestion" class="btn btn-embossed btn-primary btn-block">&Agrave; choix</button>
                        </div>
                        <div class="col-md-6">
                            <button id="btnNumValueQuestion" class="btn btn-embossed btn-primary btn-block">&Agrave; valeur numérique</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
<div class="modal fade" id="alert" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Une erreur est survenue</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script src="<?= Router::root( 'js/manageSurvey.js' )?>"></script>
<script>
    manageSurvey.saveUrl = "<?= Router::generate( "survey/save/{$sondage->id}/{$sondage->slug}" )?>";
    manageSurvey.getQuestionsUrl = "<?= Router::generate( "survey/getQuestions/{$sondage->id}/{$sondage->slug}" )?>";
</script>