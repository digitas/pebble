<?php

/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

require_once  dirname(__FILE__) . '/../vendor/Pebble/Core/ClassLoader.php';
Pebble_Core_ClassLoader::register();

require_once dirname(__FILE__) . '/../vendor/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Pebble_Core_UniversalClassLoader();
$loader->registerPrefix('Digitas_', dirname(__FILE__) . '/../src');
$loader->register();