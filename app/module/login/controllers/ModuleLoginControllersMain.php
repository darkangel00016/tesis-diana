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
        $error = ModuleLoginHelpersLogin::access(array(
            "email" => $_POST["email"],
            "clave" => $_POST["password"],
            "code" => $_POST["code"]
        ));
        echo json_encode($error);
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