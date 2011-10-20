<?php

/**
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 * @copyright Digitas France <http://digitas.fr>
 */

require_once  dirname(__FILE__) . '/Digitas/Core/ClassLoader.php';
Digitas_Core_ClassLoader::register();

require_once dirname(__FILE__) . '/../vendor/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();