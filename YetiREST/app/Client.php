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
	/**
	 * Config variable.
	 *
	 * @var array
	 */
	protected $config;
	/**
	 * Debug variable.
	 *
	 * @var bool
	 */
	public $debug = false;
	/**
	 * Logs variable.
	 *
	 * @var array
	 */
	protected $logs = [];
	/**
	 * Errors variable.
	 *
	 * @var array
	 */
	protected $error = [];
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
	 *
	 * @return static
	 */
	public static function init(?array $config = null)
	{
		$loader = new static();
		$loader->config = $config ?? (include_once __DIR__ . '/../config.php');
		if (!empty($loader->config['options']) && \is_array($loader->config['options'])) {
			$loader->options = array_merge($loader->options, $loader->config['options']);
		}
		$caPathOrFile = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
		$loader->options['verify'] = \is_file($caPathOrFile) ? $caPathOrFile : false;
		$loader->options['base_uri'] = $loader->config['apiPath'] . 'webservice/';
		$loader->options['auth'] = [$loader->config['wsAppName'], $loader->config['wsAppPass']];
		$loader->options['headers']['x-api-key'] = $loader->config['wsApiKey'];
		$loader->httpClient = new \GuzzleHttp\Client($loader->options);
		return $loader;
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
			$response = $this->httpClient->request($method, $uri, $options);
			$body = $response->getBody()->getContents();
			if ($this->debug) {
				$this->logs[] = [
					'uri' => $uri,
					'method' => $method,
					'options' => $options,
					'statusCode' => $response->getStatusCode(),
					'reasonPhrase' => $response->getReasonPhrase(),
					'protocol' => $response->getProtocolVersion(),
					'headers' => array_map(function ($value) {
						return implode(', ', $value);
					}, $response->getHeaders()),
					'responseBody' => $body,
				];
			}
		} catch (\Throwable $th) {
			$this->error[] = array_merge(
				[
					'type' => 'httpClientException',
					'datetime' => date('Y-m-d H:i:s'),
					'uri' => $uri,
					'method' => $method,
					'message' => $th->getMessage(),
					'options' => $options,
				],
				$this->parserErrorResponse($th)
			);
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
	 *
	 * @return array
	 */
	public function json(string $method, string $uri = '', array $data = []): array
	{
		$return = json_decode($this->request($method, $uri, [
			'json' => $data
		]), true);
		if (isset($return['error'])) {
			$this->error[] = array_merge(
				[
					'type' => 'crmException',
					'datetime' => date('Y-m-d H:i:s'),
					'uri' => $uri,
					'method' => $method,
					'data' => $data,
				],
				$this->parserErrorResponse($return)
			);
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
	protected function parserErrorResponse($error): array
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
			if (method_exists($error, 'getResponse')) {
				$response = $error->getResponse();
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

	/**
	 * Get logs function.
	 *
	 * @return array
	 */
	public function getLogs(): array
	{
		return $this->logs;
	}

	/**
	 * Get errors function.
	 *
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->error;
	}
}
