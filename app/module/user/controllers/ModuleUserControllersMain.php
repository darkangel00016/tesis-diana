<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:03
 */

class ModuleUserControllersMain extends CoreControllers {

    public static $urlBase = "index.php?m=User";

    public function index () {
        echo $this->twig->render('@ModuleUser/user.html.twig', array(
            "title" => _("Listado de usuarios"),
            "menu" => $this->menu(),
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina(),
            "breadcrumb" => $this->breadcrumb(array(
                "listado"
            ))
        ));
    }

    public function listado () {
        echo $this->twig->render('@ModuleUser/user.listado.html.twig', array(
            "pagination" => $this->_listado(),
            "pagina" => $this->getPagina()
        ));
    }

    private function _listado () {

        $searchUsuario = (isset($_GET["usuario"]) && !empty($_GET["usuario"]))?$_GET["usuario"]:"";
        $searchTipo = (isset($_GET["tipo"]) && !empty($_GET["tipo"]))?$_GET["tipo"]:array();
        $searchNombre = (isset($_GET["nombre"]) && !empty($_GET["nombre"]))?$_GET["nombre"]:"";
        $searchEstado = (isset($_GET["estado"]) && !empty($_GET["estado"]))?$_GET["estado"]:array();

        $usuario = new EntityUsuario;
        $records = $usuario->findAll(array(
            array(
                "field" => "usuario",
                "comparacion" => "like",
                "value" => $searchUsuario
            ),
            array(
                "field" => "tipo",
                "comparacion" => "in",
                "value" => $searchTipo
            ),
            array(
                "field" => "nombre",
                "comparacion" => "like",
                "value" => $searchNombre
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
            CoreRoute::getUrl("index.php?m=User&a=listado"),
            $this->getPagina(),
            $records["total"]
        );
        return $pagination;
    }

    public function add ()
    {
        echo $this->twig->render('@ModuleUser/user.add.html.twig', array(
            "title" => _("Agregar usuario"),
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
            $record = new EntityUsuario($usr);
        }
        $u = CoreRoute::getUserLogged();
        echo $this->twig->render('@ModuleUser/user.add.html.twig', array(
            "title" => _("Editar usuario"),
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
            $usuario = new EntityUsuario;
        } else {
            $usuario = new EntityUsuario($id);
        }
        $time = time();
        $usuario->usuario = $_POST["email"];
        $usuario->nombre = $_POST["nombre"];
        if(empty($id)) {
            $usuario->passusuario = md5($_POST["password"]);
            $usuario->creado_por = $currentUser->getId();
            $usuario->fecha_creacion = $time;
        } else {
            $usuario->modificado_por = $currentUser->getId();
            $usuario->fecha_modificacion = $time;
            $password = (isset($_POST["password"]) && !empty($_POST["password"]))?$_POST["password"]:"";
            if(!empty($password) && $usuario->passusuario != md5($password)) {
                $usuario->passusuario = md5($password);
            }
        }
        if(CoreRoute::isAdmin()) {
            $usuario->tipo = $_POST["tipo"];
            $usuario->estado = $_POST["estado"];
        }
        $error = array(
            "msg" => _("Ocurrio un error al guardar el usuario."),
            "error" => true
        );
        if(!CoreRoute::isAdmin() && ($id != $currentUser->getId())){
            $error["msg"] = _("No coincide el usuario logeado con el enviado.");
        } else {
            if ($usuario->getUsuario() == "" || ($usuario->getPassusuario() == "" && $usuario->isNew) || $usuario->getTipo() == "") {
                $error["msg"] = _("Hay campos vacios.");
            } else {
                $rowChanges = $usuario->save();
                $errorno = $usuario->getDbInstance()->errorno();
                if ($rowChanges > 0) {
                    $error["msg"] = _("Se ha guardado el usuario exitosamente.");
                    $error["error"] = false;
                } else if($errorno == 1062){
                    $error["msg"] = _("Usuario duplicado.");
                } else {
                    $error["msg"] = _("No hubo cambios en el registro.");
                }
            }
        }

        echo json_encode($error);

    }

    public function delete () {

        $id = $_POST["id"];
        $error = array(
            "msg" => _("Ocurrio un error al registrar el usuario."),
            "error" => true
        );
        if(empty($id)) {
            $error["msg"] = _("Seleccione un registro valido.");
        } else {
            $usuario = new EntityUsuario($id);
            if($usuario->delete()) {
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
                        "text" => _("Usuarios")
                    );
                    break;
                case "add":
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=Add"),
                        "text" => _("Agregar usuario")
                    );
                    break;
                case "edit":
                    $id = $_GET["id"];
                    $lista[] = array(
                        "url" => CoreRoute::getUrl(self::$urlBase . "&a=Edit&id=" . $id),
                        "text" => _("Editar usuario")
                    );
                    break;
            }
        }
        return $lista;
    }

} 