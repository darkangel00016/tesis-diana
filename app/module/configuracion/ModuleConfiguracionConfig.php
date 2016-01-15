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
    public static $urlBase = "index.php?m=Configuracion";

    public static function twig(Twig_Loader_Filesystem &$loader) {
        $base = "ModuleConfiguracion";
        $loader->addPath(TEMPLATES . "module/configuracion/", $base);
    }

    public static function getMain() {
        return self::$base;
    }

    public static function getUrls(array &$container) {
        $container["configuracion"] = self::$urlBase;
    }
}