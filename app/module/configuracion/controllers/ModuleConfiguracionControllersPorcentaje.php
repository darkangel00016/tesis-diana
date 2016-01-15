<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:03
 */

class ModuleConfiguracionControllersPorcentaje extends CoreControllers {

    public $urlBase;

    function __construct(Twig_Environment $twig)
    {
        parent::__construct($twig);
        $this->urlBase = ModuleConfiguracionConfig::$urlPorcentaje;
    }
    
    public function index () {
        echo $this->twig->render('@'.ModuleConfiguracionConfig::getPorcentaje().'/main.html.twig', array(
            "title" => _("Listado de Porcentajes"),
            "menu" => $this->menu(),
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina(),
            "url" => $this->urlBase,
            "breadcrumb" => $this->breadcrumb(array(
                "listado"
            ))
        ));
    }

    public function listado () {
        echo $this->twig->render('@'.ModuleConfiguracionConfig::getPorcentaje().'/listado.html.twig', array(
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina(),
            "tipo" => "normal"
        ));
    }

    public function listadoSearch () {
        echo $this->twig->render('@'.ModuleConfiguracionConfig::getPorcentaje().'/listado.html.twig', array(
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina(),
            "tipo" => "search"
        ));
    }

    private function _listado () {

        $searchPorcentaje = (isset($_GET["porcentaje"]) && !empty($_GET["porcentaje"]))?$_GET["porcentaje"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();

        $records = ModuleConfiguracionHelpersPorcentaje::listado(array(
            "porcentaje" => $searchPorcentaje,
            "estado" => $searchEstado,
            "pagina" => $this->getPagina(),
            "paginas" => PAGINATION_PAGES,
        ));

        $pagination = new CorePagination(
            $records["records"],
            PAGINATION_PAGES,
            CoreRoute::getUrl($this->urlBase),
            $this->getPagina(),
            $records["total"]
        );
        return $pagination;
    }

    public function add ()
    {
        echo $this->twig->render('@'.ModuleConfiguracionConfig::getPorcentaje().'/add.html.twig', array(
            "title" => _("Agregar porcentaje"),
            "menu" => $this->menu(),
            "breadcrumb" => $this->breadcrumb(array(
                "listado",
                "add"
            ))
        ));
    }

    public function edit ()
    {

        $usr = $_GET["id"];
        $record = null;
        if(!empty($usr)) {
            $record = new ModuleConfiguracionEntityPorcentaje($usr);
        }
        echo $this->twig->render('@'.ModuleConfiguracionConfig::getPorcentaje().'/add.html.twig', array(
            "title" => _("Editar porcentaje"),
            "menu" => $this->menu(),
            "record" => $record,
            "breadcrumb" => $this->breadcrumb(array(
                "listado",
                "edit"
            ))
        ));
    }

    public function save () {

        echo json_encode(ModuleConfiguracionHelpersPorcentaje::save());

    }

    public function delete () {
        echo json_encode(ModuleConfiguracionHelpersPorcentaje::delete());
    }

    private function breadcrumb ($breadcrumb) {
        $lista = array();
        foreach($breadcrumb as $value) {
            switch ($value) {
                case "listado":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase),
                        "text" => _("Porcentajes")
                    );
                    break;
                case "add":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase . "&a=Add"),
                        "text" => _("Agregar porcentaje")
                    );
                    break;
                case "edit":
                    $id = $_GET["id"];
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase . "&a=Edit&id=" . $id),
                        "text" => _("Editar porcentaje")
                    );
                    break;
            }
        }
        return $lista;
    }

} 