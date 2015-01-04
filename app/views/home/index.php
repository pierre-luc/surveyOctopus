<?php
namespace octopus\app\views\home;
use octopus\core\Controller;
use octopus\core\Router;
function pagination( $page, $previousLink, $nextLink, $countPages, $baseUrlPagination ) {
    ?>
    <div class="row">
        <div class="container">
            <div class="row">
                <div style="text-align: center;">
                    <div class="pagination">
                        <ul>
                            <li class="previous<?=isset($previousLink)?'':' disabled'?>">
                                <a href="<?=isset($previousLink)?$previousLink:'#'?>" class="fui-arrow-left"></a>
                            </li>
                            <?php for($i = 1; $i <= $countPages; $i++ ):?>
                                <li<?=$page == $i ? ' class="active"':''?>>
                                    <a href="<?="$baseUrlPagination/$i"?>"><?=$i?></a>
                                </li>
                            <?php endfor;?>
                            <li class="next<?=isset($nextLink)?'':' disabled'?>">
                                <a href="<?=isset($nextLink)?$nextLink:'#'?>" class="fui-arrow-right"></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
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
    <?php pagination($page, $previousLink, $nextLink, $countPages, $baseUrlPagination);?>
    <div class="row survey-grid">
        <div class="col-md-offset-1">
            <div class="container-fluid">
                <?php foreach ( $sondages as $s):?>
                    <a href="<?= Router::generate( "survey/respondent/{$s->id}/{$s->slug}" ); ?>">
                        <div class="col-md-3 col-sm-5  col-xs-12 cell">
                            <div><?= $s->title?></div>
                            <div><?= date( "d M Y H:i:s", $s->date) ?></div>
                        </div>
                    </a>
                <?php endforeach;?>
            </div>
        </div>
    </div>
    <?php pagination($page, $previousLink, $nextLink, $countPages, $baseUrlPagination);?>
</div>