<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 13/12/15
 * Time: 10:50 AM
 */
class ModuleConfiguracionConfig
{
    public static $base = "ModuleConfiguracion";
    public static $porcentaje = "Porcentaje";
    public static $zona = "Zona";
    public static $metodo = "Metodo";

    public static $urlMetodo = "index.php?m=Configuracion&c=Metodo";
    public static $urlPorcentaje = "index.php?m=Configuracion&c=Porcentaje";
    public static $urlZona = "index.php?m=Configuracion&c=Zona";

    public static function twig(Twig_Loader_Filesystem &$loader) {
        $base = "ModuleConfiguracion";
        $loader->addPath(TEMPLATES . "module/configuracion/", $base);
        $loader->addPath(TEMPLATES . "module/configuracion/porcentaje", $base . self::$porcentaje);
        $loader->addPath(TEMPLATES . "module/configuracion/zona", $base . self::$zona);
        $loader->addPath(TEMPLATES . "module/configuracion/metodo", $base . self::$metodo);
    }

    public static function getMain() {
        return self::$base;
    }

    public static function getPorcentaje() {
        return self::$base . self::$porcentaje;
    }

    public static function getZona() {
        return self::$base . self::$zona;
    }

    public static function getMetodo() {
        return self::$base . self::$metodo;
    }

    public static function getUrls(array &$container) {
        $container["metodo"] = self::$urlMetodo;
        $container["porcentaje"] = self::$urlPorcentaje;
        $container["zona"] = self::$urlZona;
    }
}