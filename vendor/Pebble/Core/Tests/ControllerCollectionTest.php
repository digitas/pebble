<?php
/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

require_once dirname(__FILE__) . '/../ControllerCollection.php';

class ControllerCollectionTest extends PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $controllerCollection = new Pebble_Core_ControllerCollection();
        $controllerCollection->get('/', 'homepage');
        $routes = $controllerCollection->getRoutes();
        $this->assertTrue(is_array($routes));
        $this->assertArrayHasKey('/', $routes);
        $this->assertArrayHasKey('get', $routes['/']);
        $this->assertContains('homepage', $routes['/']['get']);
    }

    public function testPost()
    {
        $controllerCollection = new Pebble_Core_ControllerCollection();
        $controllerCollection->post('/', 'homepage');
        $routes = $controllerCollection->getRoutes();
        $this->assertTrue(is_array($routes));
        $this->assertArrayHasKey('/', $routes);
        $this->assertArrayHasKey('post', $routes['/']);
        $this->assertContains('homepage', $routes['/']['post']);
    }

    public function testMatch()
    {
        $controllerCollection = new Pebble_Core_ControllerCollection();
        $controllerCollection->match('/', 'homepage');
        $routes = $controllerCollection->getRoutes();
        $this->assertTrue(is_array($routes));
        $this->assertArrayHasKey('/', $routes);
        $this->assertArrayHasKey('get', $routes['/']);
        $this->assertArrayHasKey('post', $routes['/']);
        $this->assertContains('homepage', $routes['/']['post']);
        $this->assertContains('homepage', $routes['/']['get']);
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}