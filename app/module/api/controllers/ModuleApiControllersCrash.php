<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:33 PM
 */
class ModuleApiControllersCrash extends CoreControllers
{
    public function index() {
        $crash = $_POST["crash"];
        $db = CoreDb::getInstance();
        $db->insert("crash", array(
            "text" => $crash
        ));
    }
}