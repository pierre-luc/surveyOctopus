<?php
namespace octopus\core;
use octopus\core\utils\JSONConvertor;


/**
 * Class Conf
 * @package octopus
 *
 * Cette classe permet de configurer les connexions aux bases de données.
 *
 */
class Config {
    static $databases = null;
    private static $parameters;
    private static $routes;
    private static $appname = 'SurveyOctopus';

    /**
     * Charge les élements de connexion à la base de données qui se trouvent
     * dans le fichier parameters.json.
     */
    public static function loadParameters() {
        self::$parameters =
            JSONConvertor::parseFile( APP . DS . 'parameters.json' );
        self::loadDatabasesConfig();
    }

    /*
     * Initialise la variable de classe $database si elle ne l'est pas déjà.
     * @return bool
     */
    private static function loadDatabasesConfig() {
        if ( !isset( self::$parameters[ 'databases' ] ) ) { return false; }
        self::$databases = self::$parameters[ 'databases' ];
        return true;
    }

    /**
     * Effectue le mappage des routes à partir de leurs configurations dans le
     * fichier routes.json
     * @return bool
     */
    public static function loadRouteMap() {
        self::$routes =
            JSONConvertor::parseFile( APP . DS . 'routes.json' );
        if ( self::$routes == null ) { return false; }

        $map = self::$routes;
        foreach( $map as $target => $url ) {
            Router::map( $target, $url );
        }
        return true;
    }

    /**
     * Retourne le nom de l'application
     * @return string
     */
    public static function getAppName() {
        return self::$appname;
    }

    /**
     * Indique si l'installation à déjà été effectuée en retournant true si oui
     * et false sinon.
     * @return bool
     */
    public static function checkIfAlreadyInstalled() {
        if ( !isset( self::$databases ) ) {
            Config::loadParameters();
        }
        $dbname = Config::$databases[ 'default' ][ 'database' ];
        return ( file_exists( APP . DS . 'parameters.json' ) &&
            file_exists( APP . DS . 'database_installed.sql' )
        );
    }
}
