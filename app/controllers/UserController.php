<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;

/**
 * Class UserController
 * @package octopus\app\controllers
 *
 * Controleur de la page user.
 */
class UserController extends Controller {
    /**
     * Controleur de la page d'inscription.
     */
    public function signup() {
        $this->loadMessageFormatter( 'signup' );
        $session = self::getSession();
        $login = $session->get( 'signup_login' );
        $session->delete( 'signup_login' );
        $this->sendVariables( 'login', $login );
    }

    /**
     * Controleur de la page de connexion.
     */
    public function signin() {
        $this->loadMessageFormatter( 'signup' );
        $session = self::getSession();
        $login = $session->get( 'signup_login' );
        $session->delete( 'signup_login' );
        $this->sendVariables( 'login', $login );
    }

    /**
     * Action permettant la création d'un nouvel utilisateur.
     *
     * Une erreur peut être envoyée à la vue dans le bag signup_err si:
     * - le mot de passe de confirmation est différent du mot de passe saisi.
     * - si le login existe déjà
     * - si une erreur interne est survenue
     *
     * Si le compte utilisateur a bien été créé un message est envoyé à la vue
     * dans le bag signup_ok
     *
     * L'utilisateur est redirigé vers la page de connexion si tout c'est bien
     * passé. Si non, vers la page d'inscription.
     */
    public function createUser() {
        $data = $this->getData();

        $session = self::getSession();

        if ( $data->pass != $data->pass2 ) {
            $session->setBag(
                'Le mot de passe de confirmation est différent du mot de passe',
                'signup_err'
            );
            $session->put( 'signup_login', $data->login );
            $this->redirect( 'inscription' );
        }

        $this->loadModel( 'user' );
        $userModel = $this->getModel( 'user' );
        $r = $userModel->add( $data->login, $data->pass );
        if ( $r == 'created' ) {
            $session->setBag(
                'Inscription réalisée avec succès',
                'signup_ok'
            );
            $this->redirect( 'connexion' );
        } elseif ( $r == 'duplicate' ) {
            $session->setBag(
                'Le login existe déjà', 'signup_err'
            );
            $session->put( 'signup_login', $data->login );
        } else {
            $session->setBag(
                'Une erreur interne est survenue', 'signup_err'
            );
            $session->put( 'signup_login', $data->login );
        }
        $this->redirect( 'inscription' );
    }

    /**
     * Action connectant un utilisateur.
     *
     * Si le login ou le mot de passe est incorrect, une erreur est envoyé à la
     * vue dans le bag signup_err. Puis l'utilisateur est redirigé vers la page
     * de connexion.
     *
     * Si l'authentification s'est bien passée, l'utilisateur est redirigé vers
     * la page de gestion des sondages.
     */
    public function connect() {
        $data = $this->getData();
        $session = self::getSession();
        $this->loadModel( 'user' );
        $userModel = $this->getModel( 'user' );

        $user = $userModel->getUser( $data->login, $data->pass );
        if ( $user == null ) {
            $session->setBag(
                'Votre login ou mot de passe est incorrect',
                'signup_err'
            );
            $this->redirect( 'connexion' );
        } else {
            $session->put( 'user', $user );
            $this->redirect( 'dashboard' );
        }
    }

    /**
     * Action déconnectant un utilisateur.
     *
     * Déconnecte l'utilisateur connecté et envoie un message de confirmation
     * à la vue dans le bag home_disconnect
     *
     * @post
     *  La session est détruite.
     *  Une nouvelle session contient le message de confirmation de déconnexion
     */
    public function disconnect() {
        $session = self::getSession();
        $session->delete();
        $session->setBag(
            'Vous êtes bien déconnecté', 'home_disconnect'
        );
        $this->redirect( '' );
    }
}