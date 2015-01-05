<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;
use octopus\core\Router;

/**
 * Class HomeController
 * @package octopus\app\controllers
 * Cette classe est le controleur pour la page principale.
 */
class HomeController extends Controller {

    /**
     * Controleur de la page principale.
     * La principale possède un système de pagination afin d'afficher les
     * sondages par groupe de 18.
     *
     * Cette action redirige vers la page d'administration si l'utilisateur est
     * l'administrateur. Si non, vers la page de d'administration des sondages
     * d'un utilisateurs connecté.
     * @param int $page
     *  page est le numéro de la page à charger pour la vue
     *
     * Les variables renvoyées à la vue sont les suivantes:
     * sondages: tableau contenant les sondages de l'utilisateur du groupe $page
     * page: numéro de la page courante
     * countPages: nombre de pages
     * previousLink: url vers la page précédente, null si page < 2
     * nextLink: url vers la page suivante, null si page > countPage
     * baseUrlPagination: base de l'url de pagination
     */
    public function index( $page = 1 ) {
        $this->loadModel( 'user' );
        if ( User::isConnected() ) {
            if ( User::isAdmin() ) {
                $this->redirect( 'admin/index' );
            } else {
                $this->redirect( 'dashboard' );
            }
        }
        /*
         * utilisé pour bag
         */
        $this->loadMessageFormatter( 'home' );

        $this->loadModel( 'sondage' );

        $sondageModel = $this->getModel( 'sondage' );

        $item_per_page = 18;
        $offset = ( $page - 1 ) * $item_per_page;

        $sondages = $sondageModel->getSondages(
            null, array( 'opened' => 1 ), "$offset,$item_per_page"
        );

        $rowcount = $sondageModel->getSondagesCount(
            null, array( 'opened' => 1 )
        );
        $count = (int) ceil( $rowcount / $item_per_page );

        $baseUrlPagination = Router::generate( 'page' );

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
}
