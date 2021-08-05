<?php
/**
 * Disable 2FA or LDAP auth of any user.
 *
 * @package   YetiForce
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @version   >= 6.0.0
 */
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

/**
 * Set variable $userId or $userName and run the script.
 */
$userId = 1;
$userName = '';

try {
	if ($userName) {
		$userId = \App\User::getUserIdByName($userName);
	}
	$userRecordModel = Users_Record_Model::getInstanceById($userId, 'Users');
	$userRecordModel->set('login_method', 'PLL_PASSWORD');
	$userRecordModel->set('authy_secret_totp', '');
	$userRecordModel->set('authy_methods', '');
	$userRecordModel->save();
} catch (\Throwable $th) {
	echo $th->__toString();
}

echo '<pre>';
echo 'Login: ' . $userRecordModel->get('user_name') . PHP_EOL;
echo 'Full name: ' . $userRecordModel->getName() . PHP_EOL;
