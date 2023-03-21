<?php
/**
 * Loader file.
 *
 * @copyright YetiForce S.A.
 * @license   MIT
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @version 1.2
 */

namespace App;

/**
 * Cache class.
 */
class Cache
{
	const LONG = 3600;
	const MEDIUM = 300;
	const SHORT = 60;
	private static $apcuEnabled;

	/**
	 * Is apcu is available.
	 *
	 * @return bool
	 */
	public static function isApcu()
	{
		if (isset(self::$apcuEnabled)) {
			return self::$apcuEnabled;
		}
		return self::$apcuEnabled = (\function_exists('apcu_enabled') && apcu_enabled());
	}

	/**
	 * Returns a cache item representing the specified key.
	 *
	 * @param array|string $key Cache ID
	 *
	 * @return array|string|null
	 */
	public static function get($key)
	{
		if (self::isApcu()) {
			return apcu_fetch($key);
		}
		return null;
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param array|string $key Cache ID
	 *
	 * @return bool|null
	 */
	public static function has($key)
	{
		if (self::isApcu()) {
			return apcu_exists($key);
		}
		return null;
	}

	/**
	 * Cache save.
	 *
	 * @param string       $key      Cache ID
	 * @param array|string $value    Data to store
	 * @param int          $duration Cache TTL (in seconds)
	 *
	 * @return array|bool|null
	 */
	public static function save($key, $value, $duration)
	{
		if (self::isApcu()) {
			return apcu_store($key, $value, $duration);
		}
		return null;
	}

	/**
	 * Removes the item from the cache.
	 *
	 * @param array|string $key Cache ID
	 *
	 * @return bool|null
	 */
	public static function delete($key)
	{
		if (self::isApcu()) {
			return apcu_delete($key);
		}
		return null;
	}

	/**
	 * Deletes all items in the cache.
	 *
	 * @return bool|null
	 */
	public static function clear()
	{
		if (self::isApcu()) {
			return apcu_clear_cache();
		}
		return null;
	}
}
