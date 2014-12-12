<?php
namespace octopus\core;
use octopus\app\Debug;

/**
 * Class Model
 * @package octopus\core
 *
 * Cette classe permet de gérer les données d'un model. Elle utilise une
 * instance de DataBaseManager afin de pouvoir récupérer le contenu en base de
 * données.
 */
class Model {
    const PRIMARY_KEY = 'id';
    private $dbm;

    public function __construct() {
        /* le nom de la table correspond au nom de la classe. En effet la classe
         * Model a pour but d'être dérivée. Les classes filles auront des noms
         * différents et correspondent aux noms des tables.
         */
        $name = strtolower( get_class( $this ) ) . 's';
        $path = explode( '\\', $name );
        $name = $path[ sizeof( $path ) - 1 ];
        $this->dbm =
            new DataBaseManager( 'default',
                $name,
                self::PRIMARY_KEY);
    }

    /**
     * Supprime une entrée de la table courante en fonction du nom du champs
     * ainsi que de sa valeur
     * @param $key
     *  nom du champs
     * @param $value
     *  valeur dont le champs doit être égal pour effectuer la suppression
     */
    public function delete( $key, $value ) {
        $this->dbm->delete( array(
            $key => $value
        ) );
    }

    /**
     * Persiste les données dans la table courante.
     * @param array $data
     */
    public function create( $data ) {
        /* on souhaite créer une entrée. Donc si l'objet possède une clé
         * d'indexation il est nécessaire de ne pas la considérer.
         */
        if ( isset( $data[ 'id' ] ) ) {
            unset( $data[ 'id' ] );
        }
        $this->dbm->save( $data );
    }

    /**
     * Met à jour les données dans la table courante.
     * @param array|stdClass $data
     *  l'incice id doit être nécessairement égal à l'indice de l'entrée dans la
     * table courante à mettre à jour.
     * @throws \Exception
     */
    public function update( $data ) {
        if ( is_array( $data ) ) {
            if ( !isset( $data[ 'id' ] ) ) {
                throw new \Exception( "Aucune clé primaire trouvée." );
            }
            $d = new \stdClass();
            foreach( $data as $k => $v ) {
                $d->$k = $v;
            }
            $data = $d;
        }
        if ( gettype( $data ) == 'stdClass' && !isset( $data->id ) ) {
            throw new \Exception( "Aucune clé primaire trouvée." );
        }
        $this->dbm->save( $data );
    }

    /**
     * Retourne un tableau contenant les résultats de la recherche.
     * @param array $request
     *  $request est un tableau regroupant différents critères pour construire
     * la requête SQL à exécuter.
     *    Les champs possible pour ce tableau sont :
     *      @field string fields
     *          spécifie un champs de la table à selectionner.
     *
     *      @field array fields
     *          spécifie les champs de la table à selectionner.
     *
     *      @field array conditions
     *          spécifie les conditions sur la requête à effectuer.
     *          Il s'agit d'une suite de AND.
     *
     *      @field string conditions
     *          spécifie les conditions sur la requête à effectuer.
     *          Il appartient à l'utilisateur de rédiger les conditions en SQL.
     *
     *      @field string order
     *          spécifie l'ordre de tri. ASC ou DESC
     *
     *      @field string limit
     *          définit un nombre maximal d'élément dans le résultat de la
     *          requête
     *
     * @return mixed
     */
    public function search( $request ) {
        return $this->dbm->select( $request );
    }

    /**
     * Retourne la première réponse trouvée de la recherche effectuée.
     * @param array $request
     *  $request est un tableau regroupant différents critères pour construire
     *  la requête SQL à exécuter.
     *    Les champs possible pour ce tableau sont :
     *      @field string fields
     *          spécifie un champs de la table à selectionner.
     *
     *      @field array fields
     *          spécifie les champs de la table à selectionner.
     *
     *      @field array conditions
     *          spécifie les conditions sur la requête à effectuer.
     *          Il s'agit d'une suite de AND.
     *
     *      @field string conditions
     *          spécifie les conditions sur la requête à effectuer.
     *          Il appartient à l'utilisateur de rédiger les conditions en SQL.
     *
     *      @field string order
     *          spécifie l'ordre de tri. ASC ou DESC
     *
     *      @field string limit
     *          définit un nombre maximal d'élément dans le résultat de la
     *          requête
     *
     * @return mixed
     */
    public function searchOne( $request ) {
        return $this->dbm->selectFirst( $request );
    }

    /**
     * Retourne le nombre d'éléments dans la réponse de select
     * avec conditions.
     *
     * @param $conditions
     *      @field array conditions
     *          spécifie les conditions sur la requête à effectuer.
     *          Il s'agit d'une suite de AND.
     *
     *      @field string conditions
     *          spécifie les conditions sur la requête à effectuer.
     *          Il appartient à l'utilisateur de rédiger les conditions en SQL.
     * @return mixed
     */
    public function count( $conditions ) {
        return $this->dbm->count( $conditions );
    }
}
