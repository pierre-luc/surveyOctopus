<?php
namespace octopus\core;
use octopus\app\Debug;

/**
 * Class Router
 * @package octopus\core
 *
 * Cette classe permet de convertir une url en un objet Request.
 * Les URLs sont au format MVC suivant:
 *  controller/action/param_1/.../param_k
 *
 * Elle permet surtout de traduire les URLs vers le motif précédent.
 *
 * Ainsi nous pourrions avoir comme URL
 * http://forum-exemple.fr/un-article-sur-le-mvc-1
 *
 * Vue par le système comme étant une URL
 * http://forum-exemple.fr/articles/view/1/un-article-sur-le-mvc
 *
 * Ce système permet de personnaliser les URLs, les rendre plus belles et
 * surtout plus lisibles.
 *
 * IMPORTANT: En réalité le terme URL désignera ici la partie sans le nom de
 * domaine et le http://
 */
class Router{
    private static $lowerAlphaNumeric = "[a-z0-9]";

    /*
     * Mémorise toutes les routes à parser
     */
    static $mapping = array();

    /**
     * Parse une url et persiste les données analysées dans un objet Request.
     * @param $url
     * @param $request
     *  Instance d'un objet Request
     */
    static function translate($url, Request $request){
        $url = urldecode( $url );

        $url = trim( $url, '/' );
        if( empty( $url ) ){
            if ( !Config::checkIfAlreadyInstalled() ) {
                /* le processus d'installation est automatiquement chargé
                 * si besoin est.
                 */
                Controller::redirect( 'install' );
                die();
            }
            if ( !isset( Router::$mapping[ 0 ] ) ) {
                Kernel::error( "Votre routage n'est pas initialisé.\n"
                    . "Veuillez créer une route pour la racine /"
                );
            }
            // si l'url est vide nous prenons la première enregistrée
            $url = Router::$mapping[ 0 ][ 'url' ];
        } else {
            $matched = false; // aucune route n'a été trouvée
            foreach( Router::$mapping as $route ){ // parcours des routes
                /* si aucune route n'a été trouvée et que l'url match avec la
                 * route courante, l'url est traduite.
                 */
                if( !$matched
                        && preg_match(
                        $route[ 'regex_redirection' ], $url, $match)
                ){
                    $url = $route[ 'realurl' ];
                    /* traduction
                     * L'utilisation des sous-masques nommés est justifiée ici.
                     * En effet cela permet de remplacer le bon paramètre
                     * par sa valeur dans l'url.
                     */
                    foreach( $match as $k => $v ){
                        $url = str_replace(':'.$k.':', $v, $url);
                    }
                    $match = true;
                }
            }
        } // url traduite au format controller/action/param_1/.../param_k

        /*
         * Analyse de l'url afin de paramétrer request.
         */
        $params = explode( '/', $url );
        $request->setControllerName( $params[ 0 ] );
        /* s'il y a juste le controleur de renseigner, l'action par défaut est
         * par convention index.
         */
        $action = isset( $params[1] ) ? $params[ 1 ] : 'index';
        $request->setAction( $action );

        $request->setParams( array_slice( $params, 2) );
    }

    /**
     * Connecte une url à une action particulière.
     * @param $alias
     *  Pattern définissant comment devra être reconnue une url. L'écriture de
     * ce pattern permet de nommer des paramètres. Par exemple:
     *
     *  - une/chaine
     *  - une/chaine/et/un/:param_1
     *  - une/chaine/:param_1/une/autre/chaine/:param_2
     *
     * @param string $url
     *  Pattern définissant un motif pour une route. Il est possible de
     *  paramétrer cette expression. Comme suit:
     *
     *  controller/action/param_1:(regex_1)/.../param_k:(regex_k)
     *
     * Par exemple:
     *  Le pattern suivant
     *  articles/view/id:([0-9]+)/slug:([a-z0-9\-]+)'
     *
     *  a été écrit si l'on souhaite exécuter l'action view du controleur
     *  articles. Il est préciser que cette url devra contenir deux paramètres.
     *  Ici on nomme, pour les besoins du traitement, chaque paramètres.
     *  Le premier paramètre se nomme id sera uniquement consitué de chiffres.
     *  Un ou plusieurs. Puis le second se nomme slug et correspond à la partie
     *  lisible du lien de l'article. Il est constitué de chiffres et de lettres
     *  miniscules ainsi que de traits d'unions uniquement.
     */
    static function map($alias, $url){
        // construction de la route alias <-> url
        $r = array();
        $r[ 'params' ] = array(); // mémorise les paramètres de l'url
        $r[ 'url' ] = $url; // mémorise l'url

        /* construction de l'expression régulière qui reconnait l'url MVC et qui
         * identifie les noms des paramètres.
         *
         * Pour chaque nom de paramètre trouvé, son nom est récupéré afin de
         * créer un sous-masque et l'expression associé au sous-masque est celle
         * associé au paramètre trouvé.
         *
         * L'usage des sous-masques est très pratique puisque cela permet via
         * un preg_match de récupérer un tableau associatif dans lequel les clés
         * sont nommées avec le nom du sous-masque. S'il n'y a pas de nom il
         * s'agit du numéro du sous-masque.
         *
         * Voir exemple 4 sur http://php.net/manual/fr/function.preg-match.php
         */
        $regex = preg_replace( '/(' . self::$lowerAlphaNumeric . '+):([^\/]+)/',
                               '(?P<${1}>${2})',
                               $url
        );

        /* on gère aussi le cas où à la fin de l'alias une étoile est lue.
         * Elle sert en fait à définir un alias de manière plus générale
         * qu'avec une expression régulière plus fine.
         *
         * Le sous-masque est nommé args, par défaut, puisque l'absence de nom
         * de paramètre ne permet pas le nommage automatique.
         */
        $regex = str_replace( '/*', '(?P<args>/?.*)', $regex );

        /* Finisaliation de l'écriture de l'expression régulière.
         * L'expression régulière peut reconnaitre une url MVC.
         */
        $regex = '/^' . str_replace( '/','\/', $regex ) . '$/';

        /* on stock l'expression régulière de l'url MVC afin de pouvoir générer
         * des url ne cassant pas le routage définit par l'utilisateur.
         */
        $r[ 'regex_realurl' ] = $regex;

        /* Ici on supprime les expressions régulières associées aux paramètres.
         * On récrit le nom du paramètre en l'encadrant par le symbole :
         */
        $realurl = preg_replace(
            '/(' . self::$lowerAlphaNumeric . '+):([^\/]+)/', ':${1}:', $url
        );

        $realurl = str_replace( '/*', ':args:', $realurl );

        $r[ 'realurl' ] = $realurl;

        /* analyse de l'url afin de construire la tableau des paramètres
         * nom_param => valeur
         */
        $params = explode( '/', $url );
        foreach( $params as $k => $v ){
            /*
             * S'il y a : nous avons un paramètre sinon soit un controleur
             * ou une action
             */
            if( strpos( $v, ':' ) ){
                $param = explode( ':', $v );
                $r[ 'params' ][ $param[ 0 ] ] = $param[ 1 ];
            }
        }

        /* Construction de l'url de routage avec son expression régulière.
         */
        $regex_redirection = $alias;

        $regex_redirection =
            str_replace( '/*','(?P<args>/?.*)', $regex_redirection );

        /* Les paramètres ont été lus et associés avec leurs expressions
         * régulières respectives.
         *
         * Pour chaque paramètre de l'url de routage on écrit un sous-masque
         * nommé correspondant au routage de l'url MVC
         */
        foreach( $r[ 'params' ] as $k => $v ){
            $regex_redirection =
                str_replace( ":$k", "(?P<$k>$v)", $regex_redirection );
        }

        /* écriture de l'expression régulière reconnaissant une url de
         * routage.
         */
        $regex_redirection =
            '/^' . str_replace( '/', '\/', $regex_redirection ) . '$/';
        $r[ 'regex_redirection' ] = $regex_redirection;

        $target = preg_replace(
            '/:(' . self::$lowerAlphaNumeric . '+)/', ':${1}:', $alias
        );

        $target = str_replace( '/*', ':args:', $target);
        $r[ 'target' ] = $target;

        self::$mapping[] = $r;
    }

    /**
     * Génère une URL de routage à partir d'une URL MVC
     * @param string $url
     *  URL MVC
     * @return string
     *  URL de routage
     */
    static function generate( $url = '' ){
        // émondage de la chaîne de caractères
        trim( $url, '/' );

        /* Parcours de la liste des routes enregistrées afin de trouver une
         * route correspondante à l'url mvc passée en paramètre.
         *
         * Si une route est trouvée alors elle est traduite en url de routage.
         */
        foreach( self::$mapping as $route ){
            if( preg_match( $route[ 'regex_realurl' ], $url, $match ) ){
                // route trouvée

                /* url de routage abstraite
                 * Les paramètres sont seulement nommés.
                 */
                $url = $route[ 'target' ];

                /* Remplacement des noms de paramètres par leurs valeurs.
                 */
                foreach( $match as $name => $value ){
                    $url = str_replace( ":$name:", $value, $url );
                }
            }
        }

        // construction de l'url de routage
        $url = URL_ROOT . '/' . $url;

        // encodage de l'url
        $frags = explode( '/', $url );
        foreach ( $frags as $f => $value ) {
            $frags[ $f ] = urlencode( $value );
        }
        return 'http://' . implode('/', $frags);
    }

    /**
     * Préfixe l'url mvc ou de routage avec l'url racine de l'application.
     * @param $url
     * @return string
     */
    static function root( $url ){
        trim( $url, '/' );
        return 'http://' . URL_ROOT . '/' . $url;
    }
}
