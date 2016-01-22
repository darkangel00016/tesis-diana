<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 16/11/2015
 * Time: 23:10
 */

class CoreRoute {

    static public $access = array(
        "public" => array(
            "login/main/(index|access|catpcha)"
        ),
        "logged" => array(
            "main/index",
            "login/main/logout",
            "user/main/(edit|save)",
            "managermail/(estudiante|supervisor)/(index|listado)",
        ),
        "admin" => array(
            "user/main/(index|listado|add|edit|delete|save)",
            "configuracion/main/(index|add|edit|delete|save)",
            "managermail/(estudiante|supervisor)/(index|listado|add|edit|save|delete|import|model)"
        )
    );

    static public $redirectLogin = "login/main/index";

    static function ini($twig) {

        self::filter($twig);

        $module = (isset($_REQUEST["m"]))?$_REQUEST["m"]:"";
        $controller = (isset($_REQUEST["c"]))?$_REQUEST["c"]:DEFAULT_CONTROLLER;
        $action = (isset($_REQUEST["a"]))?$_REQUEST["a"]:DEFAULT_ACTION;

        $class = "";
        if(!empty($module)) {
            $class .= "Module" . $module;
        }
        if(!empty($controller)) {
            $class .= "Controllers" . $controller;
        }
        $c = new $class($twig);
        if(!empty($action) && $c) {
            if(method_exists($c, $action)) {
                $c->$action();
            } else {
                //Lanzar error action not exist
            }
        }
    }

    public static function paths()
    {
        $loader = new Twig_Loader_Filesystem(TEMPLATES);
        $loader->addPath(TEMPLATES . "module/user/", 'ModuleUser');
        $loader->addPath(TEMPLATES . "modales/", 'Modal');
        ModuleManagerMailConfig::twig($loader);
        ModuleLoginConfig::twig($loader);
        ModuleConfiguracionConfig::twig($loader);
        return $loader;
    }

    public static function getUserLogged() {
        $usr = isset($_SESSION["usr"])?$_SESSION["usr"]:"";
        $usuario = null;
        if(!empty($usr)) {
            $usuario = new ModuleUserEntityUsuario($usr);
        }
        return $usuario;
    }

    public static function isAdmin() {
        $currentUser = self::getUserLogged();
        $response = false;
        if(is_object($currentUser) && $currentUser->getTipo() == "admin") {
            $response = true;
        }
        return $response;
    }

    public static function isLogged() {
        $usr = isset($_SESSION["usr"])?$_SESSION["usr"]:"";
        $usuario = new ModuleUserEntityUsuario;
        $records = array();
        if(!empty($usr)) {
            $records = $usuario->find($usr->getId());
        }
        return sizeof($records) > 0;
    }

    public static function validate () {
        $module = (isset($_REQUEST["m"]))?$_REQUEST["m"]:"";
        $controller = (isset($_REQUEST["c"]))?$_REQUEST["c"]:DEFAULT_CONTROLLER;
        $action = (isset($_REQUEST["a"]))?$_REQUEST["a"]:DEFAULT_ACTION;

        $ruta = "";
        if(!empty($module)) {
            $ruta .= strtolower($module) . "/";
        }
        if(!empty($controller)) {
            $ruta .= strtolower($controller) . "/";
        }
        if(!empty($action)) {
            $ruta .= strtolower($action);
        }

        $user = self::getUserLogged();
        $canIn = false;
        $isPublic = false;
        if (empty($user)) {
            if(isset(self::$access["public"]) && sizeof(self::$access["public"]) > 0) {
                //Solo valido lo publico
                foreach (self::$access["public"] as $value) {
                    if(preg_match("%^" . $value . "$%", $ruta)) {
                        $canIn = true;
                        $isPublic = true;
                        break;
                    }
                }
            }
        } else {
            //Valido logged
            if(isset(self::$access["logged"]) && sizeof(self::$access["logged"]) > 0) {
                foreach (self::$access["logged"] as $value) {

                    if (preg_match("%^" . $value . "$%", $ruta)) {
                        $canIn = true;
                        break;
                    }
                }
            }
            //Valido por tipo si no coincide en logged
            if(!$canIn) {
                if(isset(self::$access[$user->getTipo()]) && sizeof(self::$access[$user->getTipo()]) > 0) {
                    foreach (self::$access[$user->getTipo()] as $value) {
                        if (preg_match("%^" . $value . "$%", $ruta)) {
                            $canIn = true;
                            break;
                        }
                    }
                }
            }
        }

        if(!$canIn && empty($user) && !$isPublic) {
            header('Location: index.php?m=Login');
            die;
        } else if (!$canIn && !empty($user)) {
            header('Location: index.php');
            die;
        }

    }

    public static function getUrl($path)
    {
        return URL . $path;
    }

    public static function getWhere($opciones)
    {
        $w = array();
        foreach ($opciones as $value) {
            switch($value["comparacion"]) {
                case "=":
                    if($value["value"] != "") {
                        $w[] = "{$value["field"]} = '{$value["value"]}'";
                    }
                    break;
                case "like":
                    if($value["value"] != "") {
                        $w[] = "{$value["field"]} like('%{$value["value"]}%')";
                    }
                    break;
                case "in":
                    if(sizeof($value["value"]) > 0) {
                        $aux = array();
                        foreach ($value["value"] as $v) {
                            $aux[] = "'$v'";
                        }
                        $w[] = "{$value["field"]} in (" . implode(",", $aux) . ")";
                    }
                    break;
            }
        }
        return $w;
    }

    public static function getConfig()
    {
        $config = CoreConfig::getConfig();
        return $config->getData();
    }

    public static function db()
    {
        $coreDb = new CoreDb(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        try {
            $coreDb->connect();
        } catch (Exception $e) {

        }
    }

    private static function filter(Twig_Environment &$twig)
    {
        $twig->addExtension(new Twig_Extensions_Extension_I18n());
        $twig->addExtension(new Twig_Extension_Debug());

        $function = new Twig_SimpleFunction('session_user', function () {
            return CoreRoute::getUserLogged();
        });
        $twig->addFunction($function);

        $function = new Twig_SimpleFunction('get_url', function ($ruta) {
            return CoreRoute::getUrl($ruta);
        });
        $twig->addFunction($function);

        $function = new Twig_SimpleFunction('get_config', function () {
            return CoreRoute::getConfig();
        });
        $twig->addFunction($function);
    }

} 