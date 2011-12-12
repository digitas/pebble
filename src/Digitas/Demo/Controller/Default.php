<?php

/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
class Digitas_Demo_Controller_Default extends Pebble_Core_Controller
{
    /**
     * Show the homepage
     *
     * @return type
     */
    public function homepageAction()
    {
        return $this->render('Demo/homepage.html.twig');
    }

    /**
     * Correspond a route with method name
     *
     * @param Pebble_Core_Application $app
     * @return Pebble_Core_ControllerCollection
     */
    public function connect(Pebble_Core_Application $app)
    {
        $controllerCollection = new Pebble_Core_ControllerCollection();
        $controllerCollection->get('/demo', 'homepage');

        return $controllerCollection;
    }
}
