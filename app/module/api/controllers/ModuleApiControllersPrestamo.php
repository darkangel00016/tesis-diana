<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:33 PM
 */
class ModuleApiControllersPrestamo extends CoreControllers
{

    public function index () {
        $searchCliente = (isset($_GET["cliente"]) && !empty($_GET["cliente"]))?$_GET["cliente"]:"";
        $searchTipoIntervalo = (isset($_GET["tipo_intervalo"]) && !empty($_GET["tipo_intervalo"]))?$_GET["tipo_intervalo"]:array();
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $inputUsuarioSearch = (isset($_GET["usuario"]) && !empty($_GET["usuario"]))?$_GET["usuario"]:array();
        $inputFechaInicioStartSearch = (isset($_GET["fecha_inicio_start"]) && !empty($_GET["fecha_inicio_start"]))?$_GET["fecha_inicio_start"]:"";
        $inputFechaInicioEndSearch = (isset($_GET["fecha_inicio_end"]) && !empty($_GET["fecha_inicio_end"]))?$_GET["fecha_inicio_end"]:"";
        $inputFechaFinStartSearch = (isset($_GET["fecha_fin_start"]) && !empty($_GET["fecha_fin_start"]))?$_GET["fecha_fin_start"]:"";
        $inputFechaFinEndSearch = (isset($_GET["fecha_fin_end"]) && !empty($_GET["fecha_fin_end"]))?$_GET["fecha_fin_end"]:"";

        $searchZona = array();
		$currentUser = CoreRoute::getUserLogged();
		if( $currentUser->getTipo() == "usuario" ) {
			if($currentUser->zonas != null) {
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
        $records = ModuleLoansHelpersPrestamo::listado(array(
            "cliente" => $searchCliente,
            "tipo_intervalo" => $searchTipoIntervalo,
            "estado" => $searchEstado,
	    	"zonas" => $searchZona,
            "usuarios" => $inputUsuarioSearch,
            "fecha_inicio_start" => $inputFechaInicioStartSearch,
            "fecha_inicio_end" => $inputFechaInicioEndSearch,
            "fecha_fin_start" => $inputFechaFinStartSearch,
            "fecha_fin_end" => $inputFechaFinEndSearch,
            "pagina" => 0,
            "paginas" => 0
        ));

        $moduleLoansEntityCuota = new ModuleLoansEntityCuota;
		foreach($records["records"] as $key => $value) {
            $cuotas = $moduleLoansEntityCuota->findAll( array(
                array(
                    "field" => "prestamo",
                    "comparacion" => "=",
                    "value" => $value["id"]
                )
            ),0,0);
			$records["records"][$key]["cuotas"] = $cuotas["records"];

		}

        echo json_encode(array(
            "msg" => _("Lista de Prestamos"),
            "records" => $records["records"],
            "total" => $records["total"],
            "error" => false
        ));
    }

    public function save () {
        echo json_encode(ModuleLoansHelpersPrestamo::save());
    }

    public function delete () {
        echo json_encode(ModuleLoansHelpersPrestamo::delete());
    }

   public function report() {

       $type_report = (isset($_POST["type_report"]) && !empty($_POST["type_report"]))?$_POST["type_report"]:"resumen";
       $fecha_inicio = (isset($_POST["fecha_inicio"]) && !empty($_POST["fecha_inicio"]))?$_POST["fecha_inicio"]:"";
       $fecha_fin = (isset($_POST["fecha_fin"]) && !empty($_POST["fecha_fin"]))?$_POST["fecha_fin"]:"";
       $estado = (isset($_POST["estado"]))?$_POST["estado"]:"";
       $tipo_intervalo = (isset($_POST["tipo_intervalo"]) && !empty($_POST["tipo_intervalo"]))?$_POST["tipo_intervalo"]:"";

       $opciones = array();

       if(!empty($fecha_inicio) && empty($fecha_fin)) {
           $opciones[] = array(
               "comparacion"=> "=date",
               "value" => "'$fecha_inicio'",
               "field" => "date_format(from_unixtime(p.fecha_creacion), '%Y-%m-%d')"
           );
       } else if (!empty($fecha_inicio) && !empty($fecha_fin)) {
           $opciones[] = array(
               "comparacion"=> "btdate",
               "value" => "'$fecha_inicio' AND '$fecha_fin'",
               "field" => "date_format(from_unixtime(p.fecha_creacion), '%Y-%m-%d')"
           );
       }

       if($estado != "") {
           $opciones[] = array(
               "comparacion"=> "=",
               "value" => "$estado",
               "field" => "p.estado"
           );
       }

       if(!empty($tipo_intervalo)) {
           $opciones[] = array(
               "comparacion"=> "=",
               "value" => "$tipo_intervalo",
               "field" => "p.tipo_intervalo"
           );
       }

       $select = "c.nombres, c.apellidos, p.estado, SUM(monto) AS amount, SUM(monto_interes) AS tax_amount";
       $from = "prestamo as p inner join clientes as c on p.cliente = c.id";
       $group_by = "p.estado";
       if($type_report == "by_user") {
           $select = "u.nombre as nombres, '' as apellidos, SUM(monto) AS amount, SUM(monto_interes) AS tax_amount";
           $from = "prestamo as p inner join usuario as u on p.creado_por = u.id";
           $group_by = "p.creado_por";
       }

       $records = ModuleLoansHelpersPrestamo::report(
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
