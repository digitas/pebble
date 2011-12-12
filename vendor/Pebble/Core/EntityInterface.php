<?php

/**
 * Entity
 *
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
interface Pebble_Core_EntityInterface
{
    /**
     * Define the table schema
     */
    static public function getSchema();
}