<?php
/**
 * @package    Fuel\Alias
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Alias\Providers;

use League\Container\ServiceProvider;

/**
 * Fuel ServiceProvider class for Alias
 *
 * @package Fuel\Alias
 *
 * @since 2.0
 */
class FuelServiceProvider extends ServiceProvider
{
	/**
	 * @var array
	 */
	protected $provides = ['alias'];

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Fuel\Alias\Manager')
			->withMethodCall('register');
	}
}
