<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:03
 */
class ModuleConfiguracionControllersMain extends CoreControllers {
    public $urlBase;
    function __construct(Twig_Environment $twig)
    {
        parent::__construct($twig);
        $this->urlBase = ModuleConfiguracionConfig::$urlBase;
    }

    public function index () {
        echo $this->twig->render('@'.ModuleConfiguracionConfig::getMain().'/main.html.twig', array(
            "title" => _("Configuracion General"),
            "menu" => $this->menu(),
            "url" => $this->urlBase,
            "breadcrumb" => $this->breadcrumb(array(
                "main"
            ))
        ));
    }
    public function save () {
        $db = CoreDb::getInstance();
        $campos = array(
            "host",
            "usuario",
            "clave",
            "from",
            "subject",
            "formato_fecha_corta",
            "formato_fecha_larga"
        );
        foreach($campos as $value) {
            $db->query("UPDATE config SET valor = '".@$_POST[$value]."' WHERE etiqueta = '$value'");
        }
        echo json_encode(array(
            "msg" => _("Se guardo con exito."),
            "error" => false
        ));
    }
    private function breadcrumb ($breadcrumb) {
        $lista = array();
        foreach($breadcrumb as $value) {
            switch ($value) {
                case "main":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase),
                        "text" => _("Configuracion General")
                    );
                    break;
            }
        }
        return $lista;
    }
}