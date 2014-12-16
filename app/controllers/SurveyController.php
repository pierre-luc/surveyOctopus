<?php
namespace octopus\app\controllers;

use octopus\app\models\User;
use octopus\core\Controller;

class SurveyController extends Controller {
    public function dashboard() {
        $this->setLayout( 'dashboard' );
        $this->redirectIfNotConnected();
    }

    public function add() {
        $this->setLayout( 'dashboard' );
        $this->redirectIfNotConnected();
    }

    private function redirectIfNotConnected() {
        $this->loadModel( 'user' );
        if ( !User::isConnected() ) {
            $this->redirect( '' );
        }
    }
}