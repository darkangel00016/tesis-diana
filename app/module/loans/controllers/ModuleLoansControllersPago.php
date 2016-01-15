<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:03
 */

class ModuleLoansControllersPago extends CoreControllers {

    public $urlBase;

    function __construct(Twig_Environment $twig)
    {
        parent::__construct($twig);
        $this->urlBase = ModuleLoansConfig::$urlPago;
    }
    
    public function index () {
        $usuario = new ModuleUserEntityUsuario;
        $usuarios = $usuario->findAll(array(), 0, 0);
        $respuesta = $this->_listado();
        echo $this->twig->render('@'.ModuleLoansConfig::getPago().'/main.html.twig', array(
            "title" => _("Listado de pagos"),
            "menu" => $this->menu(),
            "pagination" => $respuesta[0],
            "pagina" => $this->getPagina(),
            "url" => $this->urlBase,
            "breadcrumb" => $this->breadcrumb(array(
                "listado"
            )),
            "usuarios" => $usuarios["records"],
            "query_string" => $respuesta[1]
        ));
    }

    public function listado () {
        $respuesta = $this->_listado();
        echo $this->twig->render('@'.ModuleLoansConfig::getPago().'/listado.html.twig', array(
            "pagination" => $respuesta[0],
            "pagina" => $this->getPagina(),
            "query_string" => $respuesta[1]
        ));
    }

    private function _listado () {

        $searchCliente = (isset($_GET["cliente"]) && !empty($_GET["cliente"]))?$_GET["cliente"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $searchCobrador = (isset($_GET["cobrador"]) && !empty($_GET["cobrador"]))?$_GET["cobrador"]:array();
        $inputFechaStartSearch = (isset($_GET["fecha_start"]) && !empty($_GET["fecha_start"]))?$_GET["fecha_start"]:"";
        $inputFechaEndSearch = (isset($_GET["fecha_end"]) && !empty($_GET["fecha_end"]))?$_GET["fecha_end"]:"";

        $query_string = "cliente={$searchCliente}&fecha_start={$inputFechaStartSearch}&fecha_end={$inputFechaEndSearch}";
        foreach($searchEstado as $value) {
            $query_string.="&estado[]={$value}";
        }
        foreach($searchCobrador as $value) {
            $query_string.="&cobrador[]={$value}";
        }

        $records = ModuleLoansHelpersPago::listado(array(
            "cliente" => $searchCliente,
            "cobrador" => $searchCobrador,
            "estado" => $searchEstado,
            "fecha_start" => $inputFechaStartSearch,
            "fecha_end" => $inputFechaEndSearch,
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
        echo $this->twig->render('@'.ModuleLoansConfig::getPago().'/add.html.twig', array(
            "title" => _("Agregar pago"),
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
            $record = new ModuleLoansEntityPago($usr);
        }
        echo $this->twig->render('@'.ModuleLoansConfig::getPago().'/add.html.twig', array(
            "title" => _("Editar pago"),
            "menu" => $this->menu(),
            "record" => $record,
            "breadcrumb" => $this->breadcrumb(array(
                "listado",
                "edit"
            ))
        ));
    }

    public function save () {
        echo json_encode(ModuleLoansHelpersPago::save());
    }

    public function delete () {
        echo json_encode(ModuleLoansHelpersPago::delete());
    }

    public function rechazar () {
        echo json_encode(ModuleLoansHelpersPago::rechazar());
    }

    public function aprobar () {
        echo json_encode(ModuleLoansHelpersPago::aprobar());
    }

    public function reversar () {
        echo json_encode(ModuleLoansHelpersPago::reversar());
    }

    public function export() {
        $usr = $_GET["id"];
        $record = null;
        if(!empty($usr)) {
            $record = new ModuleLoansEntityPago($usr);
            $pdf = new CorePdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetTitle('Pago: ' . $record->id);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set font
            $pdf->SetFont('times', 'BI', 12);

            $pdf->AddPage();

            $pdf->WriteHTML($this->twig->render('@'.ModuleLoansConfig::getPago().'/single.pdf.twig', array(
                "record" => $record,
                "title" => "Pago"
            )), true);

            //Close and output PDF document
            $pdf->Output('prestamo.pdf', 'I');

        }
    }

    public function exports() {
        $pdf = new CorePdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('Listado de pagos');

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('times', 'BI', 12);

        $pdf->AddPage();

        $searchCliente = (isset($_GET["cliente"]) && !empty($_GET["cliente"]))?$_GET["cliente"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $searchCobrador = (isset($_GET["cobrador"]) && !empty($_GET["cobrador"]))?$_GET["cobrador"]:array();
        $inputFechaStartSearch = (isset($_GET["fecha_start"]) && !empty($_GET["fecha_start"]))?$_GET["fecha_start"]:"";
        $inputFechaEndSearch = (isset($_GET["fecha_end"]) && !empty($_GET["fecha_end"]))?$_GET["fecha_end"]:"";

        $records = ModuleLoansHelpersPago::listado(array(
            "cliente" => $searchCliente,
            "cobrador" => $searchCobrador,
            "estado" => $searchEstado,
            "fecha_start" => $inputFechaStartSearch,
            "fecha_end" => $inputFechaEndSearch,
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

        $pdf->WriteHTML($this->twig->render('@'.ModuleLoansConfig::getPago().'/listado.pdf.twig', array(
            "pagination" => $pagination,
            "title" => "Listado de pagos"
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
                        "text" => _("Pagos")
                    );
                    break;
                case "add":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase . "&a=Add"),
                        "text" => _("Agregar pago")
                    );
                    break;
                case "edit":
                    $id = $_GET["id"];
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase . "&a=Edit&id=" . $id),
                        "text" => _("Editar pago")
                    );
                    break;
            }
        }
        return $lista;
    }

} 