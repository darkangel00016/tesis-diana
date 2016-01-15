<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:03
 */

class ControllersMain extends CoreControllers {

    public function index () {

        $db = CoreDb::getInstance();
        $cliente = $db->query("select count(id) as total from clientes");
        $prestamo = $db->query("select count(id) as total from prestamo");
        $cobro = $db->query("select count(id) as total from pago");
        $result = $db->query("select p.id, c.id as cuota, c.estado as estado_cuota, (c.monto + c.interes) as monto_cuota from prestamo p inner join cuotas c on c.prestamo = p.id where c.fecha = '".date("Y-m-d")."'");

        echo $this->twig->render('main.index.html.twig', array(
            "title" => "Inicio",
            "menu" => $this->menu(),
            "balance_dia" => $result,
            "clientes" => $cliente[0]["total"],
            "prestamos" => $prestamo[0]["total"],
            "cobros" => $cobro[0]["total"]
        ));
    }

} 