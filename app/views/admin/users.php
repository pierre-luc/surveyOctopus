<?php
namespace octopus\app\views\admin;
use octopus\core\Router;
?>



<div class="row table">
    <div class="container">
        <div class="row">
            <div class="col-md-2">login</div>
            <div class="col-md-10">action</div>
        </div>
        <?php foreach ($users as $u): ?>
            <?php if ($u->role != 'admin' ):?>
                <div class="row table-row">
                    <div class="col-md-2"><?=$u->login;?></div>
                    <div class="col-md-10">
                            <a href="<?= Router::generate( "administration/remove/user/{$u->id}" )?>" class="btn btn-default navbar-btn btn-xs btn-danger">Supprimer</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>