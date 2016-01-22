<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 13/12/15
 * Time: 10:50 AM
 */
class ModuleManagerMailConfig
{
    public static $base = "ModuleManagerMail";
    public static $estudiante = "Estudiante";
    public static $supervisor = "Supervisor";
    public static $urlBase = "index.php?m=ManagerMail";
    public static $urlEstudiante = "index.php?m=ManagerMail&c=Estudiante";
    public static $urlSupervisor = "index.php?m=ManagerMail&c=Supervisor";

    public static function twig(Twig_Loader_Filesystem &$loader) {
        $loader->addPath(TEMPLATES . "module/managermail", self::$base);
        $loader->addPath(TEMPLATES . "module/managermail/estudiantes", self::getEstudiante());
        $loader->addPath(TEMPLATES . "module/managermail/supervisores", self::getSupervisor());
    }

    public static function getUrls(array &$container) {
        $container["managermail"] = self::$urlBase;
        $container["estudiantes"] = self::$urlEstudiante;
        $container["supervisores"] = self::$urlSupervisor;
    }

    public static function getEstudiante() {
        return self::$base . self::$estudiante;
    }

    public static function getSupervisor() {
        return self::$base . self::$supervisor;
    }
}