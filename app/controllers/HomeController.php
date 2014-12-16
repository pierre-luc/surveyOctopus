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
    }
}
