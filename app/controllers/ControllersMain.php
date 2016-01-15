<?php
/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 17/11/2015
 * Time: 0:03
 */

class ControllersMain extends CoreControllers {

    public function index () {
        echo $this->twig->render('main.index.html.twig', array(
            "title" => "Inicio",
            "menu" => $this->menu()
        ));
    }

} 