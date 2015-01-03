<?php
namespace octopus\app\models;

use octopus\app\Debug;
use octopus\core\Model;

class Question extends Model {
    public function removeQuestionWithSurveyId( $id ) {
        try {
            $this->delete( 'sondage', $id );

        } catch (\PDOException $e) {
            Debug::debug( $e );
        }

    }
}
