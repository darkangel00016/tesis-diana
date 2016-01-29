<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 01/12/15
 * Time: 12:19 AM
 */
class ModuleManagerMailControllersBoletin extends CoreControllers
{
    public static $urlBase = "index.php?m=ManagerMail&c=Boletin";

    public function index () {
        echo $this->twig->render('@' . ModuleManagerMailConfig::getBoletin() . '/boletin.html.twig', array(
            "title" => _("Listado de boletines"),
            "menu" => $this->menu(),
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina(),
            "breadcrumb" => $this->breadcrumb(array(
                "listado"
            ))
        ));
    }

    public function listado () {
        echo $this->twig->render('@' . ModuleManagerMailConfig::getBoletin() . '/boletin.listado.html.twig', array(
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina()
        ));
    }

    private function _listado () {

        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();
        $searchTipo = (isset($_GET["tipo"]) && !empty($_GET["tipo"]))?$_GET["tipo"]:array();

        $boletin = new ModuleManagerMailEntityBoletin;
        $records = $boletin->findAll(array(
            array(
                "field" => "estado",
                "comparacion" => "in",
                "value" => $searchEstado
            ),
            array(
                "field" => "tipo",
                "comparacion" => "in",
                "value" => $searchTipo
            )
        ), $this->getPagina(), PAGINATION_PAGES);
        $pagination = new CorePagination(
            $records["records"],
            PAGINATION_PAGES,
            CoreRoute::getUrl(self::$urlBase . "&a=listado"),
            $this->getPagina(),
            $records["total"]
        );
        return $pagination;
    }

    public function add ()
    {
        echo $this->twig->render('@' . ModuleManagerMailConfig::getBoletin() . '/boletin.add.html.twig', array(
            "title" => _("Agregar boletin"),
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
            $record = new ModuleManagerMailEntityBoletin($usr);
        }
        echo $this->twig->render('@' . ModuleManagerMailConfig::getBoletin() . '/boletin.add.html.twig', array(
            "title" => _("Editar boletin"),
            "menu" => $this->menu(),
            "record" => $record,
            "breadcrumb" => $this->breadcrumb(array(
                "listado",
                "edit"
            ))
        ));
    }

    public function save () {
        $currentUser = CoreRoute::getUserLogged();
        $id = (isset($_POST["id"]) && !empty($_POST["id"]))?$_POST["id"]:"";
        if(empty($id)) {
            $boletin = new ModuleManagerMailEntityBoletin;
        } else {
            $boletin = new ModuleManagerMailEntityBoletin($id);
        }
        $time = time();
        $boletin->tipo = $_POST["tipo"];
        $boletin->fecha_envio = $_POST["fecha_envio"];
        $boletin->contenido = $_POST["contenido"];
        $boletin->from = $_POST["from"];
        $boletin->subject = $_POST["subject"];
        $boletin->estado = $_POST["estado"];
        if(empty($id)) {
            $boletin->creado_por = $currentUser->getId();
            $boletin->fecha_creacion = $time;
        } else {
            $boletin->modificado_por = $currentUser->getId();
            $boletin->fecha_modificacion = $time;
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el boletin."),
            "error" => true
        );
        if ($boletin->tipo == "" || ($boletin->tipo == "p" && $boletin->fecha_envio == "") || $boletin->contenido == "" || $boletin->from == "" || $boletin->subject == "") {
            $error["msg"] = _("Hay campos vacios.");
        } else {
            $rowChanges = $boletin->save();
            $errorno = $boletin->getDbInstance()->errorno();
            if ($rowChanges > 0) {
                $error["msg"] = _("Se ha guardado el boletin exitosamente.");
                $error["error"] = false;
            } else if($errorno == 1062){
                $error["msg"] = _("Boletin duplicado.");
            } else {
                $error["msg"] = _("No hubo cambios en el registro." . $boletin->getDbInstance()->errornoString());
            }
        }

        echo json_encode($error);

    }

    public function delete () {

        $id = $_POST["id"];
        $error = array(
            "msg" => _("Ocurrio un error al eliminar el boletin."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $boletin = new ModuleManagerMailEntityBoletin($id);
            if($boletin->delete()) {
                $error["msg"] = _("Se elimino el registro exitosamente.");
                $error["error"] = false;
            }
        }
        echo json_encode($error);
    }

    private function breadcrumb ($breadcrumb) {
        $lista = array();
        foreach($breadcrumb as $value) {
            switch ($value) {
                case "listado":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase),
                        "text" => _("Boletin")
                    );
                    break;
                case "add":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=Add"),
                        "text" => _("Agregar Boletin")
                    );
                    break;
                case "edit":
                    $id = $_GET["id"];
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=Edit&id=" . $id),
                        "text" => _("Editar Boletin")
                    );
                    break;
            }
        }
        return $lista;
    }
}