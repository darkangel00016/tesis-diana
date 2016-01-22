<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 01/12/15
 * Time: 12:19 AM
 */
class ModuleManagerMailControllersSupervisor extends CoreControllers
{
    public static $urlBase = "index.php?m=ManagerMail&c=Supervisor";

    public function index () {
        echo $this->twig->render('@' . ModuleManagerMailConfig::getSupervisor() . '/supervisor.html.twig', array(
            "title" => _("Listado de supervisores"),
            "menu" => $this->menu(),
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina(),
            "breadcrumb" => $this->breadcrumb(array(
                "listado"
            ))
        ));
    }

    public function import () {

        //cedula,apellidos, nombres, nacionalidad, estado civil, fecha nacimiento, genero, telefono, direccion, ciudad, correo
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "POST") {
            $error = array(
                "msg" => _("Ocurrio un error al importar los supervisores."),
                "error" => true
            );
            if(isset($_FILES) && sizeof($_FILES) > 0) {
                $types = array(
                    "text/csv",
                    "application/csv",
                    " application/vnd.ms-excel"
                );
                if(in_array($_FILES[0]["type"], $types)) {
                    $size = filesize($_FILES[0]["tmp_name"]);
                    if($size > 0) {
                        $content = file_get_contents($_FILES[0]["tmp_name"]);
                        $lineas = preg_split("/\n/", $content);
                        $lines = array();
                        foreach($lineas as $value) {
                            if(trim($value) != "") {
                                $lines[] = $value;
                            }
                        }
                        if(sizeof($lines) > 1) {
                            $supervisor = new ModuleManagerMailEntitySupervisor;
                            $data = array();
                            foreach($lines as $key => $value) {
                                $campos = preg_split("/;/", $value);
                                $data[$key] = array();
                                if($key > 0) {
                                    $supervisor->reset();
                                    $isExist = $supervisor->find($campos[0], "cedula");
                                    list($cedula, $apellidos, $nombres, $nacionalidad, $estado_civil, $fecha_nacimiento, $genero, $telefono, $direccion, $ciudad, $correo) = $campos;
                                    $supervisor->cedula = $cedula;
                                    $supervisor->apellidos = $apellidos;
                                    $supervisor->nombres = $nombres;
                                    $supervisor->correo = $correo;
                                    if(!$isExist) {
                                        $supervisor->estado = 1;
                                    }
                                    $rowChanges = $supervisor->save();
                                    $errorno = $supervisor->getDbInstance()->errorno();
                                    if ($rowChanges) {
                                        $data[$key]["msg"] = _("Se ha guardado el supervisor exitosamente.");
                                    } else if ($errorno == 1062) {
                                        $data[$key]["msg"] = _("Supervisor duplicado.");
                                    } else {
                                        $data[$key]["msg"] = _("No hubo cambios en el registro.");
                                    }
                                    $error["tipo"] = "campo";
                                } else {
                                    $error["tipo"] = "header";
                                }
                                $data[$key]["value"] = $campos;
                            }
                            unset($lines);
                            unset($lineas);
                            $error["msg"] = "Se ha importado exitosamente los supervisores.";
                            $error["data"] = array();
                            $error["data"] = $data;
                            $error["error"] = false;
                        } else {
                            $error["msg"] = _("El archivo no tiene contenido.");
                        }
                    } else {
                        $error["msg"] = _("El archivo no tiene contenido.");
                    }
                } else {
                    $error["msg"] = _("Solo se permite subir archivos .csv validos.");
                }
            } else {
                $error["msg"] = _("Debe enviar un archivo.");
            }
            echo json_encode($error);
        } else {
            echo $this->twig->render('@' . ModuleManagerMailConfig::getSupervisor() . '/supervisor.import.html.twig', array(
                "title" => _("Importar supervisores"),
                "menu" => $this->menu(),
                "breadcrumb" => $this->breadcrumb(array(
                    "listado",
                    "import"
                )),
                "urlBase" => self::$urlBase
            ));
        }
    }

    public function listado () {
        echo $this->twig->render('@' . ModuleManagerMailConfig::getSupervisor() . '/supervisor.listado.html.twig', array(
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina()
        ));
    }

    private function _listado () {

        $searchNombres = (isset($_GET["nombres"]) && !empty($_GET["nombres"]))?$_GET["nombres"]:"";
        $searchApellidos = (isset($_GET["apellidos"]) && !empty($_GET["apellidos"]))?$_GET["apellidos"]:"";
        $searchCorreo = (isset($_GET["correo"]) && !empty($_GET["correo"]))?$_GET["correo"]:"";
        $searchCedula = (isset($_GET["cedula"]) && !empty($_GET["cedula"]))?$_GET["cedula"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();

        $supervisor = new ModuleManagerMailEntitySupervisor;
        $records = $supervisor->findAll(array(
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
                "field" => "correo",
                "comparacion" => "like",
                "value" => $searchCorreo
            ),
            array(
                "field" => "cedula",
                "comparacion" => "like",
                "value" => $searchCedula
            ),
            array(
                "field" => "estado",
                "comparacion" => "in",
                "value" => $searchEstado
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
        echo $this->twig->render('@' . ModuleManagerMailConfig::getSupervisor() . '/supervisor.add.html.twig', array(
            "title" => _("Agregar supervisor"),
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
            $record = new ModuleManagerMailEntitySupervisor($usr);
        }
        echo $this->twig->render('@' . ModuleManagerMailConfig::getSupervisor() . '/supervisor.add.html.twig', array(
            "title" => _("Editar supervisor"),
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
            $supervisor = new ModuleManagerMailEntitySupervisor;
        } else {
            $supervisor = new ModuleManagerMailEntitySupervisor($id);
        }
        $time = time();
        $supervisor->nombres = $_POST["nombres"];
        $supervisor->apellidos = $_POST["apellidos"];
        $supervisor->correo = $_POST["correo"];
        $supervisor->cedula = $_POST["cedula"];
        $supervisor->estado = $_POST["estado"];
        if(empty($id)) {
            $supervisor->creado_por = $currentUser->getId();
            $supervisor->fecha_creacion = $time;
        } else {
            $supervisor->modificado_por = $currentUser->getId();
            $supervisor->fecha_modificacion = $time;
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el usuario."),
            "error" => true
        );

        if ($supervisor->getNombres() == "" || $supervisor->getApellidos() == "" || $supervisor->getCorreo() == "" || $supervisor->getCedula() == "") {
            $error["msg"] = _("Hay campos vacios.");
        } else {
            $rowChanges = $supervisor->save();
            $errorno = $supervisor->getDbInstance()->errorno();
            if ($rowChanges > 0) {
                $error["msg"] = _("Se ha guardado el supervisor exitosamente.");
                $error["error"] = false;
            } else if($errorno == 1062){
                $error["msg"] = _("Supervisor duplicado.");
            } else {
                $error["msg"] = _("No hubo cambios en el registro.");
            }
        }

        echo json_encode($error);

    }

    public function delete () {

        $id = $_POST["id"];
        $error = array(
            "msg" => _("Ocurrio un error al eliminar el supervisor."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $supervisor = new ModuleManagerMailEntitySupervisor($id);
            if($supervisor->delete()) {
                $error["msg"] = _("Se elimino el registro exitosamente.");
                $error["error"] = false;
            }
        }
        echo json_encode($error);
    }

    public function model() {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=example.csv');
        header('Pragma: no-cache');
        echo "cedula;apellidos;nombres;nacionalidad;estado civil;fecha nacimiento;genero;telefono;direccion;ciudad;correo";
    }

    private function breadcrumb ($breadcrumb) {
        $lista = array();
        foreach($breadcrumb as $value) {
            switch ($value) {
                case "listado":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase),
                        "text" => _("Supervisores")
                    );
                    break;
                case "add":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=Add"),
                        "text" => _("Agregar Supervisor")
                    );
                    break;
                case "edit":
                    $id = $_GET["id"];
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=Edit&id=" . $id),
                        "text" => _("Editar Supervisor")
                    );
                    break;
                case "import":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=import"),
                        "text" => _("Importar Supervisores")
                    );
                    break;
            }
        }
        return $lista;
    }
}