<?php
/**
 * Repository
 *
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
class Pebble_Core_Repository
{
    private $entityManager;
    protected $entityName;

    /**
     * Constructor
     *
     * @param Pebble_Core_EntityManager $entityManager
     * @param string $entityName
     */
    public function __construct(Pebble_Core_EntityManager $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
    }

    /**
     * Search for all entities
     *
     * @return array
     */
    public function findAll()
    {
        $schema = $this->entityManager->getSchema($this->entityName);

        if ($schema === null) {
            throw new Exception('Schema is null');
        }

        $query = 'SELECT * FROM ' . $schema['table'];
        $results = $this->execute($query);

        return $results;
    }

    /**
     * Search for an entity matching the conditions in $fieldValueTab
     *
     * @param array $fieldValueTab array($fieldName => $value)
     * @return type
     */
    public function findOneBy($fieldValueTab)
    {
        $schema = $this->entityManager->getSchema($this->entityName);

        if ($schema === null) {
            throw new Exception('Schema is null');
        }

        $query = 'SELECT * FROM ' . $schema['table'] . ' WHERE 1 ';

        foreach ($fieldValueTab as $field => $value) {
            $query .= ' AND ' . $field . ' = "' . $value . '"';
        }

        $results = $this->execute($query);

        if (count($results) === 0) {
            return null;
        }

        return $results[0];
    }

    /**
     * Search for all entities matching the conditions in $fieldValueTab
     *
     * @param array $fieldValueTab array($fieldName => $value)
     * @return type
     */
    public function findAllBy(array $fieldValueTab)
    {
        $schema = $this->entityManager->getSchema($this->entityName);

        if ($schema === null) {
            throw new Exception('Schema is null');
        }

        $query = 'SELECT * FROM ' . $schema['table'] . ' WHERE 1 ';

        foreach ($fieldValueTab as $field => $value) {
            $query .= ' AND ' . $field . ' = "' . $value . '"';
        }

        $results = $this->execute($query);

        return $results;
    }

    /**
     * Execute a SQL query
     *
     * @param string $query
     * @return array|bool
     */
    protected function execute($query, array $parameters = array())
    {
        $results = array();
        $database = Pebble_Core_Database::getInstance();
        $database->prepare($query);
        $database->execute($parameters);

        return $database->fetchAll($this->entityName);
    }
}