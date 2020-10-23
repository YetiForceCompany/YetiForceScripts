<?php
/**
 * Main YetiForce REST interface file.
 *
 * @copyright YetiForce Sp. z o.o
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @version 1.0
 */

namespace App;

require __DIR__ . '/vendor/autoload.php';

echo '<pre>';

$api = Portal::init([
	'apiPath' => 'https://gitdeveloper.yetiforce.com/',
	'wsAppName' => 'portal',
	'wsAppPass' => 'portal',
	'wsApiKey' => 'VMUwRByXHSq1bLW485ikfvcC97P6gJsz',
	'wsUserName' => 'demo@yetiforce.com',
	'wsUserPass' => 'demo',
]);
if ($login = $api->login()) {
	// var_dump($login);
	print_r($api->listModules());
}
