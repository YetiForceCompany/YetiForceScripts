<?php
/**
 * Configuration file.
 * apiPath/apiSiteUrl must end with an `__CRM_URL__/webservice/`.
 * For CRM versions greater than 6.1.255, you need to add: RestApi , Portal. `__CRM_URL__/webservice/RestApi/`.
 * For CRM versions greater than 6.3, you need to add: WebserviceStandard , WebservicePremium. `__CRM_URL__/webservice/WebserviceStandard/`.
 *
 * @copyright YetiForce Sp. z o.o
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
return [
	'apiPath' => 'https://gitdeveloper.yetiforce.com/webservice/Portal/',
	'wsAppName' => 'portal',
	'wsAppPass' => 'portal',
	'wsApiKey' => 'VMUwRByXHSq1bLW485ikfvcC97P6gJsz',
	'wsUserName' => 'customer@yetiforce.com',
	'wsUserPass' => 'customer',
	// 'bruteForceDriver' => 'db',
	// 'bruteForceDayLimit' => 1000,
	// 'logDriver' => 'db',
	// 'dbHost' => 'localhost',
	// 'dbName' => 'api',
	// 'dbPort' => 3306,
	// 'dbUser' => 'api',
	// 'dbPass' => '',
];
