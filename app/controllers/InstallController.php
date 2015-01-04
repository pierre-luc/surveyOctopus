<?php
namespace octopus\app\controllers;
use octopus\app\Debug;
use octopus\core\Config;
use octopus\core\Controller;
use octopus\core\DataBaseManager;
use octopus\core\utils\JSONConvertor;

class InstallController extends Controller {
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

    public function databaseInstallation() {
        $this->setLayout( 'installer' );
        if ( Config::checkIfAlreadyInstalled() ) {
            Controller::redirect( '' );
        }
    }

    public function databaseInstallationProcess() {
        if ( Config::checkIfAlreadyInstalled() ) {
            Controller::redirect( '' );
        }
        $dbname = Config::$databases[ 'default' ][ 'database' ];
        $json = array();
        $json[ 'status' ] = 'success';
        $file = APP . DS . $dbname . ".sql";
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
                rename( $file, APP . DS . $dbname . '_installed.sql' );
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
