<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;

/**
 * Class AdminController
 * @package octopus\app\controllers
 *
 * Cette classe est le controleur pour la page d'administration.
 */
class AdminController extends Controller {

    /**
     * Controleur de la page principale de l'administration.
     * Vérifie si l'utilisateur est autorisé à accéder au contenu. Si non,
     * redirige vers la page principale.
     * Cette action définit le layout admin afin de préparer la vue.
     */
    public function index() {
        $this->redirectIfNotLogged();
        $this->setLayout( 'admin' );
    }

    /**
     * Controleur de la page de gestion des utilisateurs.
     * Vérifie si l'utilisateur est autorisé à accéder au contenu. Si non,
     * redirige vers la page principale.
     * Cette action prépare la vue en stockant la liste de tous les utilisateurs
     * dans la variables $users accessible dans la vue associée.
     */
    public function users() {
        $this->redirectIfNotLogged();

        $this->setLayout( 'admin' );
        $userModel = $this->getModel( 'user' );
        $users = $userModel->search();

        $this->sendVariables( array(
            'users' => $users
        ) );
    }

    /**
     * Controleur de la page de gestion des sondages.
     * Vérifie si l'utilisateur est autorisé à accéder au contenu. Si non,
     * redirige vers la page principale.
     * Cette action prépare la vue en stockant la liste de tous les sondages
     * dans la variables $sondages accessible dans la vue associée.
     */
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

    /**
     * Gère la suppression d'un utilisateur depuis l'administration.
     * Vérifie si l'utilisateur est autorisé à accéder au contenu. Si non,
     * redirige vers la page principale.
     *
     * La suppression de l'utilisateur assure la suppression de ses sondages
     * ainsi que les questions et réponses associées à ceux-ci.
     * @param $id
     *  id de l'utilisateur à supprimer.
     */
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

    /**
     * Gère la suppression d'un sondage depuis l'administration.
     * Vérifie si l'utilisateur est autorisé à accéder au contenu. Si non,
     * redirige vers la page principale.
     *
     * La suppression d'un sondage assure la suppression de des questions et
     * réponses associées à celui-ci.
     * @param $id
     *  id du sondage à supprimer.
     */
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

    /**
     * Vérifie si l'utilisateur est autorisé à accéder au contenu. Si non,
     * redirige vers la page principale.
     */
    private function redirectIfNotLogged() {
        $this->loadModel( 'user' );
        if ( !User::isConnected() | !User::isAdmin() ) {
            $this->redirect( '' );
            die();
        }
    }
}
