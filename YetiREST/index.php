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

$api = Portal::init();
if ($login = $api->login()) {
	// var_dump($login);
	print_r($api->listModules());
}
