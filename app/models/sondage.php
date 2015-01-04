<?php
namespace octopus\app\models;
use octopus\app\Debug;
use octopus\core\Model;
use octopus\core\utils\JSONConvertor;

class Sondage extends Model {
    public function add( $userId, $title ) {

        $slug = strtolower( htmlspecialchars( $title ) );
        $slug = str_replace( ' ', '-', $slug );
        $slug = JSONConvertor::remove_accents( $slug );
        $data = array(
            'user'   => htmlspecialchars( $userId ),
            'title'  => htmlspecialchars( $title ),
            'date'   => time(),
            'opened' => 0,
            'slug' => $slug
        );

        try {
        $this->create( $data );

        } catch (\Exception $e) {
            Debug::debug($e);
        }
    }

    public function getSondages( $userId, $conditions = null, $limit = "" ) {
        $c = array(
            'user' => $userId
        );
        if ( !empty( $conditions ) ) {
            foreach($conditions as $k => $v) {
                $c[ $k ] = $v;
            }
        }
        $request = array(
            'conditions' => $c,
            'order' => array(
                'by' => 'date',
                'dir' => 'desc'
            )
        );
        if ( $limit != "" ) {
            $request[ 'limit' ] = $limit;
        }
        return $this->search( $request );
    }

    public function getSondagesCount( $userId, $conditions = null ) {
        $c = array(
            'user' => $userId
        );
        if ( !empty( $conditions ) ) {
            foreach($conditions as $k => $v) {
                $c[ $k ] = $v;
            }
        }
        return $this->count( $c );
    }
}