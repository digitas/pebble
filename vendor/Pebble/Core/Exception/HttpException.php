<?php
/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

class Pebble_Core_Exception_HttpException extends Exception
{
    public static $status = array(
        '404' => 'Not Found',
        '403' => 'Forbidden'
    );
}
