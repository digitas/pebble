<?php

/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
class Digitas_Demo_Repository_People extends Pebble_Core_Repository
{
    /**
     * Search all entities with alphabetical order
     * @return type
     */
    public function findAllOrderedByName()
    {
        $query = 'SELECT * FROM people ORDER BY name';
        $results = $this->execute($query);

        return $results;
    }
}
