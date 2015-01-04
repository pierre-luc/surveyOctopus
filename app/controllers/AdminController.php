<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;

class AdminController extends Controller {
    public function index() {
        $this->redirectIfNotLogged();
        $this->setLayout( 'admin' );
    }

    public function users() {
        $this->redirectIfNotLogged();

        $this->setLayout( 'admin' );
        $userModel = $this->getModel( 'user' );
        $users = $userModel->search();

        $this->sendVariables( array(
            'users' => $users
        ) );
    }

    public function surveys() {
        $this->redirectIfNotLogged();

        $this->setLayout( 'admin' );
        $this->loadModel( 'sondage' );
        $sondageModel = $this->getModel( 'sondage' );
        $sondages = $sondageModel->search();

        $this->sendVariables( array(
            'sondages' => $sondages
        ) );
    }

    public function removeUser( $id ) {
        $this->redirectIfNotLogged();
        $id = htmlspecialchars( $id );
        $this->loadModel( 'answer' );
        $this->loadModel( 'question' );
        $this->loadModel( 'sondage' );
        $userModel = $this->getModel( 'user' );
        $sondageModel = $this->getModel( 'sondage' );
        $questionModel = $this->getModel( 'question' );
        $answerModel = $this->getModel( 'answer' );

        $sondages = $sondageModel->search( array(
            'conditions' => array(
                'user' => $id
            )
        ) );

        foreach ($sondages as $s) {
            $answerModel->removeAnswersWithSurveyId( $s->id );
            $questionModel->removeQuestionWithSurveyId( $s->id );
        }

        $sondageModel->delete( 'user', $id );
        $userModel->delete( 'id', $id );
        $this->redirect( 'admin/users' );
        die();
    }

    public function removeSurvey( $id ) {
        $this->redirectIfNotLogged();
        $id = htmlspecialchars( $id );
        $this->loadModel( 'answer' );
        $this->loadModel( 'question' );
        $this->loadModel( 'sondage' );

        $sondageModel = $this->getModel( 'sondage' );
        $questionModel = $this->getModel( 'question' );
        $answerModel = $this->getModel( 'answer' );

        $answerModel->removeAnswersWithSurveyId( $id );
        $questionModel->removeQuestionWithSurveyId( $id );

        $sondageModel->delete( 'id', $id );

        $this->redirect( 'admin/surveys' );
        die();
    }

    private function redirectIfNotLogged() {
        $this->loadModel( 'user' );
        if ( !User::isConnected() | !User::isAdmin() ) {
            $this->redirect( '' );
            die();
        }
    }
}
