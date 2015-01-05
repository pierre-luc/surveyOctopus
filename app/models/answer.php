<?php
namespace octopus\app\models;

use octopus\core\Model;

/**
 * Class Answer
 * @package octopus\app\models
 *
 * Cette classe modélise les réponses aux questions des sondages.
 */
class Answer extends Model {
    /**
     * Supprime toutes les réponses d'un sondage donné.
     * @param $id
     *  id du sondage
     */
    public function removeAnswersWithSurveyId( $id ) {
        try {
            $this->delete( 'sondage', $id );
        } catch (\PDOException $e) {
            Debug::debug( $e );
        }
    }
}
