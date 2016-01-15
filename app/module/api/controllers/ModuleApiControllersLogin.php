<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:33 PM
 */
class ModuleApiControllersLogin extends CoreControllers
{
    public static $urlBase = "index.php?m=Api&c=Login";

    public function login () {
        $error = ModuleLoginHelpersLogin::access(array(
            "email" => $_POST["email"],
            "clave" => $_POST["password"]
        ));
        echo json_encode($error);
    }

    public function logout () {
        session_destroy();
        echo json_encode(array(
            "msg" => _("Sesión cerrada exitosamente"),
            "error" => false
        ));
    }

    public function forgot() {
        echo json_encode(ModuleLoginHelpersLogin::forgot($this->twig));
    }

    public function check() {
        echo json_encode(ModuleLoginHelpersLogin::check());
    }

}
