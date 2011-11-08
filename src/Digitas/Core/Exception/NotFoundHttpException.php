<?php
/**
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 * @copyright Digitas France <http://digitas.fr>
 */

class Digitas_Core_Exception_NotFoundHttpException extends Digitas_Core_Exception_HttpException
{
    public function __construct ($message = 'The page you requested was not found.', $code = 404)
    {
        parent::__construct($message, $code);
    }
}
