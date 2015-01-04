<?php
namespace octopus\app\views\survey;
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

<?php if ( $sondages == null ):?>
<div class="row empty-survey">
    <div class="col-md-8 col-md-offset-2">
        <h1>Aucun sondage</h1>
    </div>
</div>
<?php else: ?>

    <?php pagination($page, $previousLink, $nextLink, $countPages, $baseUrlPagination);?>
    <div class="row table">
        <div class="container">
            <div class="row hidden-xs">
                <div class="col-md-3 col-sm-3">Titre</div>
                <div class="col-md-2 col-md-offset-4 col-sm-offset-4 col-sm-3">Ouvert aux réponses</div>
                <div class="col-md-3 col-sm-3">Date de création</div>
            </div>
            <?php foreach( $sondages as $s ):?>
                <div class="row table-row">
                    <div class="col-md-3 col-sm-3"><?= $s->title?></div>
                    <div class="col-md-3 action col-md-offset-1 col-sm-4">
                        <a href="<?= Router::generate( "survey/stats/{$s->id}/{$s->slug}" )?>" class="btn btn-default navbar-btn btn-xs btn-inverse">Stats</a>
                        <a href="<?= Router::generate( "survey/manage/{$s->id}/{$s->slug}" )?>" class="btn btn-default navbar-btn btn-xs btn-warning">Modifier</a>
                        <a href="<?= Router::generate( "survey/remove/{$s->id}/{$s->slug}" )?>" class="btn btn-default navbar-btn btn-xs btn-danger">Supprimer</a>
                    </div>
                    <div class="col-md-1 col-sm-1">
                        <span id="switch<?=$s->id;?>" class="bootstrap-switch-square">
                            <input type="checkbox" <?= $s->opened ? 'checked' : '' ?> data-toggle="switch" name="square-switch" data-on-text="Oui" data-off-text="Non" data-value="<?=$s->id;?>" data-ajax="<?=Router::generate( "survey/activate/{$s->id}" )?>"/>
                        </span>
                            <img id="manage_preloader<?=$s->id;?>" class="manage_preloader" src="<?= Router::generate('img/preloader/barloader.gif') ?>" alt=""/>

                    </div>
                    <div class="col-md-3 col-md-offset-1 col-sm-offset-1 col-sm-3"><?= date( "d M Y H:i:s", $s->date) ?></div>
                </div>
            <?php endforeach;?>
        </div>
    </div>
    <?php pagination($page, $previousLink, $nextLink, $countPages, $baseUrlPagination);?>
    <script>
        $(document).ready(function(){
            $('input[data-toggle="switch"]').each(function(){
                var id = $(this).attr('data-value');

                var saveUrl = $(this).attr('data-ajax');
                $(this).parent().click(function(){
                    var checked;
                    var clazz = $(this).children('.bootstrap-switch').attr('class').split( ' ' );

                    for (var i in clazz) {
                        if ( clazz[ i ] == 'bootstrap-switch-off' ) {
                            checked = false;
                        } else if ( clazz[ i ] == 'bootstrap-switch-on') {
                            checked = true;
                        }
                    }

                    $('#manage_preloader' + id).css('visibility', 'visible');
                    $('#switch' + id).css('visibility', 'hidden');
                    $.post( saveUrl, {data:{opened:checked}}, function( res ) {
                        $('#manage_preloader' + id).css('visibility', 'hidden');
                        $('#switch' + id).css('visibility', 'visible');
                        if ( res.status == 'success' ) {

                        } else if (res.status == 'failure' ) {

                        }
                        console.log(res);
                    }, "json");
                });
            });
        });
    </script>
<?php endif;?>