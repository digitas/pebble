<?php

/**
 * @author Damien Pitard <dpitard at digitas.fr>
 * @copyright Digitas France
 */
class Application extends Pebble_Core_Application
{
    public function getControllers()
    {
        return array(
            '/' => new Digitas_Demo_Controller_Default(),
        );
    }
}
