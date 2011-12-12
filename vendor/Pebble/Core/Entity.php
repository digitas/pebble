<?php

/**
 * Entity
 *
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
abstract class Pebble_Core_Entity implements Pebble_Core_EntityInterface
{
    /**
     * Method get
     *
     * @param string $field The field name
     * @return mixed
     */
    public function get($field)
    {
        $method = 'get' . ucfirst($field);
        if (!method_exists($this, $method)) {
            throw new Exception(sprintf('Method "%s" is undefined in class %s', $method, get_class($this)));
        }

        return call_user_func(array($this, $method));
    }

    /**
     * Setter method
     *
     * @param string $field The field name
     * @param mixed $value The value to set
     */
    public function set($field, $value)
    {
        $method = 'set' . ucfirst($field);
        if (!method_exists($this, $method)) {
            throw new Exception(sprintf('Method "%s" is undefined in class %s', $method, get_class($this)));
        }

        call_user_func(array($this, $method), $value);
    }
}