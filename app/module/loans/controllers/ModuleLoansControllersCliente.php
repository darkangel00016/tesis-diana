<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:03
 */

class ModuleLoansControllersCliente extends CoreControllers {

    public $urlBase;
    
    function __construct(Twig_Environment $twig)
    {
        parent::__construct($twig);
        $this->urlBase = ModuleLoansConfig::$urlCliente;
    }

    public function index () {
        $respuesta = $this->_listado();
        echo $this->twig->render('@'.ModuleLoansConfig::getCliente().'/main.html.twig', array(
            "title" => _("Listado de clientes"),
            "menu" => $this->menu(),
            "pagination" => $respuesta[0],
            "pagina" => $this->getPagina(),
            "url" => $this->urlBase,
            "breadcrumb" => $this->breadcrumb(array(
                "listado"
            )),
            "query_string" => $respuesta[1]
        ));
    }

    public function listado () {
        $respuesta = $this->_listado();
        echo $this->twig->render('@'.ModuleLoansConfig::getCliente().'/listado.html.twig', array(
            "pagination" => $respuesta[0],
            "pagina" => $this->getPagina(),
            "tipo" => "normal",
            "query_string" => $respuesta[1]
        ));
    }

    public function listadoSearch () {
        $respuesta = $this->_listado();
        echo $this->twig->render('@'.ModuleLoansConfig::getCliente().'/listado.html.twig', array(
            "pagination" => $respuesta[0],
            "pagina" => $this->getPagina(),
            "tipo" => "search",
            "query_string" => $respuesta[1]
        ));
    }

    private function _listado () {

        $searchNombres = (isset($_GET["nombres"]) && !empty($_GET["nombres"]))?$_GET["nombres"]:"";
        $searchApellidos = (isset($_GET["apellidos"]) && !empty($_GET["apellidos"]))?$_GET["apellidos"]:"";
        $searchTelefono = (isset($_GET["telefono"]) && !empty($_GET["telefono"]))?$_GET["telefono"]:"";
        $searchDireccion = (isset($_GET["direccion"]) && !empty($_GET["direccion"]))?$_GET["direccion"]:"";
        $searchBarrio = (isset($_GET["barrio"]) && !empty($_GET["barrio"]))?$_GET["barrio"]:"";
        $searchCiudad = (isset($_GET["ciudad"]) && !empty($_GET["ciudad"]))?$_GET["ciudad"]:"";
        $searchObservaciones = (isset($_GET["observaciones"]) && !empty($_GET["observaciones"]))?$_GET["observaciones"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $searchZona = (isset($_GET["zona"]) && !empty($_GET["zona"]))?$_GET["zona"]:array();
        $searchRate = (isset($_GET["rate"]) && !empty($_GET["rate"]))?$_GET["rate"]:array();

        $query_string = "nombres={$searchNombres}&apellidos={$searchApellidos}&telefono={$searchTelefono}&direccion={$searchDireccion}&barrio={$searchBarrio}&ciudad={$searchCiudad}&observaciones={$searchObservaciones}";
        foreach($searchEstado as $value) {
            $query_string.="&estado[]={$value}";
        }
        foreach($searchZona as $value) {
            $query_string.="&zona[]={$value}";
        }
        foreach($searchRate as $value) {
            $query_string.="&rate[]={$value}";
        }

        $records = ModuleLoansHelpersCliente::listado(array(
            "nombres" => $searchNombres,
            "apellidos" => $searchApellidos,
            "telefono" => $searchTelefono,
            "direccion" => $searchDireccion,
            "barrio" => $searchBarrio,
            "ciudad" => $searchCiudad,
            "observaciones" => $searchObservaciones,
            "estado" => $searchEstado,
            "zona" => $searchZona,
            "rate" => $searchRate,
            "pagina" => $this->getPagina(),
            "paginas" => PAGINATION_PAGES
        ));
        $pagination = new CorePagination(
            $records["records"],
            PAGINATION_PAGES,
            CoreRoute::getUrl($this->urlBase),
            $this->getPagina(),
            $records["total"]
        );
        return array($pagination, $query_string);
    }

    public function add ()
    {
        echo $this->twig->render('@'.ModuleLoansConfig::getCliente().'/add.html.twig', array(
            "title" => _("Agregar cliente"),
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
            $record = new ModuleLoansEntityCliente($usr);
        }
        echo $this->twig->render('@'.ModuleLoansConfig::getCliente().'/add.html.twig', array(
            "title" => _("Editar cliente"),
            "menu" => $this->menu(),
            "record" => $record,
            "breadcrumb" => $this->breadcrumb(array(
                "listado",
                "edit"
            ))
        ));
    }

    public function save () {
        echo json_encode(ModuleLoansHelpersCliente::save());
    }

    public function delete () {
        echo json_encode(ModuleLoansHelpersCliente::delete());
    }

    public function export() {
        $usr = $_GET["id"];
        $record = null;
        if(!empty($usr)) {
            $record = new ModuleLoansEntityCliente($usr);
            $pdf = new CorePdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetTitle('Cliente: ' . $record->id);

            // set default header data
            $pdf->SetHeaderData(IMAGES . "ic_icono.png", 25, "Cliente", "");

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set font
            $pdf->SetFont('times', 'BI', 12);

            $pdf->AddPage();

            $pdf->WriteHTML($this->twig->render('@'.ModuleLoansConfig::getCliente().'/single.pdf.twig', array(
                "record" => $record,
                "title" => 'Cliente: ' . $record->id
            )), true, 0, false, 0);

            //Close and output PDF document
            $pdf->Output('cliente.pdf', 'I');

        }
    }

    public function exports() {
        $pdf = new CorePdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Listado de clientes');

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('times', 'BI', 12);

        $pdf->AddPage();

        $searchNombres = (isset($_GET["nombres"]) && !empty($_GET["nombres"]))?$_GET["nombres"]:"";
        $searchApellidos = (isset($_GET["apellidos"]) && !empty($_GET["apellidos"]))?$_GET["apellidos"]:"";
        $searchTelefono = (isset($_GET["telefono"]) && !empty($_GET["telefono"]))?$_GET["telefono"]:"";
        $searchDireccion = (isset($_GET["direccion"]) && !empty($_GET["direccion"]))?$_GET["direccion"]:"";
        $searchBarrio = (isset($_GET["barrio"]) && !empty($_GET["barrio"]))?$_GET["barrio"]:"";
        $searchCiudad = (isset($_GET["ciudad"]) && !empty($_GET["ciudad"]))?$_GET["ciudad"]:"";
        $searchObservaciones = (isset($_GET["observaciones"]) && !empty($_GET["observaciones"]))?$_GET["observaciones"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $searchZona = (isset($_GET["zona"]) && !empty($_GET["zona"]))?$_GET["zona"]:array();
        $searchRate = (isset($_GET["rate"]) && !empty($_GET["rate"]))?$_GET["rate"]:array();

        $records = ModuleLoansHelpersCliente::listado(array(
            "nombres" => $searchNombres,
            "apellidos" => $searchApellidos,
            "telefono" => $searchTelefono,
            "direccion" => $searchDireccion,
            "barrio" => $searchBarrio,
            "ciudad" => $searchCiudad,
            "observaciones" => $searchObservaciones,
            "estado" => $searchEstado,
            "zona" => $searchZona,
            "rate" => $searchRate,
            "pagina" => 0,
            "paginas" => 0
        ));

        $pagination = new CorePagination(
            $records["records"],
            PAGINATION_PAGES,
            CoreRoute::getUrl($this->urlBase),
            $this->getPagina(),
            $records["total"]
        );

        $pdf->WriteHTML($this->twig->render('@'.ModuleLoansConfig::getCliente().'/listado.pdf.twig', array(
            "pagination" => $pagination,
            "title" => "Listado de clientes"
        )), true);

        //Close and output PDF document
        $pdf->Output('prestamos.pdf', 'I');
    }

    private function breadcrumb ($breadcrumb) {
        $lista = array();
        foreach($breadcrumb as $value) {
            switch ($value) {
                case "listado":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase),
                        "text" => _("Clientes")
                    );
                    break;
                case "add":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase . "&a=Add"),
                        "text" => _("Agregar cliente")
                    );
                    break;
                case "edit":
                    $id = $_GET["id"];
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase . "&a=Edit&id=" . $id),
                        "text" => _("Editar cliente")
                    );
                    break;
            }
        }
        return $lista;
    }

} 