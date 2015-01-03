<?php
namespace octopus\app\views\survey;
use octopus\core\Controller;
use octopus\core\Router;

function questionNumericView( $data ) {
    $values = explode(';', $data->criteres);
    $min = $values[0];
    $max = $values[1];
    $interval = "$min et $max";
    ?>
    <div id="question_<?= $data->orderNum;?>" class="row question" data-order="<?= $data->order;?>">
        <div class="login-form">
            <div class="container-fluid">
                <div class="row">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-11">
                                <p><b><?= $data->orderNum;?></b> <?= $data->text;?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group has-error">
                                <input name="<?=$data->token;?>>" type="text" class="form-control input-interval" placeholder="La valeur doit être comprise entre <?= $interval;?>" required checked data-min="<?=$min;?>" data-max="<?=$max;?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }

function questionChoiceView( $data ) {
    $answers = explode(';', $data->criteres);
    ?>
    <div id="question_<?= $data->orderNum;?>" class="row question" data-order="<?= $data->order;?>">
        <div class="login-form">
            <div class="container-fluid">
                <div class="row">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-11">
                                <p><b><?= $data->orderNum;?></b> <?= $data->text;?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group has-error">
                                <?php foreach( $answers as $k => $a ):?>
                                <label class="radio" for="radio<?=$k;?>">
                                    <input type="radio" name="<?=$data->token;?>" data-toggle="radio" value="<?=$k;?>" id="radio<?=$k;?>" required checked>
                                    <?=$a;?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }
?>

<div class="container-fluid">
    <div class="row"><br/></div>
    <div class="row">
        <div class="col-xs-12">
            <nav class="navbar navbar-inverse navbar-embossed" role="navigation">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-01">
                        <span class="sr-only">Toggle navigation</span>
                    </button>
                    <a class="navbar-brand" href="<?= Router::root('');?>">Survey Octopus</a>
                </div>
                <div class="collapse navbar-collapse" id="navbar-collapse-01">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="<?= Router::generate( 'connexion' );?>">Se connecter</a></li>
                        <li><a href="<?= Router::generate( 'inscription' );?>">Inscription</a></li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </nav><!-- /navbar -->
        </div>
    </div>
    <div class="row">
        <form action="<?=Router::generate( "survey/getSurvey/$sondageId/$sondageSlug" );?>" method="post">
            <div class="login">
                <div class="login-screen">
                    <div class="login-icon" style="position: fixed; top: 120px;">
                        <img src="<?= Router::generate( 'img/icons/svg/mail.svg' );?>" alt="Welcome to Mail App">
                        <h4><?= $appname?><small>Sondage anonyme</small></h4>
                        <img id="manage_preloader" src="<?= Router::generate('img/preloader/barloader.gif') ?>" alt=""/>
                        <?php if ( $sondageOpened ): ?>
                            <input type="submit" id="btnSave" class="btn btn-warning btn-lg btn-block" value="Envoyer">
                        <?php endif; ?>
                    </div>

                    <p class="lead"><?= $sondageTitle; ?></p>
                    <?=Controller::getSession()->bag()?>
                    <div id="questions" class="container-fluid manage">
                        <?php foreach( $questions as $q ){
                            switch( $q->type ) {
                                case 'choice':
                                    questionChoiceView( $q );
                                break;
                                case 'numeric':
                                    questionNumericView( $q );
                                break;
                                default:
                                    // rien à faire
                            }
                        }?>
                    </div>

                </div>
            </div>
        </form>

    </div>

</div>
<script>
    $(document).ready(function(){
        $(':radio').radiocheck('uncheck');
        $('.input-interval').each(function(){
            $(this).keyup(function(){
                var min = $(this).attr( 'data-min' );
                var max = $(this).attr( 'data-max' );
                var val = parseInt( $(this).val() );
                if ( min <= val && val <= max ) {
                    $(this).parent().removeClass( 'has-error' );
                    $(this).parent().addClass( 'has-success' );
                } else {
                    $(this).parent().addClass( 'has-error' );
                    $(this).parent().removeClass( 'has-success' );
                }
            });
        });
    });
</script>