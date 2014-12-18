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

    public function getSondages( $userId ) {
        return $this->search( array(
            'conditions' => array(
                'user' => $userId
            ),
            'order' => array(
                'by' => 'date',
                'dir' => 'desc'
            )
        ) );
    }
}