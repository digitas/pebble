<?php

/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
class Pebble_Core_Database
{
    private static $database = null;
    private $handlerDatabase = null;
    private $isTransaction = false;
    private $statement = null;
    private $entityManager = null;

    /**
     * Declare a new transaction
     */
    public function beginTransaction()
    {
        if (!$this->isTransaction) {
            if ($this->handlerDatabase->beginTransaction()) {
                $this->isTransaction = true;
            }
        }
    }

    /**
     * Prepare a query
     *
     * @param string $query
     */
    public function prepare($query)
    {
        $this->statement = $this->handlerDatabase->prepare($query);

        if (($this->statement = $this->handlerDatabase->prepare($query)) === false){
            $errorInfo = $statement->errorInfo();
            throw new Exception($errorInfo[0] . ' ' . $errorInfo[2]);
        }
    }

    /**
     * Execute a prepared query
     *
     * @param array $parameters
     */
    public function execute(array $parameters = array())
    {
        if ($this->statement->execute($parameters) === false){
            $info = $this->statement->errorInfo();
            throw new Exception('Error: ' . $info[0] . ' ' . $info[2]);
        }
    }

    /**
     * Commit the transaction
     */
    public function commit()
    {
        if ($this->handlerDatabase->commit()){
            $this->isTransaction = false;
        }
    }

    /**
     * Get the array of results from a query
     *
     * @param string $className
     * @return array
     */
    public function fetchAll($className)
    {
        return $this->statement->fetchAll(PDO::FETCH_CLASS, $className);
    }

    /**
     * Get the last id from the last insert
     *
     * @return string
     */
    public function getLastId()
    {
        $results = $this->handlerDatabase->query('SELECT LAST_INSERT_ID() as id');

        foreach  ($results as $row) {
            return $row['id'];
        }

        return null;
    }

    /**
     * Get an instance of Pebble_Core_Database.
     *
     * @param array $config
     * @return Pebble_Core_Database
     */
    public static function getInstance(array $config = array())
    {
        if (self::$database === null){
            self::$database = new Pebble_Core_Database($config);
        }

        return self::$database;
    }

    /**
     * Create a new connection to database
     *
     * @param string $config
     */
    protected function connect($config)
    {
        if (!isset($config['pdo'])) {
            throw new Exception(sprintf('The PDO type is missing'));
        }

        switch($config['pdo']) {
            case 'mysql':
                $config['driverOptions'] = array(
                    'PDO::MYSQL_ATTR_INIT_COMMAND' => 'SET NAMES utf8',
                    );
                $this->handlerDatabase = Pebble_Core_Driver_Mysql::connect($config);
                break;

            default:
                throw new InvalidArgumentException(sprintf('The PDO type "%s" isn\'t implemented', $config['pdo']));
        }
    }

    /**
     * Constructor
     *
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->connect($config);
        $this->entityManager = Pebble_Core_EntityManager::getInstance();
    }
}
