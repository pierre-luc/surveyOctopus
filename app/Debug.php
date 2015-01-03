<?php
namespace octopus\app;

/**
 * Class Debug
 * @package octopus\app
 *
 * Cette classe permet d'activer le mode debug afin de faciliter
 * l'affichage des erreurs en developpement.
 * Elle permet également de dés/activer l'affichage des erreurs
 * que peut afficher PDO.
 */
class Debug {
    static $debug = 0;
    static $pdoDebugMode =
    //*
        \PDO::ERRMODE_EXCEPTION;
        /*/
        \PDO::ERRMODE_WARNING;
    //*/

    /**
     * Cette méthode permet d'afficher le détail d'une erreur et permet
     * de remonter sa trace. Le tout, un peu plus joli que les erreurs
     * php habituelles.
     *
     * @param $var
     * @param null $die
     */
    static function debug( $var, $die = null ) {
        if ( Debug::$debug < 1 ) { return false; }

        $debug = debug_backtrace();
        echo '<p><a href="#" onclick="$(this).parent().next(\'ol\').slideToggle(); return false;"><strong>' . $debug[0]['file'] . ' </strong>ligne:' . $debug[0]['line'] . '</a></p><ol style="padding-left: 35px; display:none;">';
        foreach ($debug as $key => $value) {
            if ( $key > 0) {
                echo '<li><a href="#"><strong>' . $value['file'] . ' </strong>ligne:' . $value['line'] . '</a></li>';
            }
        }
        echo '</ol><pre>';
        print_r( $var );
        echo '</pre>';

        if ( $die === 'die' ) { die(); }

    }

    /**
     * Affiche en bas de page le temps d'exécution avant affichage de la page.
     * @param $debut
     */
    static function debug_pageGenerated( $debut ) {
        if ( Debug::$debug < 1 ) { return false; }

        ?>
        <div style="position: fixed; bottom: 0; left: 0; right: 0; background: #900; color: #fff; line-height: 30px; height: 30px; padding-left: 10px;">
            <?php echo 'Page générée en ' . round( microtime(true) - $debut, 5 ) . ' secondes.'; ?>
        </div>
    <?php
    }

}
