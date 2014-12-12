<?php
namespace octopus\app\views\errors;
use octopus\app\Debug;
use octopus\core\utils\MessageFormatter;
use octopus\core\Router;

?>

<div class="container">
    <?php MessageFormatter::runCallback( '404', array( 'message' => $message ) );?>
    <?php MessageFormatter::runCallback( '404_debug', array( 'request' => $request ) );?>
</div>



