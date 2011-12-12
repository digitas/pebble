<?php
/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

class Pebble_Core_ControllerCollection
{
    protected $routes;

    /**
     *
     */
    public function __construct()
    {
        $this->routes = array();
    }

    /**
     * Set a new route GET
     *
     * @param string $route
     * @param array $callbackFunction
     */
    public function get($route, $callbackFunction)
    {
        $this->routes[$route]['get'] = $callbackFunction;
    }

    /**
     * Set a new route POST
     *
     * @param string $route
     * @param array $callbackFunction
     */
    public function post($route, $callbackFunction)
    {
        $this->routes[$route]['post'] = $callbackFunction;
    }

    /**
     * Set a new route POST and GET
     *
     * @param string $route
     * @param string $callbackFunction
     */
    public function match($route, $callbackFunction)
    {
        $this->get($route, $callbackFunction);
        $this->post($route, $callbackFunction);
    }

    /**
     * Get all routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}