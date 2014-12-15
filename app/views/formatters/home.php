<?php
namespace octopus\app\views\formatters;
use octopus\core\utils\MessageFormatter;

MessageFormatter::addType( 'home_disconnect', function( $args ){
    extract($args);?>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <p class="palette palette-nephritis"><?= $message ?></p>
        </div>
    </div>
<?php
});
