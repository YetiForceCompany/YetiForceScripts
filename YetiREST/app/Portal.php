<?php
/**
 * API Portal container file.
 *
 * @copyright YetiForce Sp. z o.o
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * API Portal container class.
 */
class Portal extends Client
{
	/** @var array Headers mapping. */
	protected static $headers = [
		'listRecords' => [
			'condition' => 'X-CONDITION',
			'offset' => 'X-ROW-OFFSET',
			'limit' => 'X-ROW-LIMIT',
			'fields' => 'X-FIELDS',
			'order' => 'X-ORDER-BY',
			'rawData' => 'X-RAW-DATA',
		],
		'listRelatedRecords' => [
			'rawData' => 'X-RAW-DATA',
			'offset' => 'X-ROW-OFFSET',
			'limit' => 'X-ROW-LIMIT',
			'fields' => 'X-FIELDS',
			'parentId' => 'X-PARENT-ID',
			'condition' => 'X-CONDITION',
		],
	];

	/**
	 * Login function.
	 *
	 * @see https://doc.yetiforce.com/api/#/Users/Api\RestApi\Users\Login::post
	 *
	 * @param string $userName
	 * @param string $password
	 *
	 * @return array|null
	 */
	public function login(string $userName = '', string $password = ''): ?array
	{
		if (!$userName) {
			$userName = $this->config['wsUserName'];
			$password = $this->config['wsUserPass'];
		}
		$return = $this->json('POST', 'Users/Login', [
			'userName' => $userName,
			'password' => $password,
		]);
		if (1 == $return['status']) {
			$options = $this->options;
			$options['headers']['x-token'] = $return['result']['token'];
			$this->httpClient = new \GuzzleHttp\Client($options);
			return $return['result'];
		}
		return null;
	}

	/**
	 * Logout function.
	 *
	 * @see https://doc.yetiforce.com/api/#/Users/Api\RestApi\Users\Logout::put
	 *
	 * @return bool
	 */
	public function logout(): bool
	{
		$return = $this->json('PUT', 'Users/Logout');
		if (1 == $return['status']) {
			$options = $this->options;
			unset($options['headers']['x-token']);
			$this->httpClient = new \GuzzleHttp\Client($options);
			return false;
		}
		return false;
	}

	/**
	 * Get modules list.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseAction/Api\RestApi\BaseAction\Modules::get
	 *
	 * @return string[]
	 */
	public function listModules(): array
	{
		$return = $this->json('GET', 'Modules');
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Get privileges.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseModule/Api\RestApi\BaseModule\Privileges::get
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public function privileges(string $module): array
	{
		$return = $this->json('GET', "{$module}/Privileges");
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * List records.
	 *
	 * @see  https://doc.yetiforce.com/api/#/BaseModule/Api\RestApi\BaseModule\RecordsList::get
	 *
	 * @param string $module
	 * @param array  $params
	 *
	 * @return array
	 */
	public function listRecords(string $module, array $params): array
	{
		$return = $this->json('GET', "{$module}/RecordsList", [], $this->parseHeaders('listRecords', $params));
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * List related records.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseModule/Api\RestApi\BaseModule\RecordRelatedList::get
	 *
	 * @param string $module
	 * @param array  $params
	 *
	 * @return array
	 */
	public function listRelatedRecords(string $module, array $params): array
	{
		$return = $this->json('GET', "{$module}/RecordRelatedList", [], $this->parseHeaders('listRelatedRecords', $params));
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Get record details.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseModule/getRecord
	 *
	 * @param string $module
	 * @param int    $id
	 *
	 * @return array
	 */
	public function getRecord(string $module, int $id): array
	{
		$return = $this->json('GET', "{$module}/Record/{$id}");
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Create new record.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseModule/Api\RestApi\BaseModule\Record::post
	 *
	 * @param string $module
	 * @param array  $params
	 *
	 * @return array
	 */
	public function createRecord(string $module, array $params): array
	{
		$return = $this->json('POST', "{$module}/Record", $params);
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Update record.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseModule/Api\RestApi\BaseModule\Record::put
	 *
	 * @param string $module
	 * @param int    $id
	 * @param array  $params
	 *
	 * @return array
	 */
	public function updateRecord(string $module, int $id, array $params): array
	{
		$return = $this->json('PUT', "{$module}/Record/{$id}", $params);
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Get fields in module.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseModule/Api\RestApi\BaseModule\Fields::get
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public function fields(string $module): array
	{
		$return = $this->json('GET', "{$module}/Fields");
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Delete record.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseModule/Api\RestApi\BaseModule\Record::delete
	 *
	 * @param string $module
	 * @param int    $id
	 *
	 * @return array
	 */
	public function deleteRecord(string $module, int $id): array
	{
		$return = $this->json('DELETE', "{$module}/Record/{$id}");
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Get record history.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseModule/Api\RestApi\BaseModule\RecordHistory::get
	 *
	 * @param string $module
	 * @param int    $id
	 *
	 * @return array
	 */
	public function recordHistory(string $module, int $id): array
	{
		$return = $this->json('GET', "{$module}/RecordHistory/{$id}");
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Get related modules.
	 *
	 * @see https://doc.yetiforce.com/api/#/BaseModule/Api\RestApi\BaseModule\RelatedModules::get
	 *
	 * @param string $module
	 * @param int    $id
	 *
	 * @return array
	 */
	public function relatedModules(string $module, int $id): array
	{
		$return = $this->json('GET', "{$module}/RelatedModules/{$id}");
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Parse headers.
	 *
	 * @param string $method
	 * @param array  $params
	 *
	 * @return string[]
	 */
	protected function parseHeaders(string $method, array $params): array
	{
		$headers = [];
		foreach ($params as $key => $value) {
			if (isset(static::$headers[$method][$key])) {
				$headers[static::$headers[$method][$key]] = \is_array($value) ? json_encode($value) : $value;
			} else {
				throw new \Exception('Parameter not found: ' . $key);
			}
		}
		return $headers;
	}
}
