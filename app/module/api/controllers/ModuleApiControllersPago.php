<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:33 PM
 */
class ModuleApiControllersPago extends CoreControllers
{

    public function index () {
        $searchCliente = (isset($_GET["cliente"]) && !empty($_GET["cliente"]))?$_GET["cliente"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $searchCobrador = (isset($_GET["cobrador"]) && !empty($_GET["cobrador"]))?$_GET["cobrador"]:array();
        $inputFechaStartSearch = (isset($_GET["fecha_start"]) && !empty($_GET["fecha_start"]))?$_GET["fecha_start"]:"";
        $inputFechaEndSearch = (isset($_GET["fecha_end"]) && !empty($_GET["fecha_end"]))?$_GET["fecha_end"]:"";

        $currentUser = CoreRoute::getUserLogged();
        if( $currentUser->getTipo() == "usuario" ) {
            $searchCobrador = $currentUser->getId();
        }

        $records = ModuleLoansHelpersPago::listado(array(
            "cliente" => $searchCliente,
            "cobrador" => $searchCobrador,
            "estado" => $searchEstado,
            "fecha_start" => $inputFechaStartSearch,
            "fecha_end" => $inputFechaEndSearch,
            "pagina" => 0,
            "paginas" => 0
        ));

        $moduleLoansEntityPagoItems = new ModuleLoansEntityPagoItems;
        foreach($records["records"] as $key => $value) {
            $cuotas = $moduleLoansEntityPagoItems->findAll( array(
                array(
                    "field" => "pago",
                    "comparacion" => "=",
                    "value" => $value["id"]
                )
            ),0,0);
            $records["records"][$key]["items"] = $cuotas["records"];
        }

        echo json_encode(array(
            "msg" => _("Lista de Pagos"),
            "records" => $records["records"],
            "total" => $records["total"],
            "error" => false
        ));
    }

    public function save () {
        $save = ModuleLoansHelpersPago::save();
        if(isset($save["new"]) && $save["new"]) {
            $estado = $_POST["estado"];
            $response = false;
            if($estado == 1) {
                $response = ModuleLoansHelpersPago::aprobar();
            }
            if($response) {
                if($response["error"]) {
                    $save["msg"] = $save["msg"] . ". " . $response["msg"];
                }
            }
        }
        echo json_encode($save);
    }

    public function delete () {
        echo json_encode(ModuleLoansHelpersPago::delete());
    }

    public function rechazar () {
        echo json_encode(ModuleLoansHelpersPago::rechazar());
    }

    public function aprobar () {
        echo json_encode(ModuleLoansHelpersPago::aprobar());
    }

    public function reversar () {
        echo json_encode(ModuleLoansHelpersPago::reversar());
    }

    public function report() {

        $type_report = (isset($_POST["type_report"]) && !empty($_POST["type_report"]))?$_POST["type_report"]:"resumen";
        $fecha_inicio = (isset($_POST["fecha_inicio"]) && !empty($_POST["fecha_inicio"]))?$_POST["fecha_inicio"]:"";
        $fecha_fin = (isset($_POST["fecha_fin"]) && !empty($_POST["fecha_fin"]))?$_POST["fecha_fin"]:"";
        $estado = (isset($_POST["estado"]))?$_POST["estado"]:"";
        $metodo = (isset($_POST["metodo"]) && !empty($_POST["metodo"]))?$_POST["metodo"]:"";

        $opciones = array();

        if(!empty($fecha_inicio) && empty($fecha_fin)) {
            $opciones[] = array(
                "comparacion"=> "=date",
                "value" => "'$fecha_inicio'",
                "field" => "date_format(from_unixtime(p.fecha), '%Y-%m-%d')"
            );
        } else if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $opciones[] = array(
                "comparacion"=> "btdate",
                "value" => "'$fecha_inicio' AND '$fecha_fin'",
                "field" => "date_format(from_unixtime(p.fecha), '%Y-%m-%d')"
            );
        }

        if($estado != "") {
            $opciones[] = array(
                "comparacion"=> "=",
                "value" => "$estado",
                "field" => "p.estado"
            );
        }

        if($metodo != "") {
            $opciones[] = array(
                "comparacion"=> "=",
                "value" => "$metodo",
                "field" => "p.metodo"
            );
        }

        $select = "c.nombres, c.apellidos, p.estado, SUM(monto) AS amount";
        $from = "pago as p inner join clientes as c on p.cliente = c.id";
        $group_by = "p.estado";
        if($type_report == "by_user") {
            $select = "u.nombre as nombres, '' as apellidos, SUM(monto) AS amount";
            $from = "pago as p inner join usuario as u on p.cobrador = u.id";
            $group_by = "p.cobrador";
        }

        $records = ModuleLoansHelpersPago::report(
            $select,
            $from,
            $group_by,
            $opciones
        );
        $error = array(
            "msg" => _("No hay resultados que cumplan con el criterio de busqueda."),
            "error" => true
        );
        if(sizeof($records) > 0) {
            $error["msg"] = _("Resultados encontrados.");
            $error["error"] = false;
            $error["records"] = $records;
        }
        echo json_encode($error);
    }
}