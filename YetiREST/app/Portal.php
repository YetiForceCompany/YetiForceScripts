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
			'orderField' => 'X-ROW-ORDER-FIELD',
			'order' => 'X-ROW-ORDER',
			'rawData' => 'X-RAW-DATA'
		],
		'listRelatedRecords' => [
			'rawData' => 'X-RAW-DATA',
			'offset' => 'X-ROW-OFFSET',
			'limit' => 'X-ROW-LIMIT',
			'fields' => 'X-FIELDS',
			'parentId' => 'X-PARENT-ID',
			'condition' => 'X-CONDITION',
		]
	];

	/**
	 * Login function.
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
	 * List modules.
	 *
	 * @return string[]
	 */
	public function listModules(): array
	{
		$return = $this->json('GET', 'Modules');
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * List methods of Yetiforce REST.
	 *
	 * @return string[]
	 */
	public function listMethods(): array
	{
		$return = $this->json('GET', 'Methods');
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * Privileges for module.
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
	 * Hierarchy for module.
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public function hierarchy(string $module): array
	{
		$return = $this->json('GET', "{$module}/Hierarchy");
		return $return['status'] ? $return['result'] : [];
	}

	/**
	 * List records for module.
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
	 * Related list records for module.
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
	 * Get record with id for module.
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
	 * Create new record for module.
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
	 * Save record with id for module update.
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
	 * Fields for module.
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
	 * Delete record with id for module.
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
