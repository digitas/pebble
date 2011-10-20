<?php

/**
 * @author Pierre-Louis LAUNAY <pllaunay@digitas.com>
 * @copyright Digitas France <http://digitas.fr>
 */

// This will let the permissions be 0777
// needed to get the permission to erase cache file
umask(0000); 

require_once dirname(__FILE__) . '/../src/autoload.php';

$app = new Digitas_Backstage_Application_BackstageApplication();
$backstageController = new Digitas_Backstage_Controller_BackstageController();

/**
 * Handle homepage
 */
$app->get('/', array($backstageController, 'homepage'));
$app->post('/', array($backstageController, 'subscribe'));
$app->get('/closed', array($backstageController, 'closed'));
$app->get('/thanks', array($backstageController, 'thanks'));
$app->get('/admin', array($backstageController, 'admin'));
$app->match('/admin/login', array($backstageController, 'login'));
$app->get('/admin/export', array($backstageController, 'export'));
$app->get('/admin/logout', array($backstageController, 'logout'));

$app->run();