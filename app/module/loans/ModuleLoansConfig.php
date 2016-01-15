<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 13/12/15
 * Time: 10:50 AM
 */
class ModuleLoansConfig
{
    public static $base = "ModuleLoans";
    public static $pago = "Pago";
    public static $prestamo = "Prestamo";
    public static $cliente = "Cliente";

    public static $urlCliente = "index.php?m=Loans&c=Cliente";
    public static $urlPago = "index.php?m=Loans&c=Pago";
    public static $urlPrestamo = "index.php?m=Loans&c=Prestamo";

    public static function twig(Twig_Loader_Filesystem &$loader) {
        $loader->addPath(TEMPLATES . "module/loans/", self::$base);
        $loader->addPath(TEMPLATES . "module/loans/pago", self::$base . self::$pago);
        $loader->addPath(TEMPLATES . "module/loans/prestamo", self::$base . self::$prestamo);
        $loader->addPath(TEMPLATES . "module/loans/cliente", self::$base . self::$cliente);
    }

    public static function getPago() {
        return self::$base . self::$pago;
    }

    public static function getPrestamo() {
        return self::$base . self::$prestamo;
    }

    public static function getCliente() {
        return self::$base . self::$cliente;
    }

    public static function getUrls(array &$container) {
        $container["cliente"] = self::$urlCliente;
        $container["pago"] = self::$urlPago;
        $container["prestamo"] = self::$urlPrestamo;
    }

}