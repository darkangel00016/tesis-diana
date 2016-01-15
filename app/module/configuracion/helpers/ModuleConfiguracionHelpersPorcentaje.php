<?php

/**
 * Class ModuleConfiguracionHelpersPorcentaje
 */
class ModuleConfiguracionHelpersPorcentaje
{
    /**
     * @param $data
     * @return array
     */
    public static function listado($data ) {

        $searchPorcentaje = (isset($_GET["porcentaje"]) && !empty($_GET["porcentaje"]))?$_GET["porcentaje"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();

        $porcentaje = new ModuleConfiguracionEntityPorcentaje;
        $records = $porcentaje->findAll(array(
            array(
                "field" => "porcentaje",
                "comparacion" => "like",
                "value" => $searchPorcentaje
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
            $usuario = new ModuleConfiguracionEntityPorcentaje;
        } else {
            $usuario = new ModuleConfiguracionEntityPorcentaje($id);
        }
        $time = time();
        $usuario->porcentaje = $_POST["porcentaje"];
        $usuario->estado = $_POST["estado"];
        if(empty($id)) {
            $usuario->creado_por = $currentUser->getId();
            $usuario->fecha_creacion = $time;
        } else {
            $usuario->modificado_por = $currentUser->getId();
            $usuario->fecha_modificacion = $time;
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el porcentaje."),
            "error" => true
        );

        if ($usuario->getPorcentaje() == "" || $usuario->getEstado() == "") {
            $error["msg"] = _("Hay campos vacios.");
        } else {
            $rowChanges = $usuario->save();
            $errorno = $usuario->getDbInstance()->errornost();
            if ($rowChanges) {
                $error["msg"] = _("Se ha guardado el porcentaje exitosamente.");
                $error["id"] = (empty($id))?$rowChanges:$id;
                $error["error"] = false;
            } else {
                if($errorno == 1062) {
                    $error["msg"] = _("Porcentaje duplicado.");
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
            "msg" => _("Ocurrio un error al eliminar el porcentaje."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $usuario = new ModuleConfiguracionEntityPorcentaje($id);
            if($usuario->delete()) {
                $error["msg"] = _("Se elimino el registro exitosamente.");
                $error["error"] = false;
            }
        }
        return $error;
    }

}