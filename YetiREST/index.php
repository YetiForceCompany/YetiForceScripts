<?php
/**
 * Main YetiForce REST interface file.
 *
 * @copyright YetiForce Sp. z o.o
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @version 1.0
 */

require __DIR__ . '/vendor/autoload.php';

echo '<pre>';

try {
	$api = new \App\Portal();
	$api->debug = true;
	if ($login = $api->login()) {
		print_r($api->listModules());
	}
} catch (\Throwable $th) {
	echo $th->__toString();
}