<?php
/**
 * Loader file.
 *
 * @copyright YetiForce Sp. z o.o
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Loader class.
 */
class Client
{
	/** @var array Config variable. */
	protected $config;
	/** @var bool Debug variable. */
	public $debug = false;
	/** @var array Logs variable. */
	protected $logPath = __DIR__ . '/../logs/';
	/** @var array Logs variable. */
	protected $logs = [];
	/** @var array Errors variable. */
	protected $error = [];
	/** @var \PDO Errors variable. */
	protected $db;
	/**
	 * The default configuration of GuzzleHttp.
	 *
	 * @var array
	 */
	protected $options = [
		'headers' => [
			'User-Agent' => 'YetiForceRestApi',
		],
		'timeout' => 10,
		'connect_timeout' => 2,
	];
	/**
	 * GuzzleHttp.
	 *
	 * @var \GuzzleHttp\Client
	 */
	protected $httpClient;

	/**
	 * Init function.
	 *
	 *	$api = Client::init([
	 *	'apiPath' => '',
	 *	'wsAppName' => '',
	 *	'wsAppPass' => '',
	 *	'wsApiKey' => '',
	 *	'wsUserName' => '',
	 *	'wsUserPass' => '',
	 * ]);
	 *
	 *	$api = Client::init();
	 *
	 * @param array|null $config
	 */
	public function __construct(?array $config = null)
	{
		$this->config = $config ?? (include_once __DIR__ . '/../config.php');
		if (!empty($this->config['options']) && \is_array($this->config['options'])) {
			$this->options = array_merge($this->options, $this->config['options']);
		}
		$this->debug = $this->config['debug'] ?? false;
		$this->config['logDriver'] = $this->config['logDriver'] ?? 'file';
		if (('db' === $this->config['logDriver'] || 'db' === ($this->config['bruteForceDriver'] ?? '')) && $this->config['dbHost'] && $this->config['dbName']) {
			$this->db = new \PDO(
				"mysql:host={$this->config['dbHost']};dbname={$this->config['dbName']};port={$this->config['dbPort']};charset=utf8",
				$this->config['dbUser'],
				$this->config['dbPass']
			);
			$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
		}
	}

	/**
	 * Driver initialization.
	 *
	 * @return void
	 */
	public function init(): void
	{
		$this->checkBruteForce();
		if (!isset($this->options['verify'])) {
			$caPathOrFile = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
			$this->options['verify'] = \is_file($caPathOrFile) ? $caPathOrFile : false;
		}
		$this->options['base_uri'] = $this->config['apiPath'];
		$this->options['auth'] = [$this->config['wsAppName'], $this->config['wsAppPass']];
		$this->options['headers']['x-api-key'] = $this->config['wsApiKey'];
		$this->httpClient = new \GuzzleHttp\Client($this->options);
	}

	/**
	 * Request function.
	 *
	 * @param string $method
	 * @param string $uri
	 * @param array  $options
	 *
	 * @return string
	 */
	protected function request(string $method, string $uri = '', array $options = [])
	{
		try {
			$startTime = microtime(true);
			$response = $this->httpClient->request($method, $uri, $options);
			$body = $response->getBody()->getContents();
			if ($this->debug) {
				$this->log('logs', [
					'requestTime' => round(microtime(true) - $startTime, 2),
					'uri' => $uri,
					'method' => $method,
					'options' => $options,
					'code' => $response->getStatusCode(),
					'reasonPhrase' => $response->getReasonPhrase(),
					'protocol' => $response->getProtocolVersion(),
					'headers' => array_map(function ($value) {
						return implode(', ', $value);
					}, $response->getHeaders()),
					'responseBody' => $body,
				]);
			}
		} catch (\Throwable $th) {
			// echo $th->__toString();
			$this->log('errors', array_merge(
				[
					'type' => 'httpClientException',
					'uri' => $uri,
					'method' => $method,
					'message' => $th->getMessage(),
					'options' => $options,
				],
				$this->parserErrorResponse($th)
			));
			throw $th;
		}
		return $body;
	}

	/**
	 * Json function.
	 *
	 * @param string $method
	 * @param string $uri
	 * @param array  $data
	 * @param array  $headers
	 *
	 * @return array
	 */
	public function json(string $method, string $uri = '', array $data = [], array $headers = []): array
	{
		$return = json_decode($this->request($method, $uri, [
			'json' => $data,
			'headers' => $headers,
		]), true);
		if (isset($return['error'])) {
			$this->log('errors', array_merge(
				[
					'type' => 'crmException',
					'uri' => $uri,
					'method' => $method,
					'data' => $data,
				],
				$this->parserErrorResponse($return)
			));
			throw new \Exception($return['error']['message'], $return['error']['code'] ?? 500);
		}
		return $return;
	}

	/**
	 * Parser error response.
	 *
	 * @param array|\GuzzleHttp\Exception\ClientException $error
	 * @param string                                      $uri
	 * @param array                                       $options
	 *
	 * @return array
	 */
	public function parserErrorResponse($error): array
	{
		$return = [];
		if (\is_object($error)) {
			$return = [
				'code' => $error->getCode(),
				'message' => $error->getMessage(),
			];
			if (method_exists($error, 'getRequest')) {
				$return['method'] = $error->getRequest()->getMethod();
				$return['protocol'] = $error->getRequest()->getProtocolVersion();
			}
			if (method_exists($error, 'getResponse') && ($response = $error->getResponse())) {
				$body = $response->getBody()->getContents();
				$return = [
					'code' => $response->getStatusCode(),
					'message' => $response->getReasonPhrase(),
					'headers' => array_map(function ($value) {
						return implode(', ', $value);
					}, $response->getHeaders()),
					'responseBody' => $body,
				];
				if (0 === strpos($body, '{"')) {
					$error = json_decode($body, true);
					if (isset($error['error'])) {
						$return['type'] = 'crmException';
					}
				}
			}
		}
		if (\is_array($error)) {
			foreach (['message', 'code', 'file', 'line', 'backtrace', 'previous'] as $key) {
				if (isset($error['error'][$key])) {
					$return[$key] = $error['error'][$key];
				}
			}
		}
		return $return;
	}

	public function log(string $type, array $params)
	{
		$isError = 'errors' === $type || 'proxy_errors' === $type;
		$isProxy = 0 === strpos($type, 'proxy_');
		if ('db' === $this->config['logDriver']) {
			$data = [
				'datetime' => date('Y-m-d H:i:s'),
				'code' => $params['code'] ?? 0,
				'method' => $params['method'] ?? '',
				'uri' => $params['uri'] ?? '',
			];
			if ($isProxy) {
				$data['ip'] = $_SERVER['REMOTE_ADDR'];
			}
			if ($isError) {
				$data['type'] = $params['type'] ?? '';
				$data['message'] = mb_substr($params['message'] ?? '', 0, 255, 'UTF-8');
			} else {
				$data['reason_phrase'] = $params['reasonPhrase'] ?? '';
				$data['request_time'] = $params['requestTime'] ?? '';
			}
			$params['$_REQUEST'] = print_r($_REQUEST, true);
			$params['$_SERVER'] = print_r($_SERVER, true);
			unset($params['code'], $params['message'], $params['reasonPhrase'], $params['uri'],  $params['requestTime']);
			$data['params'] = print_r($params, true);
			$columns = implode('`,`', array_keys($data));
			$values = implode(',:', array_keys($data));
			$sth = $this->db->prepare("INSERT INTO `{$type}` (`{$columns}`) VALUES (:{$values})");
			foreach ($data as $key => $value) {
				$sth->bindValue(':' . $key, $value);
			}
			$sth->execute();
		} else {
			if ($isError) {
				if (isset($params['type'],$params['method'],$params['uri'])) {
					$logRow = date('H:i:s') . " [{$params['type']}] |{$params['code']}| {$params['message']} | {$params['method']}] {$params['uri']}" . PHP_EOL;
				} else {
					$logRow = date('H:i:s') . " {$params['code']}| {$params['message']}" . PHP_EOL;
				}
			} else {
				$logRow = date('H:i:s') . " [{$params['method']}] {$params['uri']} | {$params['code']} - {$params['reasonPhrase']} [{$params['requestTime']}s]" . PHP_EOL;
			}
			unset($params['type'], $params['code'], $params['message'], $params['reasonPhrase'], $params['uri'], $params['method'], $params['requestTime']);
			if ($params) {
				$logRow .= print_r($params, true) . PHP_EOL;
			}
			if ($isProxy) {
				$logRow .= 'input:' . file_get_contents('php://input') . PHP_EOL;
				if ($_REQUEST) {
					$logRow .= '$_REQUEST: ' . print_r($_REQUEST, true) . PHP_EOL;
				}
			}
			$logRow .= '$_SERVER:' . print_r($_SERVER, true);
			$logRow .= str_repeat('=', 100) . PHP_EOL;
			file_put_contents($this->logPath . date('Y-m-d') . '.log', $logRow, FILE_APPEND);
			if ($isError) {
				$this->error[] = $logRow;
			} else {
				$this->logs[] = $logRow;
			}
		}
	}

	public function checkBruteForce()
	{
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		if (empty($ip) || empty($this->config['bruteForceDayLimit']) || (!empty($this->config['bruteForceTrustedIp']) && \in_array($ip, $this->config['bruteForceTrustedIp']))) {
			return true;
		}
		if ('db' === $this->config['bruteForceDriver']) {
			$sth = $this->db->prepare('SELECT * FROM `bruteforce` WHERE `ip` = :ip');
			$sth->execute([':ip' => $ip]);
			$row = $sth->fetch(\PDO::FETCH_ASSOC);
			if (empty($row)) {
				$statement = $this->db->prepare('INSERT INTO `bruteforce` (`ip`,`last_request`) VALUES (:ip,:last_request)');
				$statement->bindValue(':ip', $ip);
				$statement->bindValue(':last_request', date('Y-m-d H:i:s'));
				$statement->execute();
			} else {
				$statement = $this->db->prepare('UPDATE `bruteforce` SET `last_request`=:last_request,`counter`=:counter WHERE `ip` = :ip');
				$statement->bindValue(':ip', $ip);
				$statement->bindValue(':last_request', date('Y-m-d H:i:s'));
				if (date('Y-m-d', strtotime($row['last_request'])) === date('Y-m-d')) {
					$statement->bindValue(':counter', (int) $row['counter'] + 1);
				} else {
					$statement->bindValue(':counter', 1);
				}
				$statement->execute();
				if ((int) $row['counter'] > (int) $this->config['bruteForceDayLimit']) {
					throw new \Exception('Day limit exceeded | ' . $ip, 1100);
				}
			}
		} elseif ('apcu' === $this->config['bruteForceDriver']) {
			if (!\App\Cache::isApcu()) {
				throw new \Exception('APCu is not working', 1101);
			}
			$cacheKay = ($this->config['bruteForceApcuKey'] ?? 'YetiForceRestApi_') . $ip;
			if (\App\Cache::has($cacheKay)) {
				$row = \App\Cache::get($cacheKay);
				if (date('Y-m-d', strtotime($row['last_request'])) === date('Y-m-d')) {
					$row['counter'] = $row['counter'] + 1;
				} else {
					$row['counter'] = 1;
				}
				$row['last_request'] = date('Y-m-d H:i:s');
				\App\Cache::save($cacheKay, $row, 0);
				if ($row['counter'] > (int) $this->config['bruteForceDayLimit']) {
					throw new \Exception('Day limit exceeded | ' . $ip, 1100);
				}
			} else {
				\App\Cache::save($cacheKay, [
					'last_request' => date('Y-m-d H:i:s'),
					'counter' => 1,
				], 0);
			}
		}
	}
}
