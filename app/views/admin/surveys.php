<?php
namespace octopus\app\views\admin;
use octopus\core\Router;
?>

<div class="row table">
    <div class="container">
        <div class="row">
            <div class="col-md-2">titre</div>
            <div class="col-md-10">action</div>
        </div>
        <?php foreach ($sondages as $s): ?>

            <div class="row table-row">
                <div class="col-md-2"><?=$s->title;?></div>
                <div class="col-md-10">
                        <a href="<?= Router::generate( "administration/remove/survey/{$s->id}" )?>" class="btn btn-default navbar-btn btn-xs btn-danger">Supprimer</a>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
</div>