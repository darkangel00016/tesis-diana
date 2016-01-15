<?php

/**
 * Class ModuleConfiguracionHelpersMetodo
 */
class ModuleConfiguracionHelpersMetodo
{
    /**
     * @param $data
     * @return array
     */
    public static function listado($data ) {

        $searchMetodo = (isset($_GET["metodo"]) && !empty($_GET["metodo"]))?$_GET["metodo"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();

        $metodo = new ModuleConfiguracionEntityMetodo;
        $records = $metodo->findAll(array(
            array(
                "field" => "metodo",
                "comparacion" => "like",
                "value" => $searchMetodo
            ),
            array(
                "field" => "estado",
                "comparacion" => "in",
                "value" => $searchEstado
            )
        ), $data["pagina"], $data["paginas"]);
        return $records;
    }

    /**
     * @return array
     */
    public static function save () {

        $currentUser = CoreRoute::getUserLogged();

        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        if(empty($id)) {
            $usuario = new ModuleConfiguracionEntityMetodo;
        } else {
            $usuario = new ModuleConfiguracionEntityMetodo($id);
        }
        $time = time();
        $usuario->metodo = $_POST["metodo"];
        $usuario->estado = $_POST["estado"];
        if(empty($id)) {
            $usuario->creado_por = $currentUser->getId();
            $usuario->fecha_creacion = $time;
        } else {
            $usuario->modificado_por = $currentUser->getId();
            $usuario->fecha_modificacion = $time;
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el metodo."),
            "error" => true
        );

        if ($usuario->getMetodo() == "" || $usuario->getEstado() == "") {
            $error["msg"] = _("Hay campos vacios.");
        } else {
            $rowChanges = $usuario->save();
            $errorno = $usuario->getDbInstance()->errornost();
            if ($rowChanges) {
                $error["msg"] = _("Se ha guardado el metodo exitosamente.");
                $error["id"] = (empty($id))?$rowChanges:$id;
                $error["error"] = false;
            } else {
                if($errorno == 1062) {
                    $error["msg"] = _("Metodo duplicado.");
                } else {
                    $error["msg"] = _("No hubo cambios en el registro.");
                }
                $error["code_error"] = $errorno;
            }
        }

        return $error;

    }

    /**
     * @return array
     */
    public static function delete () {
        $id = $_POST["id"];
        $error = array(
            "msg" => _("Ocurrio un error al eliminar el metodo."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $usuario = new ModuleConfiguracionEntityMetodo($id);
            if($usuario->delete()) {
                $error["msg"] = _("Se elimino el registro exitosamente.");
                $error["error"] = false;
            }
        }
        return $error;
    }

}