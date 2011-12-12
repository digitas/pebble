<?php

/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */
class Pebble_Core_Driver_Mysql
{
    /**
     * Create a new mysql connection
     *
     * @param array $config
     * @return PDO
     */
    static public function connect(array $config)
    {
        return new PDO(
                'mysql:' . self::buildDsn($config),
                (isset($config['username']) ? $config['username'] : ''),
                (isset($config['password']) ? $config['password'] : ''),
                (isset($config['driverOptions']) ? $config['driverOptions'] : ''));
    }

    /**
     * Get the dsn
     *
     * @param array $config
     * @return string
     */
    static private function buildDsn(array $config)
    {
        $dsn = null;

        if (!isset($config['unixSocket'])) {
            $dsn = (isset($config['host']) ? 'host=' . $config['host'] : '');
            $dsn .= (isset($config['port']) ? ($dsn == '' ? '' : ';') . 'port=' . $config['port'] : '');
        } else {
            $dsn = 'unix_socket=' . $config['unixSocket'];
        }

        $dsn .= (isset($config['dbname']) ? ($dsn == '' ? '' : ';') . 'dbname=' . $config['dbname'] : '');

        return 'mysql:' . $dsn;
    }
}
