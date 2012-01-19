<?php
/**
 * Repository
 *
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

abstract class Pebble_Core_Repository
{
    protected $link;
    protected $result;
    protected $config;

    /**
     * Constructor
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect($config);
    }

    /**
     * Create an entity in database
     *
     * @param Digitas_Core_Entity $entity
     */
    abstract protected function create(Pebble_Core_Entity $entity);

    /**
     * Update an entity
     *
     * @param Digitas_Core_Entity $entity
     */
    abstract protected function update(Pebble_Core_Entity $entity);

    /**
     * Populate an entity with values passed in param
     *
     * @param array where keys are field names and value are values
     */
    abstract public function populate($values);

    /**
     *
     * @param Digitas_Core_Entity $entity
     * @return array where keys are field names and value error messages
     */
    public function validate(Pebble_Core_Entity $entity)
    {
        return array();
    }

    /**
     * Connect to database
     *
     * @param array $config
     */
    protected function connect(array $config)
    {
        if (($this->link = mysql_connect($config['host'], $config['user'], $config['password'])) === false) {
            throw new Exception('Could not connect', 500);
        }

        if (mysql_select_db($config['name'], $this->link) === false){
            throw new Exception('Can\'t use ' . $config['name'] . ' : ' . mysql_error(), 500);
        }
    }

    /**
     * Execute a SQL query
     *
     * @param string $query
     * @return array|bool
     */
    protected function execute($query)
    {
        if (($result = mysql_query($query, $this->link)) === false) {
            throw new Exception(mysql_error($this->link), 500);
        }

        if ($result === true) {
            return true;
        }

        $rows = array();

        if (mysql_num_rows($result) === 0) {
            return $rows;
        }

        while (($row = mysql_fetch_assoc($result))) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Store an entity
     *
     * Create if if id is null, else update it
     *
     * @param Digitas_Core_Entity $entity
     */
    public function store(Pebble_Core_Entity $entity)
    {
        if ($entity->getId()) {
            $this->update($entity);
        } else {
            $this->create($entity);
            $result = $this->execute('SELECT LAST_INSERT_ID() as id');
            $result = $result[0];

            if (!$result) {
                throw new Exception('Unable to get last insert id');
            }

            $entity->setId($result['id']);
        }
    }


    /**
     * Search for an entity matching the conditions in $fieldValueTab
     *
     * @param array $fieldValueTab array($fieldName => $value)
     * @return type
     */
    public function findOneBy($fieldValueTab)
    {
        $table = $this->getTableName();
        $query = "SELECT * FROM $table WHERE 1 ";

        foreach ($fieldValueTab as $field => $value) {
            $query .= " AND $field = '$value'";
        }

        $result = $this->execute($query);

        if (!$result) {
            return null;
        }

        return $this->populate($result[0]);
    }


    /**
     * Search for all entities matching the conditions in $fieldValueTab
     *
     * @param array $fieldValueTab array($fieldName => $value)
     * @return type
     */
    public function findAllBy(array $fieldValueTab)
    {
        $table = $this->getTableName();
        $query = "SELECT * FROM $table WHERE 1 ";
        $cond = 0;

        foreach ($fieldValueTab as $field => $value) {
            $query .= " AND $field = '$value'";
        }

        $results = $this->execute($query);
        $collection = array();

        foreach ($results as $result) {
            $collection[] = $this->populate($result);
        }

        return $collection;
    }
}