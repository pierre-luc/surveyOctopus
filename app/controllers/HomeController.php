<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;

class HomeController extends Controller {
    public function index() {
        $this->loadModel( 'user' );
        if ( User::isConnected() ) {
            $this->redirect( 'dashboard' );
        }
        $this->loadMessageFormatter( 'home' );


        $this->loadModel( 'sondage' );
        $this->loadModel( 'user' );
        $userModel = $this->getModel( 'user' );
        $users = $userModel->search();
        $sondageModel = $this->getModel( 'sondage' );
        $sondages = array();
        foreach ( $users as $user ) {
            $sondages[] = $sondageModel->getSondages( $user->id, array(
                'opened' => 1
            ) );
        }
        $this->sendVariables( 'sondages', $sondages );
    }
}
