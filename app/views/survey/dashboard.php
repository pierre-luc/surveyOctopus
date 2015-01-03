<?php
namespace octopus\app\views\survey;
use octopus\core\Router;
?>

<?php if ( $sondages == null ):?>
<div class="row empty-survey">
    <div class="col-md-8 col-md-offset-2">
        <h1>Aucun sondage</h1>
    </div>
</div>
<?php else: ?>
    <div class="row table">
        <div class="container">
            <div class="row">
                <div class="col-md-3">Titre</div>
                <div class="col-md-2 col-md-offset-4">Ouvert aux réponses</div>
                <div class="col-md-3">Date de création</div>
            </div>
            <?php foreach( $sondages as $s ):?>
                <div class="row table-row">
                    <div class="col-md-3"><?= $s->title?></div>
                    <div class="col-md-3 action col-md-offset-1">
                        <a href="<?= Router::generate( "survey/stats/{$s->id}/{$s->slug}" )?>" class="btn btn-default navbar-btn btn-xs btn-inverse">Stats</a>
                        <a href="<?= Router::generate( "survey/manage/{$s->id}/{$s->slug}" )?>" class="btn btn-default navbar-btn btn-xs btn-warning">Modifier</a>
                        <a href="<?= Router::generate( "survey/remove/{$s->id}/{$s->slug}" )?>" class="btn btn-default navbar-btn btn-xs btn-danger">Supprimer</a>
                    </div>
                    <div class="col-md-1">
                        <span id="switch<?=$s->id;?>" class="bootstrap-switch-square">
                            <input type="checkbox" <?= $s->opened ? 'checked' : '' ?> data-toggle="switch" name="square-switch" data-on-text="Oui" data-off-text="Non" data-value="<?=$s->id;?>" data-ajax="<?=Router::generate( "survey/activate/{$s->id}" )?>"/>
                        </span>
                            <img id="manage_preloader<?=$s->id;?>" class="manage_preloader" src="<?= Router::generate('img/preloader/barloader.gif') ?>" alt=""/>

                    </div>
                    <div class="col-md-3 col-md-offset-1"><?= date( "d M Y H:i:s", $s->date) ?></div>
                </div>
            <?php endforeach;?>
        </div>
    </div>
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