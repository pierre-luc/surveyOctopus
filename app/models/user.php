<?php
namespace octopus\app\models;

use octopus\app\Debug;
use octopus\core\Config;
use octopus\core\Model;

class User extends Model {
    public function add( $login, $pass ) {
        $salt = Config::getAppName() . '_' . uniqid();
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
}
