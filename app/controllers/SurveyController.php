<?php
namespace octopus\app\controllers;

use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;
use octopus\core\Router;
use octopus\core\utils\JSONConvertor;

/**
 * Class SurveyController
 * @package octopus\app\controllers
 *
 * Cette classe est le controleur des sondages.
 * Elle permet de gérer la création, la modification, et la suppression de
 * sondage.
 */
class SurveyController extends Controller {
    /**
     * Controleur de la page dashboard.
     * Cette page dispose d'un système de pagination.
     *
     * Si l'utilisateur n'est pas connecté alors il est redirigé vers la page
     * principale.
     *
     * L'action de controleur définit le layout dashboard pour sa vue. Et stocke
     * les sondages de l'utilisateur. Ces sondages ne sont pas tous récupérés.
     * Seulement des groupes de 10 sondages sont récupérés. Le numéro de la page
     * permet de définir quel groupe sera chargé puis renvoyé à la vue.
     *
     * @param int $page
     *  numéro de la page à afficher
     *
     * Les variables renvoyées à la vue sont les suivantes:
     * sondages: tableau contenant les sondages de l'utilisateur du groupe $page
     * page: numéro de la page courante
     * countPages: nombre de pages
     * previousLink: url vers la page précédente, null si page < 2
     * nextLink: url vers la page suivante, null si page > countPage
     * baseUrlPagination: base de l'url de pagination
     */
    public function dashboard($page = 1) {
        $this->redirectIfNotConnected();
        $page = intval( htmlspecialchars( $page ) );
        $this->setLayout( 'dashboard' );
        $user = $this->getSession()->get( 'user' );
        $this->loadModel( 'sondage' );
        $sondageModel = $this->getModel( 'sondage' );
        $item_per_page = 10;
        $offset = ( $page - 1 ) * $item_per_page;
        $sondages = $sondageModel->getSondages(
            $user->id, null, "$offset,$item_per_page"
        );
        $rowcount = $sondageModel->getSondagesCount( $user->id );
        $count = (int) ceil( $rowcount / $item_per_page );

        $baseUrlPagination = Router::generate( 'dashboard/page' );

        $previousLink = null;
        if ( $page > 1 ) {
            $next = $page - 1;
            $previousLink = "$baseUrlPagination/$next";
        }
        $nextLink = null;
        if ( $page < $count ) {
            $prev = $page + 1;
            $nextLink = "$baseUrlPagination/$prev";
        }
        $this->sendVariables( array(
            'sondages' => $sondages,
            'page' => $page,
            'countPages' => $count,
            'previousLink' => $previousLink,
            'nextLink' => $nextLink,
            'baseUrlPagination' => $baseUrlPagination
        ) );
    }

    /**
     * Controleur de la vue add.
     * Définit le layout et vérifie si l'utilisateur à le droit d'accéder au
     * contenu. Si non, il est redirigé vers la page principale.
     */
    public function add() {
        $this->setLayout( 'dashboard' );
        $this->redirectIfNotConnected();
    }

    /**
     * Redirige l'utilisateur vers la page principale s'il n'est pas connecté.
     * Utilisant le protocole HTTP avec la méthode redirect, si cela est permis.
     *
     * @param boolean $onlyboolean
     *  Indique si l'on souhaite seulement récupérer l'état de connexion sans
     * rediriger.
     * @return boolean
     *  booléen indiquant si l'utilisateur est connecté ou non.
     */
    private function redirectIfNotConnected( $onlyboolean = false ) {
        $this->loadModel( 'user' );
        if ( !User::isConnected() ) {
            if ( $onlyboolean ) {
                return false;
            } else {
                $this->redirect( '' );
            }
        } else if( User::isAdmin() ) {
            $this->redirect( 'admin/index' );
            die();
        }
        return true;
    }

    /**
     * Cette action permet de créer un sondage.
     * Si l'utilisateur n'est pas connecté, il est redirigé vers la page
     * principale.
     *
     * Les données du formulaire de création de sondage sont récupérées.
     * Le sondage est ensuite créé puis l'utilisateur est redirigé vers la page
     * d'édition de ce sondage.
     */
    public function create() {
        $this->redirectIfNotConnected();
        $data = $this->getData();
        $user = $this->getSession()->get( 'user' );
        $this->loadModel( 'sondage' );
        $sondageModel = $this->getModel( 'sondage' );
        $title = htmlspecialchars( $data->title );
        $sondageModel->add( $user->id, $title );
        $sondage = $sondageModel->searchOne( array(
            'conditions' => array(
                'user' => $user->id,
                'title' => $title
            )
        ) );
        $this->redirect( "survey/manage/{$sondage->id}/{$sondage->slug}" );
    }

    /**
     * Controleur de la page manage.
     *
     * Si l'utilisateur n'est pas connecté alors il est redirigé vers la page
     * principale.
     *
     * Définit le layout dahsboard pour sa vue.
     *
     * La liste des sondages de l'utilisateur connecté est stockée dans la
     * variable sondages et accessible depuis la vue.
     *
     * @param $id
     *  id du sondage
     * @param $slug
     *  slug du sondage
     *
     */
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

    /**
     * Cette action permet de retourner la liste des questions d'un sondage et
     * constitue une requête AJAX.
     *
     * Les données renvoyées sont écrite au format JSON.
     * Les erreurs qui peuvent être déclanchées au cours de cette action sont
     * transmises dans la réponse de la requête sous forme d'un message clair.
     *
     * Une erreur est déclanchée si:
     *  - l'utilisateur n'est pas connecté
     *  - l'utilisateur n'a pas les droits sur le sondage
     *
     * @param $id
     *  id du sondage
     *
     * @param $slug
     *  slug du sondage
     */
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

    /**
     * Cette action permet d'ouvrir ou de fermer un sondage aux réponses et
     * constitue une requête AJAX.
     *
     * Les données renvoyées sont écrites au format JSON.
     * @param $id
     *  id du sondage
     */
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

    /**
     * Cette action permet la suppression d'un sondage.
     *
     * Si l'utilisateur n'est pas connecté, il est redirigé vers la page de
     * gestion des sondages.
     *
     * La suppression d'un sondage entraine la suppression des questions et
     * réponses associées.
     *
     * Une fois la suppression effectuée l'utilisateur est redirigé vers la page
     * de gestion des sondages.
     *
     * @param $id
     *  id du sondage
     * @param $slug
     *  slug du sondage
     */
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

    /**
     * Cette action permet de sauvegarder un sondage et constitue une requête
     * AJAX.
     *
     * Plusieurs vérifications sont effectuées avant de procéder à la sauvegarde
     * su sondage.
     *
     * Une erreur est renvoyée à l'utilisateur si:
     * - il n'est pas connecté
     * - il n'a pas les droits sur le sondage
     * - si aucune donnée n'a été transmise
     * - si des personnes ont déjà répondus aux question du sondage
     * - si le sondage est ouvert aux réponses
     *
     * Une fois les vérifications effectuées. Les données transmises sont
     * utilisées afin de les sauvegarder.
     *
     * La modification du titre entraine la  modification du slug.
     *
     * Les questions sont identifiées par leurs token.
     *
     * Les token des nouvelles question sont envoyés au client.
     *
     * @param $id
     *  id du sondage
     * @param $slug
     *  slug du sondage
     */
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

       $slug = $sondageModel::createSlug( $title );

        $sondageModel->update( array(
            'id'    => $sondage->id,
            'title' => $title,
            'slug' => $slug
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

    /**
     * Controleur qui gère les réponses aux sondages.
     *
     * @param $id
     * @param $slug
     */
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

    /**
     * Action qui permet de récupérer les réponses des sondages des utilisateurs
     * connectés ou non.
     *
     * Si le sondage est introuvable, une erreur 404 est renvoyée.
     *
     * Si le sondage n'est pas ouvert aux réponses, une erreur est renvoyée.
     *
     * Un message est envoyé à la vue à moyen de bag de \octopus\core\Session
     * pour informer l'utilisateur que sa réponse à bien été prise en compte.
     *
     * @param $id
     *  id du sondage
     * @param $slug
     *  slug du sondage
     */
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

    /**
     * Controleur de la page stats.
     * Cette action permet de préparer les données qui seront envoyées à la vue
     * afin d'afficher les statistiques de réponses du sondage.
     *
     * Si l'utilisateur n'est pas connecté alors il est redirigé vers la page
     * principale.
     *
     * Si le sondage est introuvable, une erreur 404 est renvoyée.
     *
     * @param $id
     *  id du sondage
     * @param $slug
     *  slug du sondage
     *
     * Les variables suivantes sont envoyées à la vue:
     * sondageId: id du sondage
     * sondageTitle: titre du sondage
     * sondageSlug: slug du sondage
     * questionsStats: tableau contenant la liste des questions ainsi que le
     *                 nombre de réponses total par question et avec le nombre
     *                 de réponses pour chacun des choix de réponses du sondage
     *                 par question.
     */
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