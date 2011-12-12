<?php

/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
class Digitas_Demo_Entity_People extends Pebble_Core_Entity
{
    protected $id;
    protected $name;
    protected $lastName;

    /**
     * Define a schema of table
     *
     * @return array
     */
    static public function getSchema()
    {
        return array(
            'repository' => 'Digitas_Demo_Repository_People',
            'primary' => 'id',
            'table' => 'people',
            'columns' => array(
                'id',
                'name',
                'lastName',
            )
        );
    }

    /**
     * Get the id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the last name
     * @param type $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
}
