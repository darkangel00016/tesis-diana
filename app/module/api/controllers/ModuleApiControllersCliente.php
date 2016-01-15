<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:33 PM
 */
class ModuleApiControllersCliente extends CoreControllers
{

    public function index () {
        $searchNombres = (isset($_GET["nombres"]) && !empty($_GET["nombres"]))?$_GET["nombres"]:"";
        $searchApellidos = (isset($_GET["apellidos"]) && !empty($_GET["apellidos"]))?$_GET["apellidos"]:"";
        $searchTelefono = (isset($_GET["telefono"]) && !empty($_GET["telefono"]))?$_GET["telefono"]:"";
        $searchDireccion = (isset($_GET["direccion"]) && !empty($_GET["direccion"]))?$_GET["direccion"]:"";
        $searchBarrio = (isset($_GET["barrio"]) && !empty($_GET["barrio"]))?$_GET["barrio"]:"";
        $searchCiudad = (isset($_GET["ciudad"]) && !empty($_GET["ciudad"]))?$_GET["ciudad"]:"";
        $searchObservaciones = (isset($_GET["observaciones"]) && !empty($_GET["observaciones"]))?$_GET["observaciones"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $searchZona = (isset($_GET["zona"]) && !empty($_GET["zona"]))?$_GET["zona"]:array();

        $currentUser = CoreRoute::getUserLogged();
        if( $currentUser->getTipo() == "usuario" ) {
            if($currentUser->zonas != null) {
                $searchZona = array();
                foreach($currentUser->zonas as $value) {
                    $searchZona[] = $value->getId();
                }
            } else {
                echo json_encode(array(
                    "msg" => _("No tiene zonas asignadas."),
                    "records" => array(),
                    "total" => 0,
                    "error" => true
                ));
                return;
            }
        }

        $records = ModuleLoansHelpersCliente::listado(array(
            "nombres" => $searchNombres,
            "apellidos" => $searchApellidos,
            "telefono" => $searchTelefono,
            "direccion" => $searchDireccion,
            "barrio" => $searchBarrio,
            "ciudad" => $searchCiudad,
            "observaciones" => $searchObservaciones,
            "estado" => $searchEstado,
            "zona" => $searchZona,
            "pagina" => 0,
            "paginas" => 0
        ));

        echo json_encode(array(
            "msg" => _("Lista de clientes"),
            "records" => $records["records"],
            "total" => $records["total"],
            "error" => false
        ));
    }

    public function save () {
        echo json_encode(ModuleLoansHelpersCliente::save());
    }

    public function delete () {
        echo json_encode(ModuleLoansHelpersCliente::delete());
    }

    public function download() {
        header('Content-Type: application/download');
        header('Content-Disposition: attachment; filename="'.$_POST["file"].'"');
        header("Content-Length: " . filesize(CACHE . $_POST["file"]));
        $fp = fopen(CACHE . $_POST["file"], "r");
        fpassthru($fp);
        fclose($fp);
    }
}