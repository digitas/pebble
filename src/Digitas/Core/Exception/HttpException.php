<?php
/**
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 * @copyright Digitas France <http://digitas.fr>
 */

class Digitas_Core_Exception_HttpException extends Exception
{
    public static $status = array(
        '404' => 'Not Found',
        '403' => 'Forbidden'
    );
}
