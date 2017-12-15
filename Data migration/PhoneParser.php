<?php
/**
 * Script to convert the phone number for the new validator
 * @package YetiForce.Scripts
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
//chdir(__DIR__ . '/../');
require 'include/main/WebUI.php';
echo '<pre>';

App\User::setCurrentUserId(Users::getActiveAdminId());
$current_user = Users::getActiveAdminUser();
vglobal('current_user', $current_user);

$startTime = microtime(true);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('html_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 'On');
ini_set('output_buffering', 'Off');
AppConfig::iniSet('memory_limit', '2G');

class ParserNumber
{

	public static function process()
	{
		foreach (static::getFields() as $moduleId => $fields) {
			$moduleName = App\Module::getModuleName($moduleId);
			echo "Module: $moduleName";
			$queryGenerator = new \App\QueryGenerator($moduleName);
			$queryGenerator->setFields(['id']);
			$queryGenerator->setStateCondition('All');
			foreach ($fields as $field) {
				$queryGenerator->addCondition($field['fieldname'], '', 'ny', false);
			}
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			$i = $f = $sa = $s = 0;
			while ($id = $dataReader->readColumn(0)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
				foreach ($fields as $field) {
					if (!$recordModel->isEmpty($field['fieldname'] . '_extra')) {
						$s++;
						continue;
					}
					$data = static::getNumber($recordModel->get($field['fieldname']));
					if (!empty($data['base']) || !empty($data['extra'])) {
						$recordModel->set($field['fieldname'], $data['base']);
						$recordModel->set($field['fieldname'] . '_extra', $data['extra']);
						$f++;
					}
				}
				if ($recordModel->getPreviousValue()) {
					$recordModel->save();
					$sa++;
				}
				unset($recordModel);
				$i++;
			}
			echo " | Rows: $i | Fields: $f | Skipped: $s | Save: $sa" . PHP_EOL;
		}
	}

	public static function getNumber($text)
	{
		if ($text) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			$phoneNumberMatcher = $phoneUtil->findNumbers($text, strtoupper(\App\Language::getShortLanguageName()));
			foreach ($phoneNumberMatcher as $phoneNumber) {
				if ($rawNumber = $phoneNumber->rawString()) {
					$restOfTheString = str_replace($rawNumber, ' ', $text);
					$international = $phoneUtil->format($phoneNumber->number(), \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
					return ['base' => trim(str_replace(' ', '', $international)), 'extra' => trim($restOfTheString)];
				}
			}
			return ['base' => '', 'extra' => trim($text)];
		}
	}

	public static function getFields()
	{
		$modules = [];
		$fields = (new \App\Db\Query())
				->select(['columnname', 'fieldname', 'tablename', 'tabid'])->from('vtiger_field')
				->where(['uitype' => 11])->all();
		foreach ($fields as $field) {
			$modules[$field['tabid']][] = $field;
		}
		return $modules;
	}
}

//var_dump(ParserNumber::getNumber('tel (42) 25-32-287, tel/fax (42) 25-32-288'));

ParserNumber::process();

echo '<hr/>' . round(microtime(true) - $startTime, 5);
