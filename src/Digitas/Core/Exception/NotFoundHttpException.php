<?php
/**
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 * @copyright Digitas France <http://digitas.fr>
 */

class Digitas_Core_Exception_NotFoundHttpException extends Exception
{
    public function __construct ($message = 'Not found', $code = 404)
    {
        parent::__construct($message, $code);
    }
}
