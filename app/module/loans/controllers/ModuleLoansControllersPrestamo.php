<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:03
 */

class ModuleLoansControllersPrestamo extends CoreControllers {

    public $urlBase;

    function __construct(Twig_Environment $twig)
    {
        parent::__construct($twig);
        $this->urlBase = ModuleLoansConfig::$urlPrestamo;
    }

    public function index () {
        $usuario = new ModuleUserEntityUsuario;
        $usuarios = $usuario->findAll(array(), 0, 0);
        $respuesta = $this->_listado();
        echo $this->twig->render('@'.ModuleLoansConfig::getPrestamo().'/main.html.twig', array(
            "title" => _("Listado de prestamos"),
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
        echo $this->twig->render('@'.ModuleLoansConfig::getPrestamo().'/listado.html.twig', array(
            "pagination" => $respuesta[0],
            "pagina" => $this->getPagina(),
            "tipo" => "normal",
            "query_string" => $respuesta[1]
        ));
    }

    public function listadoSearch () {
        echo $this->twig->render('@'.ModuleLoansConfig::getPrestamo().'/listado.search.html.twig', array(
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina(),
            "tipo" => "search"
        ));
    }

    public function cuotas () {
        if($this->isAjax()) {
            $id = (isset($_GET["id"]) && !empty($_GET["id"]))?$_GET["id"]:"";
            $error = array(
                "error"=> true,
                "msg"=> _("Ocurrio un error al consultar las cuotas.")
            );
            if(!empty($id)) {
                $cuota = new ModuleLoansEntityCuota;
                $cuotas = $cuota->findAll(
                    array(
                        array("field"=> "prestamo", "comparacion"=>"=", "value"=>$id),
                        array("field"=> "estado", "comparacion"=>"=", "value"=>0)
                    ),
                    0, 0
                );
                if(sizeof($cuotas["records"]) > 0) {
                    $error["msg"] = _("Tiene cuotas pendientes.");
                    $error["cuotas"] = $cuotas["records"];
                    $error["error"] = false;
                } else {
                    $error["msg"] = _("El prestamo no tiene cuotas pendientes.");
                }
            } else {
                $error["msg"] = _("Debe enviar un prestamo valido.");
            }
            echo json_encode($error);
        }
    }

    private function _listado () {

        $searchCliente = (isset($_GET["cliente"]) && !empty($_GET["cliente"]))?$_GET["cliente"]:"";
        $searchTipoIntervalo = (isset($_GET["tipo_intervalo"]) && !empty($_GET["tipo_intervalo"]))?$_GET["tipo_intervalo"]:array();
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $inputUsuarioSearch = (isset($_GET["usuario"]) && !empty($_GET["usuario"]))?$_GET["usuario"]:array();
        $searchIdCliente = (isset($_GET["idcliente"]) && !empty($_GET["idcliente"]))?$_GET["idcliente"]:"";
        $inputFechaInicioStartSearch = (isset($_GET["fecha_inicio_start"]) && !empty($_GET["fecha_inicio_start"]))?$_GET["fecha_inicio_start"]:"";
        $inputFechaInicioEndSearch = (isset($_GET["fecha_inicio_end"]) && !empty($_GET["fecha_inicio_end"]))?$_GET["fecha_inicio_end"]:"";
        $inputFechaFinStartSearch = (isset($_GET["fecha_fin_start"]) && !empty($_GET["fecha_fin_start"]))?$_GET["fecha_fin_start"]:"";
        $inputFechaFinEndSearch = (isset($_GET["fecha_fin_end"]) && !empty($_GET["fecha_fin_end"]))?$_GET["fecha_fin_end"]:"";

        $query_string = "cliente={$searchCliente}&fecha_inicio_start={$inputFechaInicioStartSearch}&fecha_inicio_end={$inputFechaInicioEndSearch}&fecha_fin_start={$inputFechaFinStartSearch}&fecha_fin_end={$inputFechaFinEndSearch}";
        foreach($searchEstado as $value) {
            $query_string.="&estado[]={$value}";
        }
        foreach($inputUsuarioSearch as $value) {
            $query_string.="&usuario[]={$value}";
        }
        foreach($searchTipoIntervalo as $value) {
            $query_string.="&tipo_intervalo[]={$value}";
        }

        $records = ModuleLoansHelpersPrestamo::listado(array(
            "cliente" => $searchCliente,
            "tipo_intervalo" => $searchTipoIntervalo,
            "idcliente" => $searchIdCliente,
            "estado" => $searchEstado,
            "usuarios" => $inputUsuarioSearch,
            "fecha_inicio_start" => $inputFechaInicioStartSearch,
            "fecha_inicio_end" => $inputFechaInicioEndSearch,
            "fecha_fin_start" => $inputFechaFinStartSearch,
            "fecha_fin_end" => $inputFechaFinEndSearch,
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
        echo $this->twig->render('@'.ModuleLoansConfig::getPrestamo().'/add.html.twig', array(
            "title" => _("Agregar prestamo"),
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
            $record = new ModuleLoansEntityPrestamo($usr);
        }
        echo $this->twig->render('@'.ModuleLoansConfig::getPrestamo().'/add.html.twig', array(
            "title" => _("Editar prestamo"),
            "menu" => $this->menu(),
            "record" => $record,
            "breadcrumb" => $this->breadcrumb(array(
                "listado",
                "edit"
            ))
        ));
    }

    public function save () {
        echo json_encode(ModuleLoansHelpersPrestamo::save());
    }

    public function delete () {
        echo json_encode(ModuleLoansHelpersPrestamo::delete());
    }

    public function export() {
        $usr = $_GET["id"];
        $record = null;
        if(!empty($usr)) {
            $record = new ModuleLoansEntityPrestamo($usr);
            $pdf = new CorePdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetTitle('Cliente: ' . $record->id);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set font
            $pdf->SetFont('times', 'BI', 12);

            $pdf->AddPage();

            $pdf->WriteHTML($this->twig->render('@'.ModuleLoansConfig::getPrestamo().'/single.pdf.twig', array(
                "record" => $record,
                "title" => "Prestamo"
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
        $searchTipoIntervalo = (isset($_GET["tipo_intervalo"]) && !empty($_GET["tipo_intervalo"]))?$_GET["tipo_intervalo"]:array();
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $searchIdCliente = (isset($_GET["idcliente"]) && !empty($_GET["idcliente"]))?$_GET["idcliente"]:"";
        $inputUsuarioSearch = (isset($_GET["usuario"]) && !empty($_GET["usuario"]))?$_GET["usuario"]:array();
        $inputFechaInicioStartSearch = (isset($_GET["fecha_inicio_start"]) && !empty($_GET["fecha_inicio_start"]))?$_GET["fecha_inicio_start"]:"";
        $inputFechaInicioEndSearch = (isset($_GET["fecha_inicio_end"]) && !empty($_GET["fecha_inicio_end"]))?$_GET["fecha_inicio_end"]:"";
        $inputFechaFinStartSearch = (isset($_GET["fecha_fin_start"]) && !empty($_GET["fecha_fin_start"]))?$_GET["fecha_fin_start"]:"";
        $inputFechaFinEndSearch = (isset($_GET["fecha_fin_end"]) && !empty($_GET["fecha_fin_end"]))?$_GET["fecha_fin_end"]:"";

        $records = ModuleLoansHelpersPrestamo::listado(array(
            "cliente" => $searchCliente,
            "tipo_intervalo" => $searchTipoIntervalo,
            "idcliente" => $searchIdCliente,
            "estado" => $searchEstado,
            "usuarios" => $inputUsuarioSearch,
            "fecha_inicio_start" => $inputFechaInicioStartSearch,
            "fecha_inicio_end" => $inputFechaInicioEndSearch,
            "fecha_fin_start" => $inputFechaFinStartSearch,
            "fecha_fin_end" => $inputFechaFinEndSearch,
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

        $pdf->WriteHTML($this->twig->render('@'.ModuleLoansConfig::getPrestamo().'/listado.pdf.twig', array(
            "pagination" => $pagination,
            "title" => "Listado de prestamos"
        )), true);

        //Close and output PDF document
        $pdf->Output('pagos.pdf', 'I');
    }

    private function breadcrumb ($breadcrumb) {
        $lista = array();
        foreach($breadcrumb as $value) {
            switch ($value) {
                case "listado":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase),
                        "text" => _("Prestamos")
                    );
                    break;
                case "add":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase . "&a=Add"),
                        "text" => _("Agregar prestamo")
                    );
                    break;
                case "edit":
                    $id = $_GET["id"];
                    $lista[] = array(
                        "url" => CoreRoute::getUrl($this->urlBase . "&a=Edit&id=" . $id),
                        "text" => _("Editar prestamo")
                    );
                    break;
            }
        }
        return $lista;
    }

} 