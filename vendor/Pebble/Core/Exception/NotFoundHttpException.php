<?php
/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

class Pebble_Core_Exception_NotFoundHttpException extends Pebble_Core_Exception_HttpException
{
    /**
     *
     * @param string $message
     * @param int $code
     */
    public function __construct ($message = 'The page you requested was not found.', $code = 404)
    {
        parent::__construct($message, $code);
    }
}
