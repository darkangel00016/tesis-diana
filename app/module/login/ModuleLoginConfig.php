<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 13/12/15
 * Time: 10:50 AM
 */
class ModuleLoginConfig
{
    public static $base = "ModuleLogin";
    public static $urlLogin = "index.php?m=Login";

    public static function twig(Twig_Loader_Filesystem &$loader) {
        $loader->addPath(TEMPLATES . "module/login/", self::$base);
    }

    public static function getUrls(array &$container) {
        $container["metodo"] = self::$urlLogin;
    }
}