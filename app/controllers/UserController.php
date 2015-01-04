<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;


class UserController extends Controller {
    public function signup() {
        $this->loadMessageFormatter( 'signup' );
        $session = self::getSession();
        $login = $session->get( 'signup_login' );
        $session->delete( 'signup_login' );
        $this->sendVariables( 'login', $login );
    }

    public function signin() {
        $this->loadMessageFormatter( 'signup' );
        $session = self::getSession();
        $login = $session->get( 'signup_login' );
        $session->delete( 'signup_login' );
        $this->sendVariables( 'login', $login );
    }

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

    public function disconnect() {
        $session = self::getSession();
        $session->delete();
        $session->setBag(
            'Vous êtes bien déconnecté', 'home_disconnect'
        );
        $this->redirect( '' );
    }
}