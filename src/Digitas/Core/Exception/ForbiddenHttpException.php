<?php
/**
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 * @copyright Digitas France <http://digitas.fr>
 */

class Digitas_Core_Exception_ForbiddenHttpException extends Digitas_Core_Exception_HttpException
{
    public function __construct ($message = 'You are not allowed to access this area.', $code = 403)
    {
        parent::__construct($message, $code);
    }
}
