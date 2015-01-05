<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\core\Config;
use octopus\core\Controller;
use octopus\core\DataBaseManager;
use octopus\core\utils\JSONConvertor;

/**
 * Class InstallController
 * @package octopus\app\controllers
 *
 * Cette classe est le controleur du processus d'installation.
 */
class InstallController extends Controller {

    /**
     * Controleur du point d'entrée de l'installateur.
     *
     * Tout d'abord cette action prépare la vue en choisissant le layout
     * installer. Si l'installation a déjà été réalisée alors l'utilisateur est
     * redirigé vers la page principale.
     *
     * Sinon, l'installation peut commencer. Pour cela les détails de connexion
     * sont chargés s'ils existent afin de pouvoir les afficher dans la vue.
     */
    public function index() {
        $this->setLayout( 'installer' );
        if ( Config::checkIfAlreadyInstalled() ) {
            Controller::redirect( '' );
        }
        $conf = Config::$databases[ 'default' ];
        if ( isset( $conf ) ) {
            $this->sendVariables( array(
                'hostname' => $conf[ 'host' ],
                'login'    => $conf[ 'login' ],
                'dbname'   => $conf[ 'database' ]
            ) );
        }
        // correction bug de connexion automatique
        $this->getSession()->destroy();
    }

    /**
     * Cette action vérifie tout d'abord si l'installation a été effectuée. Dans
     * un tel cas l'utilisateur est redirigé vers la page principale. Sinon, les
     * données du formulaire son récupérées afin de procéder à la sauvegarde du
     * fichier de configuration parameters.json dans le répertoire app.
     *
     * Une fois le fichier de configuration créé, l'utilisateur est redirigé
     * vers l'étape suivante de l'installation. C'est à dire, la création du
     * schéma de la base de données sur le serveur de base de données.
     */
    public function database() {
        if ( Config::checkIfAlreadyInstalled() ) {
            Controller::redirect( '' );
        }
        $data = $this->getData();
        if ( APP . DS . 'parameters.json' ) {
            unlink( APP . DS . 'parameters.json' );
        }
        // préparation du contenu pour le fichier parameters.json
        $r  = "{\n";
        $r .= "    \"databases\": {\n";
        $r .= "        \"default\": {\n";
        $r .= "            \"database\": \"{$data->dbname}\",\n";
        $r .= "            \"host\": \"{$data->hostname}\",\n";
        $r .= "            \"login\": \"{$data->login}\",\n";
        $r .= "            \"pass\": \"{$data->pass}\"\n";
        $r .= "        }\n";
        $r .= "    }\n";
        $r .= "}\n";

        // Création du fichier parameters.json
        $f = fopen( APP . DS . 'parameters.json', 'a' );
        fwrite( $f, $r );
        fclose( $f );

        Controller::redirect( 'install/databaseInstallation' );
    }

    /**
     * Controleur de la page databaseInstallation du processus d'intallation.
     * Mis à part le fait que la vérification d'une installation antérieure
     * existe déjà, dans quel cas l'utilisateur est redirigé vers la page
     * principale, ce contrôleur définit le layout de la vue et rien de plus.
     */
    public function databaseInstallation() {
        $this->setLayout( 'installer' );
        if ( Config::checkIfAlreadyInstalled() ) {
            Controller::redirect( '' );
        }
    }

    /**
     * Cette action est le processus de création du schéma de la base de données
     * sur le serveur de base de données. Elle consitue une requête AJAX.
     *
     * Si l'installation a déjà été effectuée, l'utilisateur est redirigé vers
     * la page principale.
     *
     * Le fichier database.sql du répertoire app contient les directives de
     * création du schéma de de la base de données. Ce fichier est chargé et
     * exécuté grâce à la classe \octopus\core\DataBaseManager
     *
     * L'issue de cette action est retournée au format JSON dans le but d'être
     * traitée par un script javascript utilisant la technologie AJAX.
     */
    public function databaseInstallationProcess() {
        if ( Config::checkIfAlreadyInstalled() ) {
            Controller::redirect( '' );
        }
        $dbname = Config::$databases[ 'default' ][ 'database' ];
        $json = array();
        $json[ 'status' ] = 'success';
        $file = APP . DS . "database.sql";
        if ( file_exists( $file ) ) {
            $f = fopen( $file, "r");
            $sql = "";
            while (($data = fgets($f)) != NULL) {
                if (substr($data, 0, 2) != "--") {
                    $data = str_replace(chr(10), chr(13), $data);
                    $data = str_replace(chr(13)," ", $data);
                    $sql = $sql.$data;
                }
            }
            fclose($f);
            $tSql = explode(";", $sql);

            unset($tSql[sizeof($tSql) - 1]);

            foreach ($tSql as $e) {
                try {
                    DataBaseManager::execute($e.';');
                } catch (\PDOException $e) {
                    $json[ 'status' ] = 'failure';
                    if ( Debug::$debug > 0 ) {
                        $json['msg'] = $e;
                    }
                }

            }
            if ( $json[ 'status' ] == 'success' ) {
                rename( $file, APP . DS . 'database_installed.sql' );
            }
        } else {
            $json['msg'] = 'impossible de lire le fichier sql';
            $json[ 'status' ] = 'failure';
        }
        header('Content-type: application/json');
        echo JSONConvertor::JSONToText( $json );
        die();
    }
}
