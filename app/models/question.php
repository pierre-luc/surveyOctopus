<?php
namespace octopus\app\models;

use octopus\app\Debug;
use octopus\core\Model;

/**
 * Class Question
 * @package octopus\app\models
 *
 * Cette classe modélise les questions d'un sondage.
 */
class Question extends Model {
    /**
     * Supprime toutes les questions d'un sondage donné.
     * @param $id
     *  id du sondage
     */
    public function removeQuestionWithSurveyId( $id ) {
        try {
            $this->delete( 'sondage', $id );

        } catch (\PDOException $e) {
            Debug::debug( $e );
        }

    }
}
