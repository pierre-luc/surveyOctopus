<?php
namespace octopus\app\models;

use octopus\core\Model;

class Answer extends Model {
    public function removeAnswersWithSurveyId( $id ) {
        try {
            $this->delete( 'sondage', $id );
        } catch (\PDOException $e) {
            Debug::debug( $e );
        }
    }
}
