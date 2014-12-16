<?php
namespace octopus\app\views\home;
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
                    <a class="navbar-brand" href="#">Survey Octopus</a>
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
    <?= Controller::getSession()->bag()?>
    <div class="row">
        <div class="col-md-2 col-md-offset-2 col-sm-2 col-sm-offset-2 col-xs-2 col-xs-offset-2">
            <img src="<?= Router::generate( 'img/octopus.png' );?>" width="100%" alt=""/>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-6">
            <p>Survey octopus est une plateforme de sondage.</p>
        </div>
    </div>
</div>