<?php

/**
 * Class ModuleConfiguracionHelpersZona
 */
class ModuleConfiguracionHelpersZona
{
    /**
     * @param $data
     * @return array
     */
    public static function listado($data ) {

        $searchZona = (isset($_GET["zona"]) && !empty($_GET["zona"]))?$_GET["zona"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();

        $zona = new ModuleConfiguracionEntityZona;
        $records = $zona->findAll(array(
            array(
                "field" => "zona",
                "comparacion" => "like",
                "value" => $searchZona
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
            $usuario = new ModuleConfiguracionEntityZona;
        } else {
            $usuario = new ModuleConfiguracionEntityZona($id);
        }
        $time = time();
        $usuario->zona = $_POST["zona"];
        $usuario->estado = $_POST["estado"];
        if(empty($id)) {
            $usuario->creado_por = $currentUser->getId();
            $usuario->fecha_creacion = $time;
        } else {
            $usuario->modificado_por = $currentUser->getId();
            $usuario->fecha_modificacion = $time;
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el zona."),
            "error" => true
        );

        if ($usuario->getZona() == "" || $usuario->getEstado() == "") {
            $error["msg"] = _("Hay campos vacios.");
        } else {
            $rowChanges = $usuario->save();
            $errorno = $usuario->getDbInstance()->errornost();
            if ($rowChanges) {
                $error["msg"] = _("Se ha guardado el zona exitosamente.");
                $error["id"] = (empty($id))?$rowChanges:$id;
                $error["error"] = false;
            } else {
                if($errorno == 1062) {
                    $error["msg"] = _("Zona duplicado.");
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
            "msg" => _("Ocurrio un error al eliminar el zona."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $usuario = new ModuleConfiguracionEntityZona($id);
            if($usuario->delete()) {
                $error["msg"] = _("Se elimino el registro exitosamente.");
                $error["error"] = false;
            }
        }
        return $error;
    }

}