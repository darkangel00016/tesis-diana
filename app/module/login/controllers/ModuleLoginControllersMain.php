<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:03
 */

class ModuleLoginControllersMain extends CoreControllers {

    public function index () {
        echo $this->twig->render('@ModuleLogin/login.html.twig', array(
            "title" => _("Inicio de sesión")
        ));
    }

    public function access () {
        $email = $_POST["email"];
        $clave = $_POST["password"];
        $code = $_POST["code"];
        $error = array(
            "msg" => _("Su usuario o contrasena son inconrrectos."),
            "error" => true
        );
        if($this->validateForm($email, $clave, $code)) {
            if ($_SESSION['catpcha'] == $code) {
                $auxClave = md5($clave);
                $usuario = new EntityUsuario;
                $response = $usuario->login(array(
                    array(
                        "field"=>"usuario",
                        "value"=>$email,
                        "comparacion"=>"="
                    ),
                    array(
                        "field"=>"passusuario",
                        "value"=>$auxClave,
                        "comparacion"=>"="
                    )
                ));
                if($response) {
                    if($usuario->getEstado() == "1") {
                        $_SESSION["usr"] = serialize($usuario);
                        $error["msg"] = _("Inicio de sesion correcto.");
                        $error["error"] = false;
                        $usuario->ultimo_inicio_sesion = time();
                    } else {
                        $error["msg"] = _("Esta inactivo, comuniquese con el administrador.");
                    }
                }
            }
        }
        echo json_encode($error);
    }

    private function validateForm ($email, $clave, $code) {
        $response = true;
        if(empty($email)) {
            $response = false;
        }
        if(empty($clave)) {
            $response = false;
        }
        if(empty($code)) {
            $response = false;
        }
        return $response;
    }

    public function catpcha() {
        JpGraph\JpGraph::load();
        JpGraph\JpGraph::module('antispam');
        $spam = new AntiSpam();
        $_SESSION['catpcha']= $spam->Rand(6);
        $spam->Stroke();
    }

    public function logout(){
        session_destroy();
        header("Location: index.php");
    }

} 