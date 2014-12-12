<?php
namespace octopus\core\utils;

/**
 * Class JSONConvertor
 * @package octopus\core
 *
 * Cette classe est outil qui permet de convertir du json au format text en
 * tableau associatif. Et réciproquement, de convertir un tableau
 * associatif en chaîne de caractères au format JSON.
 */
class JSONConvertor {
    /**
     * Converti une chaîne de caractères au format JSON en tableau associatif.
     * @param $text
     * @return mixed
     */
    public static function textToJSON( $text ) {
        return json_decode( $text, true );
    }

    /**
     * Converti un tableau associatif en chaîne de caractères au format JSON.
     * @param $json
     * @return string
     */
    public static function JSONToText( $json ) {
        return json_encode( $json );
    }

    /**
     * Lit un fichier JSON et retourne un tableau associatif.
     * @param $file
     * @return mixed
     */
    public static function parseFile( $file ) {
        if ( !file_exists( $file ) ) {
            return null;
        }
        $f = fopen( $file, 'r' );
        $r = array();
        $t = "";
        while ( ($line = fgets( $f )) != null ) {
            $t .= $line;
        }
        // suppression des commentaires de ligne
        $t = preg_replace( '/\/\/.*$/', "", $t);

        // suppression des commantaires de block
        $t = preg_replace(
            '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', "", $t
        );

        // suppression des espaces et retours à la ligne
        $t = preg_replace( "/(\\s|\\n)/", "", $t );
        fclose( $f );
        return self::textToJSON( $t );
    }
}
