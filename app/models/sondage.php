<?php
namespace octopus\app\models;
use octopus\app\Debug;
use octopus\core\Model;
use octopus\core\utils\JSONConvertor;

/**
 * Class Sondage
 * @package octopus\app\models
 *
 * Cette classe constitue un model pour les sondages.
 */
class Sondage extends Model {
    /**
     * Crée un sondage à partir de son titre pour un utilisateur donné.
     * @param $userId
     *  id de l'utilisateur
     * @param $title
     *  titre du sondage
     */
    public function add( $userId, $title ) {
        $slug = self::createSlug( $title );
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

    /**
     * Retourne un tableau de sondages.
     *
     * Il est possible de récupérer tout les sondages d'un utilisateur. Mais
     * aussi de rafiner la sélection de ces sondages avec un tableau de
     * conditions. Et de paramétrer un nombre maximum de sondage à retourner et
     * d'indiquer à partir de quel sondage la sélection doit être faite.
     *
     * @param null $userId
     *  id de l'utilisateur. Par défaut, vaut null
     *  Si l'id n'est pas définit, les sondages seront sélectionnés tout
     *  utilisateur confondu.
     *
     * @param null $conditions
     *  tableau des conditions, similaire au tableau de condition de la méthode
     *  select de la classe \octopus\core\DataBaseManager
     * @param string $limit
     *  par défaut aucune plage n'est définit. La syntaxe SQL est adoptée ici
     *  pour définir cette plage de sélection.
     * @return mixed
     *  tableau des sondages sélectionnés
     */
    public function getSondages( $userId = null, $conditions = null, $limit = "" ) {
        $c = array();
        if ( $userId != null ) {
            $c = array(
                'user' => $userId
            );
        }
        if ( !empty( $conditions ) ) {
            foreach($conditions as $k => $v) {
                $c[ $k ] = $v;
            }
        }
        $request = array(
            'order' => array(
                'by' => 'date',
                'dir' => 'desc'
            )
        );
        if ( !empty( $c ) ) {
            $request[ 'conditions' ] = $c;
        }
        if ( $limit != "" ) {
            $request[ 'limit' ] = $limit;
        }
        return $this->search( $request );
    }

    /**
     * Retourne le nombre de sondages.
     *
     * Il est possible de récupérer le nombre de tout les sondages d'un
     * utilisateur. Mais aussi de rafiner la sélection de ces sondages avec un
     * tableau de conditions. Et de paramétrer un nombre maximum de sondage à
     * retourner et d'indiquer à partir de quel sondage la sélection doit être
     * faite.
     *
     * @param null $userId
     *  id de l'utilisateur. Par défaut, vaut null
     *  Si l'id n'est pas définit, les sondages seront sélectionnés tout
     *  utilisateur confondu.
     *
     * @param null $conditions
     *  tableau des conditions, similaire au tableau de condition de la méthode
     *  select de la classe \octopus\core\DataBaseManager
     * @param string $limit
     *  par défaut aucune plage n'est définit. La syntaxe SQL est adoptée ici
     *  pour définir cette plage de sélection.
     * @return mixed
     *  nombre de sondages
     */
    public function getSondagesCount( $userId = null, $conditions = null ) {
        $c = array();
        if ( $userId != null ) {
            $c = array(
                'user' => $userId
            );
        }
        if ( !empty( $conditions ) ) {
            foreach($conditions as $k => $v) {
                $c[ $k ] = $v;
            }
        }
        return $this->count( $c );
    }

    /**
     * Retourne un slug.
     *
     * Les caractères ' ', ',', ';', '\'', '"', '?', '!', '(', ')', '[', ']',
     * '{', '}', '@' sont transformés en '-'
     *
     * Les accents des lettres sont retirés.
     *
     * @param $string
     *  châine de caractère sur laquelle se baser pour créer un slug
     * @return mixed|string
     */
    public static function createSlug( $string ) {
        $slug = strtolower( htmlspecialchars( $string  ) );

        $chars = array(
            ' ', ',', ';', '\'', '"', '?', '!',
            '(', ')', '[', ']', '{', '}', '@'
        );
        foreach ($chars as $c) {
            $slug = str_replace( $c, '-', $slug );
        }

        $slug = JSONConvertor::remove_accents( $slug );
        return $slug;
    }
}