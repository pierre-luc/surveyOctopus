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
                <div class="col-md-1 col-md-offset-5">Activé</div>
                <div class="col-md-3">Date de création</div>
            </div>
            <?php foreach( $sondages as $s ):?>
                <div class="row table-row">
                    <div class="col-md-3"><?= $s->title?></div>
                    <div class="col-md-3 action col-md-offset-2">
                        <button class="btn btn-default navbar-btn btn-xs btn-inverse" type="button">Stats</button>
                        <a href="<?= Router::generate( "survey/manage/{$s->id}/{$s->slug}" )?>" class="btn btn-default navbar-btn btn-xs btn-warning">Modifier</a>
                        <button class="btn btn-default navbar-btn btn-xs btn-danger" type="button">Supprimer</button>
                    </div>
                    <div class="col-md-1">
                        <input type="checkbox" <?= $s->opened ? 'checked' : '' ?> data-toggle="switch" name="info-square-switch" data-on-color="warning" id="switch-05" />
                    </div>
                    <div class="col-md-3"><?= date( "d M Y H:i:s", $s->date) ?></div>
                </div>
            <?php endforeach;?>
        </div>
    </div>

<?php endif;?>