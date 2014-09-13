<?php
/**
 * @package    Fuel\Alias
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Alias;

/**
 * Defines a common interface to allow aliases to be cached.
 * Eventually this will need to be replaced/updated with a PSR-6 implementation once the PSR is accepted.
 *
 * @package Fuel\Alias
 *
 * @author Fuel Development Team
 *
 * @since 2.0
 */
interface CacheInterface
{

	/**
	 * Returns true if the cache contains an entry for the given alias.
	 *
	 * @param string $alias
	 *
	 * @return bool
	 *
	 * @since 2.0
	 */
	public function has($alias);

	/**
	 * Gets the stored cache item for the given alias.
	 *
	 * @param string|null $alias If null then all items are returned.
	 *
	 * @return string|false False if the cache item does not exist
	 *
	 * @since 2.0
	 */
	public function get($alias = null);

	/**
	 * Add an item to the cache.
	 *
	 * @param string $alias
	 * @param string $class
	 *
	 * @return bool
	 *
	 * @since 2.0
	 */
	public function set($alias, $class);

	/**
	 * Remove an item from the cache.
	 *
	 * @param string $alias
	 *
	 * @return bool
	 *
	 * @since  2.0
	 */
	public function delete($alias);

}
