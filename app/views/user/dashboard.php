<?php
namespace octopus\app\views\user;
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
                <div class="navbar-collapse collapse in" id="navbar-collapse-01">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="<?= Router::generate( 'user/disconnect' );?>">Déconnexion<span class="navbar-unread">1</span></a></li>
                    </ul>
                </div>
            </nav><!-- /navbar -->

        </div>
    </div>

</div>