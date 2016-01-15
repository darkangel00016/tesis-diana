<?php

/**
 * Created by PhpStorm.
 * User: lerny
 * Date: 11/12/15
 * Time: 12:33 PM
 */
class ModuleApiControllersConfiguracion extends CoreControllers
{
    public function index() {
        $config = CoreConfig::getConfig(true);
        echo json_encode($config->getData());
    }
}