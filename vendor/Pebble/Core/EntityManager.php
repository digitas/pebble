<?php

/**
 * EntityManager a singleton class
 *
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
class Pebble_Core_EntityManager
{
    private static $entityManager = null;

    /**
     * Get the instance
     *
     * @param array $config
     * @return Pebble_Core_EntityManager
     */
    public static function getInstance(array $config = array())
    {
        if (self::$entityManager === null){
            self::$entityManager = new Pebble_Core_EntityManager($config);
        }

        return self::$entityManager;
    }

    /**
     * Get the repository of entity class
     *
     * @param string $class The class name of entity
     * @return Pebble_Core_Repository
     */
    public function getRepository($class)
    {
        $schema = call_user_func($class . '::getSchema');

        if (isset($schema['repository'])) {
            return new $schema['repository']($this, $class);
        }

        return new Pebble_Core_Repository($this, $class);
    }

    /**
     * Get the schema of entity
     *
     * @param string $class The name of class of entity
     * @return array
     */
    public function getSchema($class)
    {
        return call_user_func($class . '::getSchema');
    }

    /**
     * Validate the entity
     *
     * @todo Implement
     *
     * @param Digitas_Core_Entity $entity
     * @return array where keys are field names and value error messages
     */
    public function validate(Pebble_Core_Entity $entity)
    {
        return array();
    }

    /**
     * Persist an entity
     *
     * @param Pebble_Core_Entity $entity
     */
    public function persist(Pebble_Core_Entity $entity)
    {
        $database = Pebble_Core_Database::getInstance();
        $database->beginTransaction();
        $query = $this->buildQueryPersist($entity);
        $database->prepare($query);
        $schema = $entity->getSchema();

        foreach ($schema['columns'] as $column) {
            $parameters[':' . $column] = $entity->get($column);
        }

        $database->execute($parameters);

        if ($entity->get($schema['primary']) === null) {
            $id = $database->getLastId();

            if ($id === null) {
                throw new Exception('Unable to get last insert id');
            }

            $entity->set($schema['primary'], $id);
        }
    }

    /**
     * Commit the transaction
     */
    public function flush()
    {
        $database = Pebble_Core_Database::getInstance();
        $database->commit();
    }

    /**
     * Remove an entity
     *
     * @param Pebble_Core_Entity $entity
     */
    public function remove(Pebble_Core_Entity $entity)
    {
        $schema = $entity->getSchema();
        $database = Pebble_Core_Database::getInstance();
        $database->prepare('DELETE FROM ' . $schema['table'] . ' WHERE id = :id');
        $database->execute(array('id' => $entity->getId()));
    }

    /**
     * Get the query to persist into database
     *
     * @param Pebble_Core_Entity $entity
     * @return string
     */
    protected function buildQueryPersist(Pebble_Core_Entity $entity)
    {
        $schema = $entity->getSchema();
        $query = null;

        if ($entity->get($schema['primary']) !== null){
            $columns = array();

            foreach($schema['columns'] as $column) {
                $columns[] = $column . ' = :' . $column;
            }

            $query = 'UPDATE ' . $schema['table'] . ' SET ' . implode(', ', $columns) . ' WHERE ' . $schema['primary'] . ' = :' . $schema['primary'];
        } else {
            $columns = array();
            $values = array();

            foreach($schema['columns'] as $column) {
                $columns[] = $column;
                $values[] = ':' . $column;
            }

            $query = 'INSERT INTO ' . $schema['table'] . '(' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ')';
        }

        return $query;
    }

    /**
     * Constructor
     */
    private function __construct(){}
}
