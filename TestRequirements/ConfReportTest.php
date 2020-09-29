<?php
/**
 * Conf report class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
ob_start();
if (file_exists('include/main/WebUI.php')) {
	include_once 'include/main/WebUI.php';
} else {
	chdir(__DIR__ . '/../');
	if (file_exists('include/main/WebUI.php')) {
		include_once 'include/main/WebUI.php';
	} else {
		chdir(__DIR__ . '/../../');
		if (file_exists('include/main/WebUI.php')) {
			include_once 'include/main/WebUI.php';
		}
	}
}
$startTime = microtime(true);

\App\Utils\ConfReport::$sapi = PHP_SAPI === 'cli' ? 'cron' : 'www';

echo '<h2>Errors:</h2><pre>';

print_r(\App\Utils\ConfReport::getAllErrors());

echo' <hr/><h2>ConfReport:</h2>';

print_r(\App\Utils\ConfReport::getAll());

echo' </pre><hr/>' . \App\Log::getlastLogs(['error', 'warning']);

echo' <hr/>' . round(microtime(true) - $startTime, 5) . ' <hr/>';

file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . basename(__FILE__, '.php') . '_' . \App\Utils\ConfReport::$sapi . '.html', ob_get_contents());
