<?php
//===============================================
// Debug
//===============================================
ini_set('display_errors','On');
error_reporting(E_ALL);
session_start();
//===============================================
// mod_rewrite
//===============================================
//Please configure via .htaccess or httpd.conf

require_once "constants.php";

spl_autoload_register(function($class_name) {
    $ruta = APP;
    if(preg_match("/^MODULE(.*)(CONTROLLERS|HELPERS|MODELS|VIEWS|ENTITY)(.*)/i", $class_name, $output)) {
        $ruta.= "module/" . strtolower($output[1]) . "/" . strtolower($output[2]) . "/" . $output[0] . ".php";
    } else if (preg_match("/^(CONTROLLERS|HELPERS|MODELS|VIEWS|CORE|ENTITY)(.*)/i", $class_name, $output)) {
        $ruta.= strtolower($output[1]) . "/" . $output[0] . ".php";
    } else if(preg_match("/^MODULE(.*)(CONFIG)$/i", $class_name, $output)) {
        $ruta.= "module/" . strtolower($output[1]) . "/" . $output[0] . ".php";
    }
    if($ruta != APP){
        require_once $ruta;
    }
});

require_once LIBS . 'vendor/autoload.php';

// Set language to French
putenv('LC_ALL=es_ES');
setlocale(LC_ALL, 'es_ES');
// Specify the location of the translation tables
bindtextdomain(TEXDOMAINS, DOCUMENT_ROOT . 'includes/locale');
bind_textdomain_codeset(TEXDOMAINS, 'UTF-8');
// Choose domain
textdomain(TEXDOMAINS);

$loader = CoreRoute::paths();

$twig = new Twig_Environment($loader, array(
    //'cache' => CACHE,
    'debug' => true
));

CoreRoute::validate();

CoreRoute::ini($twig);