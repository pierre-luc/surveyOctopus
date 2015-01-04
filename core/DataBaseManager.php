<?php
namespace octopus\core;
use octopus\app\Debug;
use octopus\core\Config;

/**
 * Class DataBaseManager
 * @package octopus\core
 *
 * Cette classe permet de gérer une base de données. Les requêtes sont
 * effectuées à l'aide de PDO. Afin de sécuriser la faille d'injection SQL, les
 * requêtes sont préparées.
 */
class DataBaseManager {
    /*
     * Mémorise les connexions réalisées à la base de données.
     */
    private static $connections = array();

    /*
     * L'utilisateur peut utiliser plusieurs serveurs de base de données et
     * choisir une configuration pour instancier le manager. Cette configuration
     * est stockée dans cet attribut.
     */
    private $conf;

    /*
     * Mémorise la table sur laquelle les opérations sont effectuées.
     */
    private $table;

    /*
     * Mémorise l'instance PDO pour ne pas l'instancier plusieurs fois.
     */
    private $db;

    /*
     * Mémorise la clé primaire de la table courante.
     */
    private $primaryKey;

    /*
     * Mémorise le dernier id lors des ajouts en base de données.
     */
    private $id;

    /**
     * DataBaseManager
     * @param $conf
     *  configuration d'accès à la base de données
     * @param $table
     *  spécifie sur quelle table l'utilisateur souhaite travailler.
     * @param $pkey
     *  spécifie la clé primaire de la table représentée par le manager.
     */
    public function __construct( $conf = 'default', $table, $pkey = 'id') {
        $this->conf = $conf;
        $this->table = $table;
        $this->primaryKey = $pkey;

        // connexion à la base de donnée
        $conf = Config::$databases[ $this->conf ];
        if ( isset( DataBaseManager::$connections[ $this->conf ] ) ) {
            $this->db = DataBaseManager::$connections[ $this->conf ];
            // puisque la connexion existe déjà on ne continue pas plus loin.
            return true;
        }
        try {
            $pdo = new \PDO(
                "mysql:host={$conf['host']};dbname={$conf['database']};",
                $conf['login'],
                $conf['pass'],
                array( \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' )
            );

            $pdo->setAttribute( \PDO::ATTR_ERRMODE, Debug::$pdoDebugMode );

            DataBaseManager::$connections[ $this->conf ] = $pdo;
            $this->db = $pdo;

        } catch( \PDOException $e) {
            if ( Debug::$debug >= 1 ) {
                die( $e->getMessage() );
            } else {
                throw $e;
            }
        }
        return true; // par homogénéité
    }

    /**
     * Retourne un tableau contenant la réponse de la requête select.
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
     *          définit un nombre maximal d'élément dans le résultat
     *          de la requête
     *
     * @return mixed
     */
    public function select( $request = array() ){
        $sql = "SELECT ";

        if ( isset( $request['select'] ) ) {
            $sql .= $request['select'] . ' ';
        }

        if ( isset( $request['fields'] ) ) {
            if ( is_array( $request['fields'] ) ) {
                $sql .= implode( ', ', $request['fields'] );
            } else {
                $sql .= $request['fields'];
            }
        } else {
            $sql .= " *";
        }

        // selection de la table
        $sql .= " FROM `" . $this->table . "` as `T" . $this->table . "` ";

        // Construction de la condition WHERE
        if ( isset( $request['conditions'] ) ) {
            $sql .= "WHERE ";
            // si la conditions est une chaîne de caractères
            if ( !is_array( $request['conditions'] ) ) {
                $sql .= $request['conditions'];
            } else { // sinon on traite les informations du tableau
                $conditions = array();
                foreach ( $request['conditions'] as $key => $value) {
                    if ( !is_numeric( $value ) && !is_array( $value ) ) {
                        $value = "'" . $value . "'";
                    }
                    $op = '=';
                    $val = $value;
                    if ( is_array( $value ) ) {
                        $op = $value[ 0 ];
                        $val = $value[ 1 ];
                    }
                    $conditions[] = "`" . $key . "`" . "$op" . $val;
                }
                $sql .= implode( ' AND ', $conditions );
            }
        }

        // Ordre de tri du résultat de la requête
        if ( isset( $request['order'] ) ) {
            if ( !is_array( $request['order'])) {
                $sql .= " order by `{$this->primaryKey}` {$request['order']}";
            } else {
                $by = $request[ 'order' ][ 'by' ];
                $dir = $request[ 'order' ][ 'dir' ];
                $sql .= " order by `$by` $dir";
            }
        }

        // limite des résultats, possibilité d'insérer un offset.
        if ( isset( $request['limit'] ) ) {
            $sql .= ' LIMIT ' . $request['limit'] ;
        }
        try {
            $pre = $this->db->prepare( $sql );
            $pre->execute();
        } catch (\PDOException $e) {
            Debug::debug($e);
        }
        return $pre->fetchAll( \PDO::FETCH_OBJ );
    }

    /**
     * Retourne la première réponse de la requête select.
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
     *          définit un nombre maximal d'élément dans le résultat
     *          de la requête
     *
     * @return mixed
     */
    public function selectFirst( $request ) {
        return current( $this->select( $request ) );
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
        $res = $this->selectFirst( array(
            'fields' => 'COUNT(' . $this->primaryKey . ') as `count`',
            'conditions' => $conditions
        ));
        return $res->count;
    }

    /**
     * Supprime un élément de la table courante.
     * @param $conditions
     *      @field array conditions
     *          spécifie les conditions sur la requête à effectuer.
     *          Il s'agit d'une suite de AND.
     *
     *      @field string conditions
     *          spécifie les conditions sur la requête à effectuer.
     *          Il appartient à l'utilisateur de rédiger les conditions en SQL.
     */
    public function delete( $conditions ) {
        // Construction de la condition WHERE
        if ( isset( $conditions ) ) {
            // si la conditions est une chaîne de caractères
            if ( !is_array( $conditions ) ) {
                $sql = $conditions;
            } else { // sinon on traite les informations du tableau
                $sql = array();
                foreach ( $conditions as $key => $value) {
                    if ( !is_numeric( $value ) ) {
                        $value = '"' . mysql_real_escape_string( $value ) . '"';
                    }
                    $sql[] = "`" . $key . "`" . "=" . $value;
                }
                $sql = implode( ' AND ', $sql );
            }
        }
        //todo requête préparée pour la suppression
        $sql = "DELETE FROM {$this->table} WHERE $sql";
        $this->db->query( $sql );
    }

    /**
     * Enregistre des données dans la table courante.
     * @param Object $data
     *  Si l'objet passé en paramètre possède un attribut
     *  identique à la clé primaire de la table alors les données
     *  seront modifiées dans la table.
     *   Sinon elles seront ajoutées.
     * @return bool
     */
    public function save( $data ) {
        // les objets sont des instances de stdClass
        $key = $this->primaryKey;
        $fields = array();
        $d = array();

        // initialisation de la requête préparée
        foreach ( $data as $k => $v) {
            if ( $k != $this->primaryKey ) {
                $fields[] = "$k=:$k";
                $d[":$k"] = $v;
            } elseif ( !empty( $v) ) {
                $d[":$k"] = $v;
            }
        }

        // construction de la chaîne des champs pour la requête sql.
        $fields = implode( ',' , $fields );

        if ( isset( $data->$key ) && !empty( $data->$key ) ) {
            $sql  = "UPDATE {$this->table} SET $fields WHERE $key=:$key";
            $this->id = $data->$key;
            $action = "update";
        } else {
            $sql  = "INSERT INTO {$this->table} SET $fields";
            $action = "insert";
        }

        // requête préparée
        try {
            $pre = $this->db->prepare( $sql );
            $pre->execute( $d );
        } catch (\PDOException $e) {
            //Debug::debug($e);
            throw $e;
        }

        // mémorisation de l'id du dernier élément inséré.
        if ( $action === "insert" ) {
            $this->id = $this->db->lastInsertId();
        }
        return true;
    }

    /**
     * Exécute une requête sql.
     * Retourne true si la requête s'est bien passée, false sinon.
     * @param $sql
     * @param string $dbconf
     */
    public static function execute( $sql, $dbconf = 'default' ) {
        // todo Refaire la gestion de la base donnée avec cette classe
        // en effet l'approche qui suit n'est pas du tout propre.

        // provoque plusieurs connexion et instanciation via PDO
        try {
            $dnm = new DataBaseManager( $dbconf, null );
            $pre = $dnm->db;
            $pre->exec( $sql );
        } catch ( \PDOException $e ) {
            throw $e;
        }
    }
}