<?php
namespace octopus\app\controllers;

use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;
use octopus\core\Router;
use octopus\core\utils\JSONConvertor;

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

    /*
     * Redirige l'utilisateur vers la page principale s'il n'est pas connecté.
     * Utilisant le protocole HTTP avec la méthode redirect, si cela est permis.
     * Retourne un booléen indiquant si l'utilisateur est connecté ou non.
     *
     * @param String $onlyboolean
     *  Indique si l'on souhaite seulement récupérer l'état de connexion sans
     * rediriger.
     */
    private function redirectIfNotConnected( $onlyboolean = false ) {
        $this->loadModel( 'user' );
        if ( !User::isConnected() ) {
            if ( $onlyboolean ) {
                return false;
            } else {
                $this->redirect( '' );
            }
        }
        return true;
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

    public function manage( $id, $slug ) {
        $this->redirectIfNotConnected();
        $this->setLayout( 'dashboard' );

        $this->loadModel( 'sondage' );
        $sondageModel = $this->getModel( 'sondage' );
        $sondage = $sondageModel->searchOne( array(
            'conditions' => array(
                'id' => htmlspecialchars( $id ),
                'user' => $this->getSession()->get( 'user' )->id
            )
        ) );

        if ( $sondage == null ) {
            $this->redirect( 'dashboard' );
        }

        $this->sendVariables( array(
            'sondage' => $sondage
        ) );
    }

    public function getQuestions( $id, $slug ) {
        header('Content-type: application/json');
        $json = array();
        $json[ 'status' ] = 'failure';
        $json[ 'connected' ] = true;
        if ( !$this->redirectIfNotConnected( true ) ) {
            $json[ 'connected' ] = false;
            echo JSONConvertor::JSONToText( $json );
            die();
        }

        $this->loadModel( 'sondage' );
        $sondageModel = $this->getModel( 'sondage' );
        $sondage = $sondageModel->searchOne( array(
            'conditions' => array(
                'id' => htmlspecialchars( $id ),
                'user' => $this->getSession()->get( 'user' )->id
            )
        ) );
        if ( $sondage == null ) {
            if ( $sondage == null ) {
                $json[ 'message' ] = "Droits insuffisant pour cette opération";
            }
            echo JSONConvertor::JSONToText( $json );
            die();
        }

        $this->loadModel( 'question' );
        $questionModel = $this->getModel( 'question' );

        $questions = $questionModel->search( array(
            'fields' => array(
                'text', 'type', 'criteres', 'orderNum', 'token'
            ),
            'conditions' => array(
                'sondage' => $sondage->id
            ),
            'order' => array(
                'by' => 'orderNum',
                'dir' => 'asc'
            )
        ) );

        foreach($questions as $q) {
            $q->criteres = explode( ';', $q->criteres );
        }
        $json[ 'questions' ] = $questions;
        $json[ 'status' ] = 'success';
        echo JSONConvertor::JSONToText( $json );
        die();
    }

    public function save( $id, $slug ) {
        header('Content-type: application/json');
        $json = array();
        $json[ 'status' ] = 'failure';
        $json[ 'connected' ] = true;
        if ( !$this->redirectIfNotConnected( true ) ) {
            $json[ 'connected' ] = false;
            echo JSONConvertor::JSONToText( $json );
            die();
        }

        $data = $this->getData()->data;

        $this->loadModel( 'sondage' );
        $sondageModel = $this->getModel( 'sondage' );
        $sondage = $sondageModel->searchOne( array(
            'conditions' => array(
                'id' => htmlspecialchars( $id ),
                'user' => $this->getSession()->get( 'user' )->id
            )
        ) );
        if ( $data == null || $sondage == null ) {
            if ( $sondage == null ) {
                $json[ 'message' ] = "Droits insuffisant pour cette opération";
            }
            if ( $data == null ) {
                $json[ 'message' ] = "Aucune donnée n'a été transmise";
            }
            echo JSONConvertor::JSONToText( $json );
            die();
        }


        $this->loadModel( 'question' );
        $questionModel = $this->getModel( 'question' );
        $json[ 'tokens' ] = array();
        foreach ($data as $k => $question) {
            if ( $k > 0 ) {
                /*
                 * Vérification de l'intégrité des données.
                 * Si les questions n'appartiennent pas à l'utilisateur courant,
                 * une erreur est renvoyée.
                 * Si oui alors elles sont mises à jour.
                 */
                if ( !empty( $question[ 'token' ] ) ) {
                    //*
                    $q = $questionModel->searchOne( array(
                        'conditions' => array(
                            'token' => $question[ 'token' ],
                            'sondage' => $sondage->id
                        )
                    ) );
                    //*/
                    /*
                     * si q est null alors l'utilisateur est probablement pas
                     * le propriétaire ou bien la question n'existe pas.
                     *
                     * sinon on récupère l'id de la question pour la modifier
                     */
                    //*
                    if ( $q == null ) {
                        $json[ 'message' ] =
                            "Droits insuffisant pour cette opération ou "
                            . "ressource introuvable";
                        echo JSONConvertor::JSONToText( $json );
                        die();
                    }
                    $criteres = array();
                    foreach( $question[ 'criteres' ] as $c ) {
                        $criteres[] = htmlspecialchars( $c );
                    }
                    $questionModel->update(array(
                        'id' => $q->id,
                        'text' => htmlspecialchars( $question[ 'text' ] ),
                        'criteres' => implode( ';', $criteres ),
                        'orderNum' => htmlspecialchars( $question[ 'order' ] )
                    ));
                    //*/
                } else {
                    /*
                     * Le token est vide donc nous devons persister la question
                     */
                    $criteres = array();
                    foreach( $question[ 'criteres' ] as $c ) {
                        $criteres[] = htmlspecialchars( $c );
                    }
                    //*
                    $token = sha1( $question[ 'text' ] . uniqid() );
                    $d = array(
                        'text' => htmlspecialchars( $question[ 'text' ] ),
                        'type' => htmlspecialchars( $question[ 'type' ] ),
                        'criteres' => implode( ';', $criteres ),
                        'orderNum' => htmlspecialchars( $question[ 'order' ] ),
                        'sondage' => $sondage->id,
                        'token' => $token
                    );
                    $questionModel->create( $d );
                    $json[ 'tokens' ][] = array(
                        'order' => $question[ 'order' ],
                        'token' => $token
                    );
                    //*/
                }
            }
        }
        $json[ 'status' ] = 'success';
        echo JSONConvertor::JSONToText( $json );
        die();
    }
}