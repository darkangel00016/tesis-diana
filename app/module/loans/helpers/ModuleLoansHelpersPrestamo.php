<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:37 PM
 */
class ModuleLoansHelpersPrestamo
{
    public static function listado( $data ) {

        $searchCliente = (isset($data["cliente"]) && !empty($data["cliente"]))?$data["cliente"]:"";
        $searchTipoIntervalo = (isset($data["tipo_intervalo"]) && !empty($data["tipo_intervalo"]))?$data["tipo_intervalo"]:array();
        $searchEstado = (isset($data["estado"]) && !empty($data["estado"]))?$data["estado"]:array();
        $searchIdCliente = (isset($data["idcliente"]) && !empty($data["idcliente"]))?$data["idcliente"]:"";
        $searchZonas = (isset($data["zonas"]) && !empty($data["zonas"]))?$data["zonas"]:array();
        $inputUsuarioSearch = (isset($data["usuario"]) && !empty($data["usuario"]))?$data["usuario"]:array();
        $inputFechaInicioStartSearch = (isset($data["fecha_inicio_start"]) && !empty($data["fecha_inicio_start"]))?$data["fecha_inicio_start"]:"";
        $inputFechaInicioEndSearch = (isset($data["fecha_inicio_end"]) && !empty($data["fecha_inicio_end"]))?$data["fecha_inicio_end"]:"";
        $inputFechaFinStartSearch = (isset($data["fecha_fin_start"]) && !empty($data["fecha_fin_start"]))?$data["fecha_fin_start"]:"";
        $inputFechaFinEndSearch = (isset($data["fecha_fin_end"]) && !empty($data["fecha_fin_end"]))?$data["fecha_fin_end"]:"";
        $cliente = new ModuleLoansEntityPrestamo;
        $records = $cliente->findAll(array(
            array(
                "field" => 'c.nombres like (\'%%%1$s%%\') OR c.apellidos like (\'%%%1$s%%\')',
                "comparacion" => "complex",
                "value" => $searchCliente
            ),
            array(
                "field" => "tipo_intervalo",
                "comparacion" => "in",
                "value" => $searchTipoIntervalo
            ),
            array(
                "field" => "cliente",
                "comparacion" => "=",
                "value" => $searchIdCliente
            ),
            array(
                "field" => "fecha_inicio",
                "comparacion" => ">=",
                "value" => CoreFilter::date($inputFechaInicioStartSearch)
            ),
            array(
                "field" => "fecha_inicio",
                "comparacion" => "<=",
                "value" => CoreFilter::date($inputFechaInicioEndSearch)
            ),
            array(
                "field" => "fecha_fin",
                "comparacion" => ">=",
                "value" => CoreFilter::date($inputFechaFinStartSearch)
            ),
            array(
                "field" => "fecha_fin",
                "comparacion" => "<=",
                "value" => CoreFilter::date($inputFechaFinEndSearch)
            ),
            array(
                "field" => "p.creado_por",
                "comparacion" => "in",
                "value" => $inputUsuarioSearch
            ),
            array(
                "field" => "estado",
                "comparacion" => "in",
                "value" => $searchEstado
            ),
            array(
                "field" => "c.zona",
                "comparacion" => "in",
                "value" => $searchZonas
            )
        ), $data["pagina"], $data["paginas"]);
        return $records;
    }

    public static function report( $select, $from, $groupby, $opciones = array() ) {
        $db = CoreDb::getInstance();
        $where = "";
        // select all active users
        if(sizeof($opciones) > 0) {
            $w = CoreRoute::getWhere($opciones);
            if(sizeof($w) > 0) {
                $where = " WHERE " . implode(" AND ", $w);
            }
        }
        $query = "select
            $select
        from
            $from ".$where.((empty($groupby)?"":"group by $groupby"));
        $result = $db->query($query);
        return $result;
    }

    public static function save () {

        $currentUser = CoreRoute::getUserLogged();

        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        if(empty($id)) {
            $prestamo = new ModuleLoansEntityPrestamo;
            $prestamo->isNew = true;
        } else {
            $prestamo = new ModuleLoansEntityPrestamo($id);
        }
        $time = time();
        if(empty($id)) {
            $prestamo->cliente = $_POST["cliente"];
            $prestamo->monto = $_POST["monto"];
            $prestamo->interes = $_POST["interes"];
            $prestamo->tipo_intervalo = $_POST["tipo_intervalo"];
            $prestamo->intervalo = $_POST["intervalo"];
            $prestamo->fecha_inicio = $_POST["fecha_inicio"];
            $prestamo->fecha_fin = $_POST["fecha_fin"];
            $prestamo->monto_interes = @$_POST["monto_interes"];
        }
        $prestamo->observaciones = $_POST["observaciones"];
        $prestamo->estado = $_POST["estado"];
        if(empty($id)) {
            $prestamo->creado_por = $currentUser->getId();
            $prestamo->fecha_creacion = $time;
            $prestamo->saldo = @$_POST["saldo"];
        } else {
            $prestamo->modificado_por = $currentUser->getId();
            $prestamo->fecha_modificacion = $time;
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el prestamo."),
            "error" => true
        );
        $validateForm = self::validateForm($prestamo);
        if (!$validateForm[0]) {
            $error["msg"] = _("Hay campos vacios.");
            $error["campos"] = $validateForm[1];
        } else {
            $rowChanges = $prestamo->save();
            $errorno = $prestamo->getDbInstance()->errornost();
            $id_prestamo = (empty($id))?$prestamo->getDbInstance()->getInsertId():$id;
            if ($rowChanges) {
                $error["msg"] = _("Se ha guardado el prestamo exitosamente.");
                $error["error"] = false;
                $error["id"] = $id_prestamo;
                //Generando Cuotas
                if(empty($id)) {
                    $date = new DateTime($prestamo->fecha_inicio);
                    switch ($prestamo->tipo_intervalo) {
                        case "diario": $intervalo = "D";break;
                        case "semanal": $intervalo = "W";break;
                        case "mensual": $intervalo = "M";break;
                        case "anual": $intervalo = "Y";break;
                    }
                    $interval = new DateInterval('P1' . $intervalo);
                    $monto = $prestamo->monto/$prestamo->intervalo;
                    $interes = $prestamo->monto_interes/$prestamo->intervalo;
                    for($i = 0; $i < $prestamo->intervalo; $i++) {
                        $date->add($interval);
                        $cuota = new ModuleLoansEntityCuota;
                        $cuota->monto = $monto;
                        $cuota->interes = $interes;
                        $cuota->fecha = $date->format('Y-m-d');
                        $cuota->prestamo = $id_prestamo;
                        $cuota->isNew = true;
                        $cuota->save();
                    }
                    $moduleLoansEntityCuota = new ModuleLoansEntityCuota;
                    $cuotas = $moduleLoansEntityCuota->findAll( array(
                        array(
                            "field" => "prestamo",
                            "comparacion" => "=",
                            "value" => $id_prestamo
                        )
                    ),0,0);
                    $error["cuotas"] = $cuotas["records"];
                }
            } else if($errorno == 1062){
                $error["msg"] = _("Prestamo duplicado.");
                $error["code_error"] = $prestamo->getDbInstance()->errornost();
                $error["code_error_msg"] = $prestamo->getDbInstance()->getLastError();
            } else if($errorno == 1452){
                $error["msg"] = _("El cliente asignado no existe.");
                $error["code_error"] = $prestamo->getDbInstance()->errornost();
                $error["code_error_msg"] = $prestamo->getDbInstance()->getLastError();
            } else {
                $error["msg"] = _("No hubo cambios en el registro.");
            }
        }

        return $error;

    }

    public static function validateForm (ModuleLoansEntityPrestamo $prestamo) {
        $isValid = true;
        $campos = array();
        if($prestamo->getMonto() == "") {
            $isValid = false;
            $campos[] = "monto";
        }
        if($prestamo->getInteres() == "") {
            $isValid = false;
            $campos[] = "interes";
        }
        if($prestamo->getTipoIntervalo() == "") {
            $isValid = false;
            $campos[] = "tipo_intervalo";
        }
        if($prestamo->getIntervalo() == "") {
            $isValid = false;
            $campos[] = "intervalo";
        }
        if($prestamo->getFechaInicio() == "") {
            $isValid = false;
            $campos[] = "fecha_inicio";
        }
        if($prestamo->getFechaFin() == "") {
            $isValid = false;
            $campos[] = "fecha_fin";
        }
        if($prestamo->getMontoInteres() == "") {
            $isValid = false;
            $campos[] = "monto_interes";
        }
        if($prestamo->getSaldo() == "") {
            $isValid = false;
            $campos[] = "saldo";
        }
        if($prestamo->getEstado() == "") {
            $isValid = false;
            $campos[] = "estado";
        }
        return array($isValid, $campos);
    }

    public static function delete () {

        $id = $_POST["id"];
        $error = array(
            "msg" => _("Ocurrio un error al eliminar el prestamo."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $prestamo = new ModuleLoansEntityPrestamo($id);
            if($prestamo->delete()) {
                $error["msg"] = _("Se elimino el registro exitosamente.");
                $error["error"] = false;
            } else {
                if($prestamo->getDbInstance()->errornost() == "1451") {
                    $error["msg"] = _("El prestamo tiene cuotas asignadas por lo que no puede ser borrado.");
                }
                $error["code_error"] = $prestamo->getDbInstance()->errornost();
            }
        }
        return $error;
    }

}
