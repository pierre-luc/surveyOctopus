<?php
namespace octopus\app\controllers;

use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;

class SurveyController extends Controller {
    public function dashboard() {
        $this->setLayout( 'dashboard' );
        $this->redirectIfNotConnected();
        $user = $this->getSession()->get( 'user' );
        $this->loadModel( 'sondage' );
        $sondageModel = $this->getModel( 'sondage' );
        $sondages = $sondageModel->getSondages( $user->id );
        $this->sendVariables( 'sondages', $sondages );
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

    public function create() {
        $this->redirectIfNotConnected();
        $data = $this->getData();
        $user = $this->getSession()->get( 'user' );
        $this->loadModel( 'sondage' );
        $sondageModel = $this->getModel( 'sondage' );

        $sondageModel->add( $user->id, $data->title );

        $this->redirect( 'dashboard' );
    }
}