<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:37 PM
 */
class ModuleLoansHelpersPago
{
    public static function listado( $data ) {

        $searchCliente = (isset($data["cliente"]) && !empty($data["cliente"]))?$data["cliente"]:"";
        $searchEstado = (isset($data["estado"]) && !empty($data["estado"]))?$data["estado"]:array();
        $searchCobrador = (isset($data["cobrador"]) && !empty($data["cobrador"]))?$data["cobrador"]:array();
        $inputFechaStartSearch = (isset($data["fecha_start"]) && !empty($data["fecha_start"]))?$data["fecha_start"]:"";
        $inputFechaEndSearch = (isset($data["fecha_end"]) && !empty($data["fecha_end"]))?$data["fecha_end"]:"";

        $cliente = new ModuleLoansEntityPago;
        $records = $cliente->findAll(array(
            array(
                "field" => 'c.nombres like (\'%%%1$s%%\') OR c.apellidos like (\'%%%1$s%%\')',
                "comparacion" => "complex",
                "value" => $searchCliente
            ),
            array(
                "field" => "estado",
                "comparacion" => "in",
                "value" => $searchEstado
            ),
            array(
                "field" => "fecha",
                "comparacion" => ">=",
                "value" => CoreFilter::date($inputFechaStartSearch)
            ),
            array(
                "field" => "fecha",
                "comparacion" => "<=",
                "value" => CoreFilter::date($inputFechaEndSearch)
            ),
            array(
                "field" => "cobrador",
                "comparacion" => "in",
                "value" => $searchCobrador
            )
        ), $data["pagina"], $data["paginas"]);
        return $records;
    }

    public static function save () {

        $currentUser = CoreRoute::getUserLogged();

        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        if(empty($id)) {
            $pago = new ModuleLoansEntityPago;
            $pago->isNew = true;
        } else {
            $pago = new ModuleLoansEntityPago($id);
        }
        $time = time();

        $pago->cliente = $_POST["cliente"];
        $pago->cobrador = $_POST["cobrador"];
        $pago->prestamo = $_POST["prestamo"];
        $pago->metodo = $_POST["metodo"];
        $pago->fecha = $_POST["fecha"];
        $pago->monto = @$_POST["monto"];
        $pago->observaciones = $_POST["observaciones"];
        $pago->estado = $_POST["estado"];
        if(empty($id)) {
            $pago->creado_por = $currentUser->getId();
            $pago->fecha_creacion = $time;
        } else {
            $pago->modificado_por = $currentUser->getId();
            $pago->fecha_modificacion = $time;
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el pago."),
            "error" => true
        );
        $validateForm = self::validateForm($pago);
        if (!$validateForm[0]) {
            $error["msg"] = _("Hay campos vacios.");
            $error["campos"] = $validateForm[1];
        } else {
            $rowChanges = $pago->save();
            $errorno = $pago->getDbInstance()->errornost();
            if ($rowChanges) {
                $error["msg"] = _("Se ha guardado el pago exitosamente.");
                $error["error"] = false;
                $error["id"] = (empty($id))?$pago->getDbInstance()->getInsertId():$id;
                $error["new"] = (empty($id))?true:false;
                //Almacenando Items
                if(empty($id)) {
                    $error["items"] = array();
                    $items = $_POST["items"];
                    $montos = $_POST["montos"];
                    $total = sizeof($items);
                    for($i = 0; $i < $total; $i++) {
                        $cuota = new ModuleLoansEntityPagoItems;
                        $cuota->monto = $montos[$i];
                        $cuota->origen = $_POST['origen'];
                        $cuota->documento = $items[$i];
                        $cuota->pago = $rowChanges;
                        $cuota->isNew = true;
                        $cuota->save();
                        $id = $cuota->getDbInstance()->getInsertId();
                        $error["items"][] = array(
                            "documento" => $items[$i],
                            "id" => $id
                        );
                    }
                }

            } else if($errorno == 1062){
                $error["msg"] = _("Pago duplicado.");
                $error["code_error"] = $pago->getDbInstance()->errornost();
                $error["code_error_msg"] = $pago->getDbInstance()->getLastError();
            } else if($errorno == 1452){
                $error["msg"] = _("El cliente asignado no existe.");
                $error["code_error"] = $pago->getDbInstance()->errornost();
                $error["code_error_msg"] = $pago->getDbInstance()->getLastError();
            } else {
                $error["msg"] = _("No hubo cambios en el registro.");
            }
        }

        return $error;

    }

    public static function rechazar () {
        $currentUser = CoreRoute::getUserLogged();
        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        $error = array(
            "msg" => _("Ocurrio un error mientras se rechazaba el pago."),
            "error" => true
        );
        if(!empty($id)) {
            $pago = new ModuleLoansEntityPago($id);
            if($pago->estado == 0 || $pago->estado == 3) {
                $time = time();
                $pago->estado = 2;
                $pago->modificado_por = $currentUser->getId();
                $pago->fecha_modificacion = $time;
                $rowChanges = $pago->save();
                if ($rowChanges) {
                    if ($pago->getDbInstance()->count == 0) {
                        $error["msg"] = _("No hubo cambios en el registro.");
                    } else {
                        $error["msg"] = _("Se ha rechazado el pago exitosamente.");
                        $error["error"] = false;
                    }
                }
            } else {
                $config = CoreConfig::getConfig();
                $error["msg"] = _("El estado no es valido para rechazar. El estado actual es:" . $config["estado_pago"][$pago->estado] );
            }
        }
        return $error;
    }

    public static function aprobar () {
        $currentUser = CoreRoute::getUserLogged();
        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        $error = array(
            "msg" => _("Ocurrio un error mientras se aprobaba el pago."),
            "error" => true
        );
        if(!empty($id)) {
            $pago = new ModuleLoansEntityPago($id);
            if($pago->estado == 0 || $pago->estado == 3) {
                $time = time();
                $pago->estado = 1;
                $pago->modificado_por = $currentUser->getId();
                $pago->fecha_modificacion = $time;
                $rowChanges = $pago->save();
                if ($rowChanges) {
                    if ($pago->getDbInstance()->count == 0) {
                        $error["msg"] = _("No hubo cambios en el registro.");
                    } else {
                        $error["msg"] = _("Se ha aprobado el pago exitosamente.");
                        $error["error"] = false;
                        $prestamo = $pago->prestamo;
                        $prestamo->saldo = $prestamo->monto + $prestamo->monto_interes - $pago->monto;
                        if($prestamo->saldo == 0) {
                            $prestamo->estado = 3;
                        }
                        $prestamo->save();
                        $cuotas = $pago->items;
                        $fecha_actual = strtotime($pago->fecha);
                        $pay_in_time = $pay_out_time = $pay_after_time = $pays = 0;
                        if(sizeof($cuotas) > 0) {
                            $pays = sizeof($cuotas);
                            foreach($cuotas as $value) {
                                $cuota = new ModuleLoansEntityCuota($value->documento);
                                $fecha_cuota = strtotime($cuota->fecha);
                                if($fecha_cuota == $fecha_actual) {
                                    $pay_in_time++;
                                } else if ($fecha_cuota < $fecha_actual) {
                                    $pay_out_time++;
                                    $pays--;
                                } else if ($fecha_cuota > $fecha_actual) {
                                    $pay_after_time++;
                                }
                                $value->estado = 1;
                                $value->save();
                            }
                            ModuleLoansHelpersCliente::estadistica($pago->cliente->id, $pay_in_time, $pay_out_time, $pay_after_time, 0, $pays);
                        }
                    }
                }
            } else {
                $config = CoreConfig::getConfig();
                $error["msg"] = _("El estado no es valido para rechazar. El estado actual es: " . $config->getData()["estado_pago"][$pago->estado] );
            }
        }
        return $error;
    }

    public static function reversar () {
        $currentUser = CoreRoute::getUserLogged();
        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        $error = array(
            "msg" => _("Ocurrio un error mientras se reversaba el pago."),
            "error" => true
        );
        if(!empty($id)) {
            $pago = new ModuleLoansEntityPago($id);
            if($pago->estado == 1) {
                $time = time();
                $pago->estado = 4;
                $pago->modificado_por = $currentUser->getId();
                $pago->fecha_modificacion = $time;
                $rowChanges = $pago->save();
                if ($rowChanges) {
                    if ($pago->getDbInstance()->count == 0) {
                        $error["msg"] = _("No hubo cambios en el registro.");
                    } else {
                        $error["msg"] = _("Se ha reversado el pago exitosamente.");
                        $error["error"] = false;
                        $prestamo = $pago->prestamo;
                        $prestamo->saldo = $prestamo->saldo + $pago->monto;
                        if($prestamo->estado == 3) {
                            $prestamo->estado = 1;
                        }
                        $prestamo->save();
                        $cuotas = $pago->items;
                        $fecha_actual = strtotime($pago->fecha);
                        $pay_in_time = $pay_out_time = $pay_after_time = $pays = 0;
                        if(sizeof($cuotas) > 0) {
                            $pays = sizeof($cuotas);
                            foreach($cuotas as $value) {
                                $cuota = new ModuleLoansEntityCuota($value->documento);
                                $fecha_cuota = strtotime($cuota->fecha);
                                if($fecha_cuota == $fecha_actual) {
                                    $pay_in_time++;
                                } else if ($fecha_cuota < $fecha_actual) {
                                    $pay_out_time++;
                                    $pays--;
                                } else if ($fecha_cuota > $fecha_actual) {
                                    $pay_after_time++;
                                }
                                $value->estado = 0;
                                $value->save();
                            }
                            $pays = -1 * $pays;
                            if($pay_in_time > 0) {
                                $pay_in_time = -1 * $pay_in_time;
                            }
                            if($pay_out_time > 0) {
                                $pay_out_time = -1 * $pay_out_time;
                            }
                            if($pay_after_time > 0) {
                                $pay_after_time = -1 * $pay_after_time;
                            }
                            ModuleLoansHelpersCliente::estadistica($pago->cliente->id, $pay_in_time, $pay_out_time, $pay_after_time, 0, $pays);
                        }
                    }
                }
            } else {
                $config = CoreConfig::getConfig();
                $error["msg"] = _("El estado no es valido para rechazar. El estado actual es: " . $config->getData()["estado_pago"][$pago->estado] );
            }
        }
        return $error;
    }

    public static function validateForm (ModuleLoansEntityPago $pago) {
        $isValid = true;
        $campos = array();
        if($pago->getMonto() == "") {
            $isValid = false;
            $campos[] = "monto";
        }
        if($pago->getFecha() == "") {
            $isValid = false;
            $campos[] = "fecha";
        }
        if($pago->getPrestamo() == "") {
            $isValid = false;
            $campos[] = "saldo";
        }
        if($pago->getCliente() == "") {
            $isValid = false;
            $campos[] = "cliente";
        }
        if($pago->getCobrador() == "") {
            $isValid = false;
            $campos[] = "cobrador";
        }
        if($pago->getMetodo() == "") {
            $isValid = false;
            $campos[] = "saldo";
        }
        if($pago->getEstado() == "") {
            $isValid = false;
            $campos[] = "estado";
        }
        if(empty($pago->getId())) {
            if (!isset($_POST["items"])) {
                $isValid = false;
                $campos[] = "items";
            } else if (!is_array($_POST["items"])) {
                $isValid = false;
                $campos[] = "items";
            } else if (sizeof($_POST["items"]) == 0) {
                $isValid = false;
                $campos[] = "items";
            }
            if (!isset($_POST["montos"])) {
                $isValid = false;
                $campos[] = "montos";
            } else if (!is_array($_POST["montos"])) {
                $isValid = false;
                $campos[] = "montos";
            } else if (sizeof($_POST["montos"]) == 0) {
                $isValid = false;
                $campos[] = "montos";
            }
        }
        return array($isValid, $campos);
    }

    public static function delete () {

        $id = $_POST["id"];
        $error = array(
            "msg" => _("Ocurrio un error al eliminar el pago."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $pago = new ModuleLoansEntityPago($id);
            if($pago->delete()) {
                $error["msg"] = _("Se elimino el registro exitosamente.");
                $error["error"] = false;
            } else {
                if($pago->getDbInstance()->errornost() == "1451") {
                    $error["msg"] = _("El pago tiene detalle asociados por lo que no puede ser borrado.");
                }
                $error["code_error"] = $pago->getDbInstance()->errornost();
            }
        }
        return $error;
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

}