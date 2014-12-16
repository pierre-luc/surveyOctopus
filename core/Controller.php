<?php
namespace octopus\core;
use octopus\app\Debug;
use octopus\core\Config;

/**
 * Class Controller
 * @package octopus\core
 *
 * Cette classe est le controleur par défaut de l'application. Il définit le
 * comportement de l'application. Il est possible de rafiner ce comportement
 * en dérivant la classe.
 *
 * A chaque chargement d'un controleur une session est ouverte.
 */
class Controller {
    private $request;
    private $variables = array();
    private $layout = 'default';
    private $rendered = false;
    private $models = array();
    private static  $session;

    function __construct( Request $request = null ) {
        self::$session = new Session();
        if ( $request ) {
            $this->request = $request;
        }
    }

    /**
     * Génère la vue dont le chemin d'accès dans le répertoire app/views est
     * passé en argument.
     *
     * Les vues sont stockées dans le répertoire views du répertoire app.
     * Les vues sont regroupées dans des répertoires propres aux models qu'elles
     * représentent.
     * @param $view
     */
    public function render( $view ) {
        /* si la vue a déjà été générée il n'est pas nécessaire de la générer
         * une nouvelle fois.
         */
        if ( $this->rendered ) { return false; }

        /* l'utilisation d'extract se révèle pratique ici puisque cette fonction
         * prend un tableau associatif en entrée dont les clés sont des noms
         * de variable. Les valeurs associée aux clés sont les valeurs
         * des variables qui seront créée.
         *
         * L'utilisateur pourra créer des variables dans la vue depuis un
         * controleur envoyant un tableau associatif. Il les utilisera comme
         * telles sans passer par ce tableau.
         *
         * Exemple:
         *
         * extract( array( "myvar" => 42 ) );
         *
         * produira une variable de nom myvar de valeur 42
         *
         * $myvar == 42
         */
        $this->sendVariables( 'appname', Config::getAppName() );
        extract( $this->variables );

        // génération du chemin complet de la vue à charger
        if ( strpos( $view, '/' ) === 0 ) {
            $view = VIEWS . $view . '.php';
        } else {
            $view = VIEWS . DS
                . $this->request->getControllerName() . DS . $view . '.php';
        }
        // chargement de la vue
        ob_start();                           // on ouvre le buffer
        require $view;                      // la vue est chargée dans le buffer
        $content_for_layout = ob_get_clean();// le contenu du buffer est renvoyé

        // chargement du layout par défaut
        require VIEWS . DS . 'layouts' . DS . $this->layout . '.php';
        $this->rendered = true;
    }

    /**
     * Permet de créer des variables dans la vue.
     * Deux méthodes sont proposées pour créer des variables. Il est possible de
     * passer en argument en tableau associatif ou bien de passer le nom d'une
     * variable à créer ainsi que sa valeur. Par défaut la valeur est null.
     *
     * @param array $key
     *  tableau associatif contenant le nom des variables à créer ainsi que
     *  leurs valeurs.
     *
     *  Exemple:
     *      array( "var1" => "val1", "var2" => "val2" )
     *
     * @param string $key
     *  nom de variable à créer
     * @param mixed $value
     *  valeur de la variable à créer
     */
    public function sendVariables( $key, $value = null ) {
        if ( is_array( $key ) ) {
            $this->variables += $key; // concatènation du tableau
        } else {
            $this->variables[ $key ] = $value;
        }
    }

    /**
     * Charge un model via son nom.
     * @param string $name
     */
    public function loadModel( $name ) {
        $file = MODELS . DS . $name . '.php';

        // chargement du model
        require_once $file;

        // stockage de l'instance du model
        if ( !isset( $this->models[ $name ] ) ) {
            $className = 'octopus\app\models\\' . $name;
            $this->models[ $name ] = new $className();
        }
    }

    /**
     * Charge un formateur de message.
     * Les formateurs se trouvent dans le répertoire formatters dans app/views.
     *
     * @param $name
     *  nom du formateur
     */
    public function loadMessageFormatter( $name ) {
        require_once VIEWS . DS . 'formatters' . DS . $name . '.php';
        return $this;
    }

    /**
     * Génère une erreur 404 en respectant le protocol HTTP.
     * @param $message
     */
    public function error404( $message ) {
        header( "HTTP/1.0 404 Not Found" );
        $this->sendVariables( 'message', $message );
        $this->sendVariables( 'request', $this->request );
        $this->render( '/errors/404' );
        die();
    }

    /**
     * Permer d'appeller un controller depuis une vue.
     *
     * @param $controller
     *  nom du controlleur
     *
     * @param $action
     *  nom de la méthode à appeler sur le controleur
     *
     * @return mixed
     *  réponse de la méthode appelée.
     */
    public function call( $controller, $action ){
        $controller .= 'Controller';
        // chargement du controleur
        require_once CONTROLLERS . DS . $controller . '.php';
        $c = new $controller();
        return $c->$action();
    }

    /**
     * Génère une redirection en respectant le protocol HTTP.
     *
     * @param string $url
     * @param integer $code
     *  - 301 : Moved Permanently
     */
    public static function redirect( $url = '', $code = null ) {
        if ( $code == 301 ) {
            header( "HTTP/1.1 301 Moved Permanently" );
        }

        header( "Location: " . Router::generate( $url ) );
    }

    /**
     * Enregistre une session dans controleur.
     * @param Session $session
     */
    public static function setSession( Session $session ) {
        self::$session = $session;
    }

    public static function getSession() { return self::$session; }

    /**
     * Permet de changer la layout par défaut depuis une classe dérivée.
     * @param $layout
     *  nom du layout
     */
    protected function setLayout( $layout ) {
        $this->layout = $layout;
    }

    /**
     * Retourne les données transmises par la requête.
     * @return bool|\stdClass
     */
    public function getData() {
        return $this->request->getData();
    }

    /**
     * Permet de récupérer un model via son nom.
     * Retourne l'instance du model en question, null s'il n'a pas été chargé.
     * @param $name
     * @return Model
     */
    public function getModel( $name ) {
        return isset( $this->models[ $name ] ) ? $this->models[ $name ] : null;
    }
}
