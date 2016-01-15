<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 13/12/15
 * Time: 10:50 AM
 */
class ModuleApiConfig
{
    public static $base = "ModuleApi";

    public static $urlApi = "index.php?m=Api";

    public static function twig(Twig_Loader_Filesystem &$loader) {
        $loader->addPath(TEMPLATES . "module/api/", self::$base);
    }

    public static function getUrls(array &$container) {
        $container["api"] = self::$urlApi;
    }
}