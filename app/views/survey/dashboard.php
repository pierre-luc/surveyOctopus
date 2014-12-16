<?php
namespace octopus\app\views\user;
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
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-5 col-md-offset-2">Titre</div>
                <div class="col-md-3">Action</div>
                <div class="col-md-2">Date de création</div>
            </div>
            <?php foreach( $sondages as $s ):?>
                <div class="row table-row">
                    <div class="col-md-5 col-md-offset-2"><?= $s->title?></div>
                    <div class="col-md-3 action">
                        <button class="btn btn-default navbar-btn btn-xs btn-inverse" type="button">Stats</button>
                        <button class="btn btn-default navbar-btn btn-xs btn-warning" type="button">Modifier</button>
                        <button class="btn btn-default navbar-btn btn-xs btn-danger" type="button">Supprimer</button>
                    </div>
                    <div class="col-md-2"><?= date( "d M Y H:i:s", $s->date) ?></div>
                </div>
            <?php endforeach;?>
        </div>
    </div>

<?php endif;?>