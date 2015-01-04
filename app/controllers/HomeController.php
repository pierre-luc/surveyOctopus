<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\app\models\User;
use octopus\core\Controller;
use octopus\core\Router;

class HomeController extends Controller {
    public function index( $page = 1 ) {
        $this->loadModel( 'user' );
        if ( User::isConnected() ) {
            if ( User::isAdmin() ) {
                $this->redirect( 'admin/index' );
            } else {
                $this->redirect( 'dashboard' );
            }
        }
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
