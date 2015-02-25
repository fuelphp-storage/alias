<?php
/**
 * @package    Fuel\Alias
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Alias;

trait ObjectTester
{
	/**
	 * Checks various object types for existence
	 */
	protected function objectExists($object, $autoload = true)
	{
		return class_exists($object, $autoload) or
			interface_exists($object, $autoload) or
			trait_exists($object, $autoload);
	}
}
