<?php
/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

class Pebble_Core_ClassLoader
{
    /**
     * Registers Pebble_Core_ClassLoader as an SPL autoloader.
     */
    static public function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(new self, 'autoload'));
    }

    /**
     * Handles autoloading of classes.
     *
     * @param  string  $class  A class name.
     *
     * @return boolean Returns true if the class has been loaded
     */
    static public function autoload($class)
    {
        if (0 !== strpos($class, 'Pebble')) {
            return;
        }

        if (is_file($file = dirname(__FILE__).'/../../'.str_replace(array('_', "\0"), array('/', ''), $class).'.php')) {
            require $file;
        }
    }
}
