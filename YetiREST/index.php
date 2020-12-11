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

/*
ini_set('html_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 'On');
error_reporting(E_ALL);
echo '<pre>';
*/

try {
	$api = new \App\Portal();
	$api->debug = true;
	try {
		$api->init();
		if ($login = $api->login()) {
			print_r($api->listModules());

			// print_r($api->listModules());

			// print_r($api->getRecord('Contacts', 634));

			// print_r($api->listRecords('Contacts', []));

			// print_r($api->createRecord('Contacts', [
			// 	'firstname' => 'xxx',
			// 	'lastname' => 'yyy',
			// ]));

			// print_r($api->updateRecord('Contacts', 421637, [
			// 	'firstname' => 'xxx',
			// 	'lastname' => 'yy222y',
			// ]));
		}
	} catch (\Throwable $th) {
		$api->log('proxy_errors', $api->parserErrorResponse($th));
	}
} catch (\Throwable $th) {
	echo $th->__toString();
}
