<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:37 PM
 */
class ModuleLoginHelpersLogin
{
    public static function access( $data ) {
        $email = $data["email"];
        $clave = $data["clave"];
        if(isset($data["code"])) {
            $code = $data["code"];
        }
        $error = array(
            "msg" => _("Su usuario o contraseña son invalidos."),
            "error" => true
        );
        $isValid = self::_validateForm($email, $clave);
        if(isset($code)) {
            $isValid = self::validateForm($email, $clave, $code);
        }
        if($isValid) {
            $in = true;
            if(isset($code)) {
                $in = @$_SESSION['catpcha'] == $code;
            }
            if($in) {
                $auxClave = md5($clave);
                $usuario = new ModuleUserEntityUsuario;
                $response = $usuario->login(array(
                    array(
                        "field" => "usuario",
                        "value" => $email,
                        "comparacion" => "="
                    ),
                    array(
                        "field" => "passusuario",
                        "value" => $auxClave,
                        "comparacion" => "="
                    )
                ));
                if ($response) {
                    if ($usuario->getEstado() == "1") {
                        $_SESSION["usr"] = $usuario->getId();
                        $error["msg"] = _("Inicio de sesión correcto.");
                        $error["error"] = false;
                        $error["token"] = session_id();
                        $error["data"] = array(
                            "id"=> $usuario->getId(),
                            "nombre"=> $usuario->getNombre(),
                            "tipo"=> $usuario->getTipo(),
                            "ultimo_inicio_sesion"=> $usuario->getUltimoInicioSesion()
                        );
                        $config = CoreConfig::getConfig();
                        $error["config"] = $config->getData();
                        $usuario->ultimo_inicio_sesion = time();
                        $usuario->save();
                    } else {
                        $error["msg"] = _("Esta inactivo, comuniquese con el administrador.");
                    }
                }
            }
        }
        return $error;
    }

    public static function _validateForm ($email, $clave) {
        $response = true;
        if(empty($email)) {
            $response = false;
        }
        if(empty($clave)) {
            $response = false;
        }
        return $response;
    }

    public static function validateForm ($email, $clave, $code) {
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

    public static function forgot(Twig_Environment $twig)
    {
        $usuario = new ModuleUserEntityUsuario;
        $email = isset($_POST["email"])?$_POST["email"]:"";
        if(isset($_POST["code"])) {
            $code = $_POST["code"];
        }
        $error = array(
            "msg" => _("Debe enviar el email."),
            "error" => true
        );
        $in = true;
        if(isset($code)) {
            $in = @$_SESSION['catpcha'] == $code;
            if(!$in) {
                $error["msg"] = _("El código de validación no es valido {$_SESSION['catpcha']}.");
            }
        }
        if(!empty($email) && $in) {
            if($usuario->findByField($email, "usuario")) {
                $code = rand(10000000, 99999999);
                $usuario->check_code = $code;
                if($usuario->save()) {
                    $sentMail = CoreMail::sentMail(
                        array(
                            array("mail" => $usuario->getUsuario(), "user" => $usuario->getNombre())
                        ),
                        _("Olvido de clave"),
                        $twig->render("@ModuleLogin/forgot.html.twig", array( "user" => $usuario, "code" => $code )),
                        $twig->render("@ModuleLogin/forgot.txt.twig", array( "user" => $usuario, "code" => $code ))
                    );
                    if(!$sentMail["error"]) {
                        $error["msg"] = _("Se genero el código exitosamente.");
                        $error["error"] = false;
                        $error["code"] = $code;
                    } else {
                        $error["msg"] = $sentMail["msg"];
                        $error["error"] = true;
                    }
                } else {
                    $error["msg"] = _("Ocurrió un error al generar el código de confirmación.");
                }
            } else {
                $error["msg"] = _("El usuario no existe.");
            }
        }
        return $error;
    }

    public static function check()
    {
        $usuario = new ModuleUserEntityUsuario;
        $email = isset($_POST["email"])?$_POST["email"]:"";
        $code = isset($_POST["code"])?$_POST["code"]:"";
        $password = isset($_POST["password"])?$_POST["password"]:"";
        if(isset($_POST["check_code"])) {
            $check_code = $_POST["check_code"];
        }
        $error = array(
            "msg" => _("Hay campos vacios."),
            "error" => true
        );
        $in = true;
        if(isset($check_code)) {
            $in = @$_SESSION['catpcha'] == $check_code;
            if(!$in) {
                $error["msg"] = _("El código de validación no es valido {$_SESSION['catpcha']}.");
            }
        }
        if(!empty($email) && !empty($code) && !empty($password) && $in) {
            if($usuario->findByField($email, "usuario")) {
                if($usuario->check_code == $code) {
                    $usuario->passusuario = md5($password);
                    $error["msg"] = _("Clave cambiada exitosamente.");
                    $error["error"] = false;
                } else {
                    $error["msg"] = _("código de confirmación es invalido {$usuario->passusuario}.");
                }
                $usuario->check_code = "";
                $usuario->save();
            } else {
                $error["msg"] = _("El usuario no existe.");
            }
        }
        return $error;
    }
}