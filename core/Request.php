<?php
namespace octopus\core;

/**
 * Class Request
 * @package octopus\core
 *
 * Cette classe permet de modeliser une reqête. La convention suivante est
 * adoptée. Une reqête est constituée d'une url accessoirement de données
 * transmises par envoie de formulaire en GET ou en POST.
 *
 * Le traitement des autres paramètres différents de l'url et des données
 * transmise par l'envoie de formulaire est réalisé par la classe Router afin de
 * bien séparer les tâches métier de chacune des deux classes.
 *
 * NOTE: Les utilisateurs de Apache 2 doivent activer l'option PathInfo dans le
 * fichier httpd.conf.
 *
 * AcceptPathInfo = On
 */
class Request {
    private $url;
    private $data = false;
    private $controller;
    private $action;
    private $params = array();

    function __construct() {
        /* on récupère le chemin de la requête dans le système de fichier, pas
         * le documentRoot, jusqu'au script courant. Ceci, une fois que le
         * serveur a fait la traduction chemin virtuel vers chemin réel.
         */
        $this->url =
            isset( $_SERVER[ 'PATH_INFO' ] ) ? $_SERVER[ 'PATH_INFO' ] : '/';
        $this->url = urldecode($this->url);

        // initilisation des données transmises en POST
        if ( !empty( $_POST ) ) {
            $this->data = new \stdClass();
            foreach ($_POST as $key => $value) {
                $this->data->$key = $value;
            }
        }
    }

    /**
     * Retourne l'url de la requête.
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Retourne les données transmises par la requête, false si aucune donnée
     * n'est trouvée.
     * @return bool|\stdClass
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Retourne l'action associée à la requête.
     * @return mixed
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Retourne le nom du controleur associé à la requête.
     * @return mixed
     */
    public function getControllerName() {
        return $this->controller;
    }

    /**
     * Retourne les paramètres fournis dans la requête.
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Enregistre l'action associée à la requête
     * @param $action
     */
    public function setAction($action) {
        $this->action = $action;
    }

    /**
     * Enregistre le nom du controleur associé à la requête.
     * @param $name
     */
    public function setControllerName($name) {
        $this->controller = $name;
    }

    /**
     * Enregistre les paramètres de la requête.
     * @param array $params
     */
    public function setParams( $params = array() ) {
        $this->params = $params;
    }
}
