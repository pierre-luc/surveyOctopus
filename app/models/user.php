<?php
namespace octopus\app\models;

use octopus\app\Debug;
use octopus\core\Config;
use octopus\core\Controller;
use octopus\core\Model;

/**
 * Class User
 * @package octopus\app\models
 *
 * Cette classe consitue un model pour les utilisateurs.
 */
class User extends Model {
    /**
     * Crée un compte utilisateur.
     *
     * la chaîne de caractères 'created' est retournée si le compte a bien été
     * créé. Si le compte utilisateur existait déja la chaîne de caractères
     * 'duplicate' est retournée.
     *
     * Le premier compte créé est le compte administrateur. Le seul et unique.
     *
     * @param $login
     *  login du nouvel utilisateur
     * @param $pass
     *  mot de passe du nouvel utilisateur
     * @return string
     */
    public function add( $login, $pass ) {
        $salt = sha1( Config::getAppName() );
        $data = array(
            'login' => htmlspecialchars( $login ),
            'pass'  => sha1( $salt . '_' . sha1( htmlspecialchars( $pass ) ) )
        );
        try {
            $result = $this->search();
            if ( empty( $result ) ) {
                $data[ 'role' ] = 'admin';
            }
            $this->create( $data );

            return 'created';
        } catch (\PDOException $e) {
            if ( $e->getCode() == 23000 ) {
                return 'duplicate';
            }
        }
    }

    /**
     * Retourne un objet représentant un utilisateur récupéré par son login et
     * son mot de passe.
     * Si le couple (login, mot de passe) n'est pas trouvé l'objet retourné vaut
     * null.
     * @param $login
     *  login de l'utilisateur
     * @param $pass
     *  mot de passe de l'utilisateur
     * @return mixed
     */
    public function getUser( $login, $pass ) {
        $salt = sha1( Config::getAppName() );
        $data = array(
            'login' => htmlspecialchars( $login ),
            'pass'  => sha1( $salt . '_' . sha1( htmlspecialchars( $pass ) ) )
        );

        $user = $this->searchOne( array(
            'conditions' => array(
                'login' => $data[ 'login' ],
                'pass'  => $data[ 'pass' ]
            )
        ) );

        return $user;
    }

    /**
     * Booléen indiquant si l'utilisateur est connecté.
     * @return bool
     */
    public static function isConnected() {
        return Controller::getSession()->get( 'user' ) != null;
    }

    /**
     * Booleén indiquant si l'utilisateur est un administrateur.
     * @pre
     *  isConnected()
     * 
     * @return bool
     */
    public static function isAdmin() {
        return Controller::getSession()->get( 'user' )->role == 'admin';
    }
}
