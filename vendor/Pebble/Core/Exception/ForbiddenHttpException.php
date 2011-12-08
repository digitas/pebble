<?php
/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

class Pebble_Core_Exception_ForbiddenHttpException extends Pebble_Core_Exception_HttpException
{
    /**
     *
     * @param string $message
     * @param int $code
     */
    public function __construct ($message = 'You are not allowed to access this area.', $code = 403)
    {
        parent::__construct($message, $code);
    }
}
