<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:12
 */

class CoreControllers {

    protected $twig;
    protected $user;

    function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getPagina() {
        return (isset($_GET["pagina"]) && !empty($_GET["pagina"]))?$_GET["pagina"]:1;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return CoreRoute::getUserLogged();
    }

    public function menu() {
        return $this->twig->render('menu.html.twig', array('user' => $this->getUser()));
    }

}