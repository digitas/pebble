<?php
/**
 * Repository
 * 
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 * @copyright Digitas France <http://digitas.fr>
 */

abstract class Digitas_Core_Repository
{
    protected $link;
    protected $result;
    
    /**
     * Constructor
     */
    public function __construct(array $config)
    {
        $this->connect($config);
    }
    
    /**
     * Store an object entity
     */
    abstract public function store(Digitas_Core_Entity $entity);
    
    /**
     * Update an object entity
     */
    abstract public function update(Digitas_Core_Entity $entity);
    
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
}
