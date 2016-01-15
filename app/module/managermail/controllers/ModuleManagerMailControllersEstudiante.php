<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 01/12/15
 * Time: 12:19 AM
 */
class ModuleManagerMailControllersEstudiante extends CoreControllers
{
    public static $urlBase = "index.php?m=ManagerMail&c=Estudiante";

    public function index () {
        echo $this->twig->render('@ModuleManagerMail/estudiante.html.twig', array(
            "title" => _("Listado de estudiantes"),
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
                "msg" => _("Ocurrio un error al importar los estudiantes."),
                "error" => true
            );
            if(isset($_FILES) && sizeof($_FILES) > 0) {
                $types = array(
                    "text/csv",
                    "application/csv",
                    "application/vnd.ms-excel"
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
                            $estudiante = new EntityEstudiante;
                            $data = array();
                            foreach($lines as $key => $value) {
                                $campos = preg_split("/;/", $value);
                                $data[$key] = array();
                                if($key > 0) {
                                    $estudiante->reset();
                                    $isExist = $estudiante->find($campos[0], "cedula");
                                    list($cedula, $apellidos, $nombres, $nacionalidad, $estado_civil, $fecha_nacimiento, $genero, $telefono, $direccion, $ciudad, $correo) = $campos;
                                    $estudiante->cedula = $cedula;
                                    $estudiante->apellidos = $apellidos;
                                    $estudiante->nombres = $nombres;
                                    $estudiante->correo = $correo;
                                    if(!$isExist) {
                                        $estudiante->estado = 1;
                                    }
                                    $rowChanges = $estudiante->save();
                                    $errorno = $estudiante->getDbInstance()->errorno();
                                    if ($rowChanges) {
                                        $data[$key]["msg"] = _("Se ha guardado el estudiante exitosamente.");
                                    } else if ($errorno == 1062) {
                                        $data[$key]["msg"] = _("Estudiante duplicado.");
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
                            $error["msg"] = "Se ha importado exitosamente los estudiantes.";
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
            echo $this->twig->render('@ModuleManagerMail/estudiante.import.html.twig', array(
                "title" => _("Importar estudiantes"),
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
        echo $this->twig->render('@ModuleManagerMail/estudiante.listado.html.twig', array(
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

        $estudiante = new EntityEstudiante;
        $records = $estudiante->findAll(array(
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
        echo $this->twig->render('@ModuleManagerMail/estudiante.add.html.twig', array(
            "title" => _("Agregar estudiante"),
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
            $record = new EntityEstudiante($usr);
        }
        echo $this->twig->render('@ModuleManagerMail/estudiante.add.html.twig', array(
            "title" => _("Editar estudiante"),
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
            $estudiante = new EntityEstudiante;
        } else {
            $estudiante = new EntityEstudiante($id);
        }
        $time = time();
        $estudiante->nombres = $_POST["nombres"];
        $estudiante->apellidos = $_POST["apellidos"];
        $estudiante->correo = $_POST["correo"];
        $estudiante->cedula = $_POST["cedula"];
        $estudiante->estado = $_POST["estado"];
        if(empty($id)) {
            $estudiante->creado_por = $currentUser->getId();
            $estudiante->fecha_creacion = $time;
        } else {
            $estudiante->modificado_por = $currentUser->getId();
            $estudiante->fecha_modificacion = $time;
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el usuario."),
            "error" => true
        );

        if ($estudiante->getNombres() == "" || $estudiante->getApellidos() == "" || $estudiante->getCorreo() == "" || $estudiante->getCedula() == "") {
            $error["msg"] = _("Hay campos vacios.");
        } else {
            $rowChanges = $estudiante->save();
            $errorno = $estudiante->getDbInstance()->errorno();
            if ($rowChanges > 0) {
                $error["msg"] = _("Se ha guardado el estudiante exitosamente.");
                $error["error"] = false;
            } else if($errorno == 1062){
                $error["msg"] = _("Estudiante duplicado.");
            } else {
                $error["msg"] = _("No hubo cambios en el registro.");
            }
        }

        echo json_encode($error);

    }

    public function delete () {

        $id = $_POST["id"];
        $error = array(
            "msg" => _("Ocurrio un error al eliminar el estudiante."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $estudiante = new EntityEstudiante($id);
            if($estudiante->delete()) {
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
                        "text" => _("Estudiantes")
                    );
                    break;
                case "add":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=Add"),
                        "text" => _("Agregar estudiante")
                    );
                    break;
                case "edit":
                    $id = $_GET["id"];
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=Edit&id=" . $id),
                        "text" => _("Editar estudiante")
                    );
                    break;
                case "import":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=import"),
                        "text" => _("Importar estudiantes")
                    );
                    break;
            }
        }
        return $lista;
    }
}