<?php
namespace octopus\app\models;

use octopus\app\Debug;
use octopus\core\Config;
use octopus\core\Model;

class User extends Model {
    public function add( $login, $pass ) {
        $salt = sha1( Config::getAppName() );
        $data = array(
            'login' => htmlspecialchars( $login ),
            'pass'  => sha1( $salt . '_' . sha1( htmlspecialchars( $pass ) ) )
        );
        try {
            $this->create( $data );
            return 'created';
        } catch (\PDOException $e) {
            if ( $e->getCode() == 23000 ) {
                return 'duplicate';
            }
        }
    }

    public function getUser( $login, $pass ) {
        $salt = sha1( Config::getAppName() );
        $data = array(
            'login' => htmlspecialchars( $login ),
            'pass'  => sha1( $salt . '_' . sha1( htmlspecialchars( $pass ) ) )
        );

        $user = $this->searchOne( array(
            'conditions' => array(
                'login' => $data[ 'login' ],
                'pass'  => $data[ 'pass' ]
            )
        ) );

        return $user;
    }
}
