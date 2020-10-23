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
	private $config;
	/**
	 * The default configuration of GuzzleHttp.
	 *
	 * @var array
	 */
	private $options = [
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
	private $httpClient;

	/**
	 * init function.
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
	 * request function.
	 *
	 * @param string $method
	 * @param string $uri
	 * @param array  $options
	 *
	 * @return string
	 */
	protected function request(string $method, string $uri = '', array $options = []): string
	{
		$request = $this->httpClient->request($method, $uri, $options);
		return $request->getBody()->getContents();
	}

	/**
	 * json function.
	 *
	 * @param string $method
	 * @param string $uri
	 * @param array  $data
	 *
	 * @return array
	 */
	public function json(string $method, string $uri = '', array $data = []): array
	{
		return json_decode($this->request($method, $uri, [
			'json' => $data
		]), true);
	}

	/**
	 * login function.
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
	 * logout function.
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
}
