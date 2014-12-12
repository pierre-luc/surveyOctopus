<?php
namespace octopus\core;
use octopus\app\Debug;
use octopus\core\utils\MessageFormatter;

/**
 * Class Session
 * @package octopus\core
 *
 * Cette classe permet de manipuler facilement et rapidement les sessions.
 * Il est possible d'écrire de lire des valeurs dans la session.
 * Dé gérer des token jetables, utile par exemple pour sécuriser la faille CSRF.
 *
 * De gérer un ensemble de message préformatés à afficher. Comme par exemple,
 * les messages qui indiquent que l'on est bien déconnecté. Qu'une information
 * a bien été prise en compte ou supprimée.
 */
class Session {
    function __construct() {
        if ( !isset( $_SESSION ) ) {
            // ouverture de la session à l'instanciation.
            session_start();
        }
    }

    public function setBag( $message, $type = 'success' ) {
        $_SESSION['bag'] = array(
            'message' => $message,
            'type' => $type
        );
    }

    /**
     * Permet de récupérer un message dans la session et de retourner ce message
     * formatté en fonction de son type au format HTML.
     * Si aucun message n'est trouvé, null est retourné.
     * @return string
     */
    public function bag() {
        if ( !isset( $_SESSION['bag']['message'] ) ) {
            return null;
        }
        $bag = $_SESSION['bag'];
        $html =
            MessageFormatter::runCallback( $bag['type'], $bag['message'] );

        /* on supprime le  message puisqu'au prochain affiche de la page
         * sur laquelle le message doit être écrit ne devra plus être
         * présent.
         */
        $_SESSION['bag'] = array();
        return $html;

    }

    /**
     * Enregistre dans la session un couple clé, valeur.
     * @param $key
     * @param $value
     */
    public function put( $key, $value ) {
        $_SESSION[$key] = $value;
    }

    /**
     * Retourne la valeur associé à une clé dans la session. Si la clé n'est pas
     * trouvée, false est retourné. Si aucune clé n'est renseignée, la session
     * est renvoyée.
     * @param string $key
     * @return mixed
     */
    public function get( $key = null ) {
        if ( $key ) {
            if ( isset( $_SESSION[$key] ) ) {
                return $_SESSION[$key];
            }
            return false;
        }
        return $_SESSION;
    }

    /**
     * Ferme la session.
     */
    public function destroy() {
        session_destroy();
    }

    /**
     * Supprime une association clé, valeur de la session. Si aucune clé n'est
     * renseignée, la session est remise à zéro.
     * @param string $key
     */
    public function delete( $key = null ) {
        if ( $key == null ) {
            foreach ($_SESSION as $key => $value) {
                unset( $_SESSION[ $key ] );
            }
        } else {
            unset( $_SESSION[ $key ] );
        }
    }

    /**
     * Génère un token jetable dans la session via son nom. Le token est unique.
     * @param string $name
     * @return string
     */
    public function generateToken( $name = '' ) {
        $token = uniqid( rand(), true );
        $_SESSION[ $name . '_token' ] = $token;
        $_SESSION[ $name . '_token_time' ] = time();
        return $token;
    }

    /**
     * Vérifie la validité d'un token.
     * Retourne false:
     *  - si le token passé en argument n'est pas le même que la session.
     *  - si aucun token n'a été généré
     *  - si le token est expiré
     *  - si la requête ne provient pas du serveur hôte
     * @param $time
     *  durée de validité du token (en seconde)
     *
     * @param $referer
     *  adresse du serveur hôte
     *
     * @param string $name
     *  nom du token
     *
     * @param string $token
     *  la chaîne de caractères constituant le token complet
     * @return bool
     */
    public function checkToken( $time, $referer, $name = '', $token ) {
        // aucun token généré
        if( !isset( $_SESSION[ $name . '_token' ] )
            || !isset( $_SESSION[ $name . '_token_time' ] )
        ){
            return false;
        }
        // si le token en session n'est pas celui récupéré
        if( $_SESSION[$name.'_token'] != $token ) {
            Debug::debug($token);
            Debug::debug($_SESSION, 'die');
            return false;
        }
        // si le token est expiré
        if( $_SESSION[ $name . '_token_time' ] < ( time() - $time ) ){
            return false;
        }
        // si la requête ne provient pas du serveur hôte.
        if($_SERVER['HTTP_REFERER'] != $referer){
            return false;
        }
        return true;
    }

}