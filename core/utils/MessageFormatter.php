<?php
namespace octopus\core\utils;
/**
 * Class MessageFormatter
 * @package octopus\core
 *
 * Cette classe offre la capacité de formatter des informations en HTML et
 * compatible avec le framework CSS Bootstrap.
 */
class MessageFormatter {
    private static $types = array();

    /**
     * Ajoute un callback référencé par son nom.
     * @param $name
     * @param callable $callback
     */
    public static function addType( $name, callable $callback) {
        self::$types[ $name ] = $callback;
    }

    /**
     * Exécute un callback via son nom et retourne ce qu'il peut retourner.
     * @param $name
     * @param $data
     */
    public static function runCallback( $name, $data ) {
        $fun = self::$types[ $name ];
        return $fun( $data );
    }
}
