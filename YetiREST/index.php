<?php
/**
 * Main YetiForce REST interface file.
 *
 * @copyright YetiForce S.A.
 * @license   MIT
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @version 1.2
 */
require __DIR__ . '/vendor/autoload.php';
// disable output buffering
ob_implicit_flush();
echo '<pre>';
/*
Debug:

ini_set('html_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 'On');
error_reporting(E_ALL);
echo '<pre>';
*/

try {
	// $api = new \App\Portal([
	// 	'apiPath' => 'https://gitdeveloper.yetiforce.com/',
	// 	'wsAppName' => 'portal',
	// 	'wsAppPass' => 'portal',
	// 	'wsApiKey' => 'VMUwRByXHSq1bLW485ikfvcC97P6gJsz',
	// 	'wsUserName' => 'demo@yetiforce.com',
	// 	'wsUserPass' => 'demo',
	// ]);
	$api = new \App\Portal();
	$api->debug = true;
	try {
		$api->init();
		if ($login = $api->login()) {
			// Show list modules.
			print_r($api->listModules());

			// Show record details
			print_r($api->getRecord('OfficeLocations', 355));

			// Show records list
			print_r($api->listRecords('OfficeLocations', []));

			// Create new record
			print_r($api->createRecord('OfficeLocations', [
				'name' => 'xxx',
				'ulica' => 'yyy',
				'miasto' => 'rrr',
			]));

			// Get fields in module.
			print_r($api->fields('OfficeLocations', ['response' => ['inventory', 'blocks', 'privileges', 'dbStructure', 'queryOperators']]));

			// Create new record
			print_r($api->createRecord('Contacts', [
				'firstname' => 'xxx',
				'lastname' => 'yyy',
			]));

			// Update exist record
			print_r($api->updateRecord('Contacts', 355, [
				'firstname' => 'xxx',
				'lastname' => 'yy222y',
			]));

			// Get related modules.
			print_r($api->relatedModules('Contacts', 355));

			// List related records.
			print_r($api->listRelatedRecords('Contacts', 355, 'Project', [
				'rawData' => 1,
				'limit' => 5,
			]));

			// Delete record.
			print_r($api->deleteRecord('Contacts', 355));

			// Get record history.
			print_r($api->recordHistory('Contacts', 355));

			$api->logout();
		}
	} catch (\Throwable $th) {
		$api->log('proxy_errors', $api->parserErrorResponse($th));
		echo $th->__toString();
	}
} catch (\Throwable $th) {
	echo $th->__toString();
}
