<?php
namespace octopus\core;
use octopus\app\Debug;
use octopus\app\controllers;
use octopus\Config;

class Kernel {
    private static $request;

    /**
     * Point d'entrée de l'application.
     * Charge le controleur associé à l'url de la requête. Puis exécute l'action
     * associé à l'url de la requête en lui passant les paramètres de la requête
     */
    public static function run() {
        self::$request = new Request();
        Router::translate( self::$request->getUrl(), self::$request );
        $controller = self::loadController();
        $action = self::$request->getAction();

        /* On cherche si l'action demandée est présente dans le controleur
         * demandé ou bien dans le controleur par défaut.
         */
        $found = in_array(
            $action,
            array_diff(
                get_class_methods( $controller ),
                get_class_methods( 'octopus\core\Controller' )
            )
        );
        if ( !$found ) { // on retourne une erreur si l'action n'est pas trouvée
            self::error(
                "Le controller " . self::$request->getControllerName()
                . " n'a pas de méthode " . $action
            );
        }

        /* Appel de la méthode dont le nom est dans $action sur le controleur
         * dont le nom est dans $controller. Les paramètres de la requête sont
         * passés en arguments à cette méthode.
         */
        call_user_func_array(
            array( $controller, $action ), self::$request->getParams() );
        $controller->render( $action );
    }

    /**
     * Génére une erreur 404 avec un message personnalisé.
     *
     * @param string $message
     *  message de l'erreur
     */
    public static function error( $message ){
        $controller = new Controller( self::$request );
        $controller->setSession( new Session() );
        $controller->loadMessageFormatter( '404' );
        $controller->error404( $message );
    }

    /**
     * Charge le controleur associé à l'url de la requête. Si le controleur
     * n'est pas trouvé, une erreur 404 est générée.
     * @return Controller
     */
    private static function loadController() {
        // génération du nom de la classe du controleur
        $name = ucfirst( self::$request->getControllerName() ) . 'Controller';

        // génération du chemin d'accès au controleur
        $file = CONTROLLERS . DS . $name . '.php';

        // si le controleur n'existe pas on génère une erreur 404
        if ( !file_exists( $file ) ) {
            self::error( 'Le <i>controller</i> <strong>'
                . self::$request->getControllerName() . '</strong>'
                . ' n\'existe pas'
            );
        }

        // chargement du controleur
        require $file;
        $name = '\octopus\app\controllers\\' . $name;
        $controller = new $name( self::$request );

        return $controller;
    }

    /**
     * Charge la ou les classes passées en paramètre.
     * @param $classes
     */
    public static function loadClasses( $classes ) {
        if ( is_array( $classes ) ) {
            foreach ($classes as $clazz ) {
                self::loadClasses( $clazz );
            }
        } else {
            $file = ROOT . DS . str_replace( '.', DS, $classes ) . '.php';
            require $file;
        }
    }
}