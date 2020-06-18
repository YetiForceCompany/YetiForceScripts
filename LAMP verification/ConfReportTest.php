<?php

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

$return = '<pre>';

$return .= print_r(\App\Utils\ConfReport::getAllErrors(), true);

$return .= ' <hr/>';
$return .= print_r(\App\Utils\ConfReport::getAll(), true);

$return .= ' <hr/>' . \App\Log::getlastLogs(['error', 'warning']);

$return .= ' <hr/>' . round(microtime(true) - $startTime, 5) . ' <hr/>';

file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . basename(__FILE__, '.php') . '_' . \App\Utils\ConfReport::$sapi . '.html', $return);

echo $return;
