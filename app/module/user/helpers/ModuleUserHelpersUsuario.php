<?php

/**
 * Class ModuleUserHelpersUsuario
 */
class ModuleUserHelpersUsuario
{

    /**
     * @return array
     */
    public static function save () {

        $currentUser = CoreRoute::getUserLogged();

        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        if(empty($id)) {
            $usuario = new ModuleUserEntityUsuario;
        } else {
            $usuario = new ModuleUserEntityUsuario($id);
        }
        $time = time();
        $usuario->usuario = $_POST["email"];
        $usuario->nombre = $_POST["nombre"];
        if(empty($id)) {
            $usuario->passusuario = md5($_POST["password"]);
            $usuario->creado_por = $currentUser->getId();
            $usuario->fecha_creacion = $time;
        } else {
            $usuario->modificado_por = $currentUser->getId();
            $usuario->fecha_modificacion = $time;
            $password = (isset($_POST["password"]) && !empty($_POST["password"]))?$_POST["password"]:"";
            if(!empty($password) && $usuario->passusuario != md5($password)) {
                $usuario->passusuario = md5($password);
            }
        }
        if(CoreRoute::isAdmin()) {
            if(!empty($_POST["tipo"])) {
                $usuario->tipo = @$_POST["tipo"];
            }
            if(!empty($_POST["estado"])) {
                $usuario->estado = @$_POST["estado"];
            }
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el usuario."),
            "error" => true
        );
        if(!CoreRoute::isAdmin() && ($id != $currentUser->getId())){
            $error["msg"] = _("No coincide el usuario logeado con el enviado.");
        } else {
            if ($usuario->getUsuario() == "" || ($usuario->getPassusuario() == "" && $usuario->isNew) || $usuario->getTipo() == "") {
                $error["msg"] = _("Hay campos vacios.");
            } else {
                $rowChanges = $usuario->save();
                $errorno = $usuario->getDbInstance()->errornost();
                //Asignar zonas
                $zonas = (isset($_POST["zonas"]) && is_array($_POST["zonas"]))?$_POST["zonas"]:array();
                $zonaUsuario = new ModuleConfiguracionEntityZonaUsuario;
                if(!empty($id)) {
                    $zonaUsuario->deleteBy(array(
                        array("field"=> "usuario", "value"=> $id, "comparacion"=> "=")
                    ));
                }
                $idUsuario = empty($id)?$rowChanges:$id;
                foreach($zonas as $value) {
                    $oZonaUsuario = new ModuleConfiguracionEntityZonaUsuario;
                    $oZonaUsuario->id = $idUsuario . "-" . $value;
                    $oZonaUsuario->usuario = $idUsuario;
                    $oZonaUsuario->zona = $value;
                    $oZonaUsuario->isNew = true;
                    $oZonaUsuario->save();
                }
                if ($rowChanges > 0) {
                    $error["msg"] = _("Se ha guardado el usuario exitosamente.");
                    $error["error"] = false;
                } else if($errorno == 1062){
                    $error["msg"] = _("Usuario duplicado.");
                } else {
                    $error["msg"] = _("No hubo cambios en el registro.");
                }
            }
        }

        return $error;

    }
}