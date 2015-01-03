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

    public function activate( $id ) {
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
        $opened = htmlspecialchars( $data['opened'] );

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

        $sondageModel->update( array(
            'id'    => $sondage->id,
            'opened' => $opened == "true" ? 1 : 0
        ) );


        $json[ 'status' ] = 'success';
        echo JSONConvertor::JSONToText( $json );
        die();
    }

    public function remove( $id, $slug ) {
        $this->redirectIfNotConnected();
        $this->loadModel( 'sondage' );
        $sondageModel = $this->getModel( 'sondage' );
        $sondage = $sondageModel->searchOne( array(
            'conditions' => array(
                'id' => htmlspecialchars( $id ),
                'slug' => htmlspecialchars( $slug ),
                'user' => $this->getSession()->get( 'user' )->id
            )
        ) );

        if ( $sondage == null ) {
            $this->redirect( 'dashboard' );
        }

        $this->loadModel( 'question' );
        $questionModel = $this->getModel( 'question' );
        $questionModel->removeQuestionWithSurveyId( $sondage->id );

        $this->loadModel( 'answer' );
        $questionModel = $this->getModel( 'answer' );
        $questionModel->removeAnswersWithSurveyId( $sondage->id );

        $sondageModel->delete( 'id', $sondage->id );
        $this->redirect( 'dashboard' );
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
        $title = htmlspecialchars( $this->getData()->title );

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



        /*
         * On vérifie si le sondage peut être encore modifié.
         */
        $this->loadModel( 'answer' );
        $answerModel = $this->getModel( 'answer' );
        $answers = $answerModel->searchOne( array(
            'conditions' => array(
                'sondage' => $sondage->id
            )
        ) );

        if ( !empty( $answers ) ) {
            $json[ 'message' ] = "Le sondage ne peut pas être modifié car il"
                . " contient déjà des réponses.";
            echo JSONConvertor::JSONToText( $json );
            die();
        }

        /*
         * Si personne n'a encore répondu mais que le sondage est ouvert aux
         * réponses, alors il est interdit de modifier le sondage.
         */
        if ( $sondage->opened ) {
            $json[ 'message' ] = "Le sondage ne peut pas être modifié car il"
                . " est ouvert aux réponses.";
            echo JSONConvertor::JSONToText( $json );
            die();
        }

        $sondageModel->update( array(
            'id'    => $sondage->id,
            'title' => $title
        ) );

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

                    if ( $question[ 'isDeleted' ] == 'true' ) {
                        $questionModel->delete( 'id', $q->id );
                    } else {

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
                    }
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

    public function respondent($id, $slug) {
        $this->setLayout( 'default' );
        $this->loadMessageFormatter( 'respondent' );

        $this->loadModel( 'sondage' );
        $this->loadModel( 'question' );
        $sondageModel = $this->getModel( 'sondage' );

        $sondage = $sondageModel->searchOne( array(
            'conditions' => array(
                'id' => $id
            )
        ) );
        if ( empty( $sondage ) ) {
            $this->error404( 'Ce sondage est introuvable.' );
        }
        $session = self::getSession();
        if ( !$sondage->opened ) {
            $session->setBag(
                "Ce sondage n'est plus ouvert aux réponses", 'stats_error'
            );
        }

        $questions = array();
        if ( $sondage->opened ) {
            $questionModel = $this->getModel( 'question' );
            $questions = $questionModel->search( array(
                'conditions' => array(
                    'sondage' => $id
                ),
                'order' => array(
                    'by' => 'orderNum',
                    'dir' => 'asc'
                )
            ) );
        }

        $this->sendVariables( array(
            'sondageId' => $sondage->id,
            'sondageTitle' => $sondage->title,
            'sondageSlug' => $sondage->slug,
            'sondageOpened' => $sondage->opened,
            'questions' => $questions
        ) );
    }

    public function getSurvey($id, $slug) {
        $this->loadModel( 'sondage' );
        $this->loadModel( 'question' );

        $sondageModel = $this->getModel( 'sondage' );

        $sondage = $sondageModel->searchOne( array(
            'conditions' => array(
                'id' => $id
            )
        ) );
        if ( !$sondage->opened ) {
            $this->redirect( "survey/respondent/$id/$slug" );
            die();
        }
        if ( empty( $sondage ) ) {
            $this->error404( 'Ce sondage est introuvable.' );
        }
        $this->loadModel( 'answer' );
        $answersModel = $this->getModel( 'answer' );
        $data = $this->getData();
        $r = "";
        foreach ($data as $key => $value) {
            $r .= htmlspecialchars($value) . ";";
        }
        $r = substr($r, 0, strlen($r) - 1 );

        try {
            $answersModel->create( array(
                'value' => $r,
                'sondage' => $id
            ) );
        } catch (\PDOException $e) {
            Debug::debug($e);
        }
        $session = self::getSession();
        $session->setBag(
            'Votre réponse a bien été envoyée', 'home_disconnect'
        );
        $this->redirect( '' );
        die();
    }

    public function stats($id, $slug) {
        $this->redirectIfNotConnected();
        $this->setLayout( 'dashboard' );

        $this->loadModel( 'sondage' );
        $this->loadModel( 'question' );
        $sondageModel = $this->getModel( 'sondage' );

        $sondage = $sondageModel->searchOne( array(
            'conditions' => array(
                'id' => $id
            )
        ) );
        if ( empty( $sondage ) ) {
            $this->error404( 'Ce sondage est introuvable.' );
        }

        $questionModel = $this->getModel( 'question' );
        $questions = $questionModel->search( array(
            'conditions' => array(
                'sondage' => $id
            ),
            'order' => array(
                'by' => 'orderNum',
                'dir' => 'asc'
            )
        ) );

        $this->loadModel( 'answer' );
        $answersModel = $this->getModel( 'answer' );
        $answers = $answersModel->search( array(
            'conditions' => array(
                'sondage' => $id
            )
        ) );

        $questionsStats = array();
        foreach ($questions as $q ) {
            $criteres = explode(';', $q->criteres);
            $stats = array();
            foreach($criteres as $c) {
                $stats[$c] = 0;
            }
            $questionsStats[] = array(
                'question' => $q,
                'stats' => array(
                    'total' =>0,
                    'votes' => $stats
                )
            );
        }

        foreach ($answers as $a ) {
            $rep = explode(';', $a->value);
            foreach($rep as $k => $r) {
                switch ($questionsStats[ $k ][ 'question' ]->type) {
                    case 'choice':
                        $criteres = explode( ';',
                            $questionsStats[ $k ][ 'question' ]->criteres
                        );
                        $name = $criteres[ $r ];
                    break;
                    case 'numeric':
                        $name = $r;
                    break;
                    default:
                        // rien à faire
                }
                $questionsStats[ $k ]['stats']['votes'][$name]++;
                $questionsStats[ $k ]['stats']['total']++;
            }
        }


        $this->sendVariables( array(
            'sondageId' => $sondage->id,
            'sondageTitle' => $sondage->title,
            'sondageSlug' => $sondage->slug,
            'questionsStats' => $questionsStats
        ) );
    }
}