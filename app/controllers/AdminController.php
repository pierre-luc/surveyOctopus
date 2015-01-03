<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;

class AdminController extends Controller {
    public function index() {
        $this->setLayout( 'admin' );
    }
}
