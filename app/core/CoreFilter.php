<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 12/12/15
 * Time: 02:40 PM
 */
class CoreFilter
{

    public static function currency($string) {
        $config = CoreRoute::getConfig();
        return (($config["simbolo_posicion"] == "i")?$config["simbolo_moneda"] . " ":"") . sprintf("%.2f", $string) . (($config["simbolo_posicion"] == "d")?" " . $config["simbolo_moneda"]:"");
    }

    public static function interes($string) {
        return sprintf("%.2f", $string) . "%";
    }

    public static function date($date, $sepIn = "/", $sepOut = "-") {
        return str_replace($sepIn, $sepOut, $date);
    }

    public static function json_decode($string) {
        return json_decode($string);
    }

}