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

use Fuel\Dependency\ServiceProvider;

/**
 * FuelPHP ServiceProvider class for this package
 *
 * @package  Fuel\Alias
 *
 * @since  2.0
 */
class FuelServiceProvider extends ServiceProvider
{
	/**
	 * @var  array  list of service names provided by this provider
	 */
	public $provides = array('alias');

	/**
	 * Service provider definitions
	 */
	public function provide()
	{
		// \Fuel\Alias\Manager
		$this->registerSingleton('alias', function($container)
		{
			$manager = $container->resolve('Fuel\Alias\Manager');
			return $manager->register();
		});
	}
}
