<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 13/12/15
 * Time: 10:50 AM
 */
class ModuleUserConfig
{
    public static $base = "ModuleUser";

    public static $urlUser = "index.php?m=User";

    public static function twig(Twig_Loader_Filesystem &$loader) {
        $loader->addPath(TEMPLATES . "module/user/", self::$base);
    }

    public static function getUrls(array &$container) {
        $container["user"] = self::$urlUser;
    }
}