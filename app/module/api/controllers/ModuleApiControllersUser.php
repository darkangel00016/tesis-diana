<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:33 PM
 */
class ModuleApiControllersUser extends CoreControllers
{

    public function save () {
        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        if(empty($id)) {
            $error = array(
                "msg" => _("El usuario es invalido."),
                "error" => true
            );
        } else {
            $error = ModuleUserHelpersUsuario::save();
        }
        echo json_encode($error);
    }
}