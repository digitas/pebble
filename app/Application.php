<?php

/**
 * @author Damien Pitard <dpitard at digitas.fr>
 * @copyright Digitas France
 */
class Application extends Pebble_Core_Application
{
    public function getControllers()
    {
        $controllers = array(
            // Add another routes here
        );

        if ($this->config['app']['env'] == 'dev') {
            $controllers['/demo'] = new Digitas_Demo_Controller_Default();
        }

        return $controllers;
    }
}
