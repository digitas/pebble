<?php

/**
 * @copyright Digitas France
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 */

// This will let the permissions be 0777
// needed to get the permission to erase cache file
umask(0000);

require_once dirname(__FILE__) . '/../app/autoload.php';
require_once dirname(__FILE__) . '/../app/Application.php';

$app = new Application('dev');
$app->run();