<?php
namespace octopus\app\views\formatters;
use octopus\core\utils\MessageFormatter;
use octopus\app\Debug;
use octopus\core\Router;
/*
 * Exemples de formateurs.
 * Le controleur doit, avant de charger la vue, charger l'ensemble des formatter
 * qu'il aura besoin. Ainsi seul les formatter utile seront chargés.
 */
MessageFormatter::addType( '404', function( $args ){
    extract($args);
    if (Debug::$debug == 0):?>
        <div class="row" style="margin-top: 250px;"></div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-1 col-md-offset-2">
            <img src="<?= Router::root( 'img/icons/svg/toilet-paper.svg' );?>" alt="Toilet-Paper">
        </div>
        <div class="col-md-6">
            <p class="lead">
                Oups! Une erreur est survenue
            </p>
            <blockquote>Le serveur a retourné une erreur "<?= $message; ?>".</blockquote>
        </div>
    </div>
    <?php
});

MessageFormatter::addType( '404_debug', function( $args ){
    extract($args);
    if (Debug::$debug > 0):?>
    <div class="row">
        <div class="col-md-7 col-md-offset-2">
            <blockquote>
                <?php
                Debug::debug( array(
                    'Le controller ' . $request->getControllerName()
                    . ' n\'existe pas', $request, true
                ));
                ?>
            </blockquote>
        </div>
    </div>
    <?php endif;
});
