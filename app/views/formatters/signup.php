<?php
namespace octopus\app\views\formatters;
use octopus\app\Debug;
use octopus\core\utils\MessageFormatter;

MessageFormatter::addType( 'signup_err', function( $args ){
    extract($args);?>
    <p class="palette palette-alizarin"><?= $message ?></p>
    <?php
});

MessageFormatter::addType( 'signup_ok', function( $args ){
    extract($args);?>
    <p class="palette palette-nephritis"><?= $message ?></p>
<?php
});
