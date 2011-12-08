<?php

/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

class Pebble_Core_UniversalClassLoader
{
    protected $prefixes;

    /**
     * Registers an array of classes using the PEAR naming convention.
     *
     * @param array $classes An array of classes (prefixes as keys and locations as values)
     *
     * @api
     */
    public function registerPrefixes(array $classes)
    {
        foreach ($classes as $prefix => $paths) {
            $this->prefixes[$prefix] = (array) $paths;
        }
    }

    /**
     * Registers a set of classes using the PEAR naming convention.
     *
     * @param string       $prefix  The classes prefix
     * @param array|string $paths   The location(s) of the classes
     *
     * @api
     */
    public function registerPrefix($prefix, $paths)
    {
        $this->prefixes[$prefix] = (array) $paths;
    }

    /**
     * Registers this instance as an autoloader.
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'), true);
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     */
    public function loadClass($class)
    {
        if (($file = $this->findFile($class))) {
            require $file;
        }
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|null The path, if found
     */
    private function findFile($class)
    {
        // PEAR-like class name
        $normalizedClass = str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';

        foreach ($this->prefixes as $prefix => $dirs) {

            if (0 !== strpos($class, $prefix)) {
                continue;
            }

            foreach ($dirs as $dir) {
                $file = $dir.DIRECTORY_SEPARATOR.$normalizedClass;

                if (is_file($file)) {
                    return $file;
                }
            }
        }

        return null;
    }
}
