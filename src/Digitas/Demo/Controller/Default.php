<?php

/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 **/
class Digitas_Demo_Controller_Default extends Pebble_Core_Controller
{
    public function homepageAction()
    {
        return $this->render('Demo/homepage.html.twig');
    }

    public function connect(Pebble_Core_Application $app)
    {
        $controllers = new Pebble_Core_ControllerCollection();
        $controllers->get('/', 'homepage');

        return $controllers;
    }
}
