<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:37 PM
 */
class ModuleLoansHelpersCliente
{
    public static function listado( $data ) {

        $searchNombres = (isset($data["nombres"]) && !empty($data["nombres"]))?$data["nombres"]:"";
        $searchApellidos = (isset($data["apellidos"]) && !empty($data["apellidos"]))?$data["apellidos"]:"";
        $searchTelefono = (isset($data["telefono"]) && !empty($data["telefono"]))?$data["telefono"]:"";
        $searchDireccion = (isset($data["direccion"]) && !empty($data["direccion"]))?$data["direccion"]:"";
        $searchBarrio = (isset($data["barrio"]) && !empty($data["barrio"]))?$data["barrio"]:"";
        $searchCiudad = (isset($data["ciudad"]) && !empty($data["ciudad"]))?$data["ciudad"]:"";
        $searchObservaciones = (isset($data["observaciones"]) && !empty($data["observaciones"]))?$data["observaciones"]:"";
        $searchEstado = (isset($data["estado"]) && !empty($data["estado"]))?$data["estado"]:array();
        $searchZona = (isset($data["zona"]) && !empty($data["zona"]))?$data["zona"]:array();
        $searchRate = (isset($data["rate"]) && !empty($data["rate"]))?$data["rate"]:array();

        $cliente = new ModuleLoansEntityCliente;
        $records = $cliente->findAll(array(
            array(
                "field" => "nombres",
                "comparacion" => "like",
                "value" => $searchNombres
            ),
            array(
                "field" => "apellidos",
                "comparacion" => "like",
                "value" => $searchApellidos
            ),
            array(
                "field" => "telefono",
                "comparacion" => "like",
                "value" => $searchTelefono
            ),
            array(
                "field" => "direccion",
                "comparacion" => "like",
                "value" => $searchDireccion
            ),
            array(
                "field" => "barrio",
                "comparacion" => "like",
                "value" => $searchBarrio
            ),
            array(
                "field" => "ciudad",
                "comparacion" => "like",
                "value" => $searchCiudad
            ),
            array(
                "field" => "observaciones",
                "comparacion" => "like",
                "value" => $searchObservaciones
            ),
            array(
                "field" => "c.estado",
                "comparacion" => "in",
                "value" => $searchEstado
            ),
            array(
                "field" => "c.zona",
                "comparacion" => "in",
                "value" => $searchZona
            ),
            array(
                "field" => "rate",
                "comparacion" => "in",
                "value" => $searchRate
            )
        ), $data["pagina"], $data["paginas"]);
        return $records;
    }

    public static function save () {

        $currentUser = CoreRoute::getUserLogged();

        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        if(empty($id)) {
            $cliente = new ModuleLoansEntityCliente;
            $cliente->isNew = true;
        } else {
            $cliente = new ModuleLoansEntityCliente($id);
        }
        $time = time();
        $cliente->nombres = $_POST["nombres"];
        $cliente->apellidos = $_POST["apellidos"];
        $cliente->telefono = $_POST["telefono"];
        $cliente->direccion = $_POST["direccion"];
        $cliente->barrio = $_POST["barrio"];
        $cliente->ciudad = $_POST["ciudad"];
        $cliente->estado = $_POST["estado"];
        $cliente->zona = $_POST["zona"];
        if(empty($id)) {
            $cliente->creado_por = $currentUser->getId();
            $cliente->fecha_creacion = $time;
        } else {
            $cliente->modificado_por = $currentUser->getId();
            $cliente->fecha_modificacion = $time;
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el cliente."),
            "error" => true
        );
        $cliente->observaciones = $_POST["observaciones"];
        if ($cliente->getNombres() == "" || $cliente->getApellidos() == "" || $cliente->getTelefono() == "" || $cliente->getDireccion() == "" || $cliente->getBarrio() == "") {
            $error["msg"] = _("Hay campos vacios.");
        } else {
            $rowChanges = $cliente->save();
            $errorno = $cliente->getDbInstance()->errornost();
            $id_cliente = (empty($id)) ? $cliente->getDbInstance()->getInsertId() : $id;
            if ($rowChanges) {
                $error["msg"] = _("Se ha guardado el cliente exitosamente.");
                $error["error"] = false;
                $error["id"] = $id_cliente;
                if(isset($_FILES) && sizeof($_FILES) > 0) {
                    $types = array(
                        "image/jpg",
                        "image/jpeg"
                    );
                    if(in_array($_FILES["foto"]["type"], $types) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK && $_FILES["foto"]["size"] > 0) {
                        copy($_FILES["foto"]["tmp_name"], CACHE . "/" . $id_cliente . ".jpg");
                        $cliente->foto = $id_cliente . ".jpg";
                        $cliente->id = $id_cliente;
                        $cliente->update();
                    } else {
                        $error["msg"] = _("El tipo de archivo no es permitido.");
                    }
                }
            } else {
                if ($errorno == 1062) {
                    $error["msg"] = _("Cliente duplicado.");
                } else {
                    $error["msg"] = _("No hubo cambios en el registro.");
                }
                $error["code_error"] = $errorno;
            }
        }
        return $error;

    }

    public static function delete () {

        $id = $_POST["id"];
        $error = array(
            "msg" => _("Ocurrio un error al eliminar el cliente."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $cliente = new ModuleLoansEntityCliente($id);
            if($cliente->delete()) {
                $error["msg"] = _("Se elimino el registro exitosamente.");
                $error["error"] = false;
            }
        }
        return $error;
    }

    public static function estadistica($cliente, $pay_in_time, $pay_out_time, $pay_before_time, $no_pay, $pays) {
        if(!empty($cliente) && ($pay_in_time != 0 || $pay_out_time != 0 || $pay_before_time != 0 || $no_pay != 0)) {
            $_cliente = new ModuleLoansEntityCliente($cliente);
            $_pay_in_time = $_cliente->pay_in_time;
            $_pay_out_time = $_cliente->pay_out_time;
            $_pay_before_time = $_cliente->pay_before_time;
            $_no_pay = $_cliente->no_pay;
            $_pays = $_cliente->pays;
            $_pay_in_time+= $pay_in_time;
            $_pay_out_time+= $pay_out_time;
            $_pay_before_time+= $pay_before_time;
            $_no_pay+= $no_pay;
            $_pays+= $pays;
            if($_cliente->id > 0 && $_pays > 0) {
                $percent_in_time = $_pay_in_time * 100 / $_pays;
                $percent_out_time = $_pay_out_time * 100 / $_pays;
                $percent_before_time = $_pay_before_time * 100 / $_pays;
                $percent_no_pay = 0;
                if(($_no_pay - $_pay_out_time) > 0) {
                    $percent_no_pay = ($_no_pay - $_pay_out_time) * 100 / $_pays;
                }
                $positive_percent = $percent_in_time + $percent_out_time + $percent_before_time;
                $rate = 0;
                if($percent_no_pay > 50) {
                    $rate = 1;
                } else if ($positive_percent > 50 && $positive_percent < 90) {
                    $rate = 2;
                } else if ($positive_percent > 90 && $positive_percent < 99) {
                    $rate = 3;
                } else if ($positive_percent == 100) {
                    $rate = 4;
                }
                $_cliente->pay_in_time = $_pay_in_time;
                $_cliente->pay_out_time = $_pay_out_time;
                $_cliente->pay_before_time = $_pay_before_time;
                $_cliente->no_pay = $_no_pay;
                $_cliente->pays = $_pays;
                $_cliente->rate = $rate;
            } else {
                $_cliente->pay_in_time = $_pay_in_time;
                $_cliente->pay_out_time = $_pay_out_time;
                $_cliente->pay_before_time = $_pay_before_time;
                $_cliente->no_pay = $_no_pay;
                $_cliente->pays = $_pays;
                $_cliente->rate = 0;
            }
            $_cliente->save();
            $_cliente->getDbInstance()->errornost();
        }
    }

}