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

class Cache
{
	/**
	 * @var  string  $path  cache path
	 */
	public $path;

	/**
	 * @var  array  $aliases  aliases to cache
	 */
	protected $aliases = array();

	/**
	 * @var  string  $format  cache format
	 */
	protected $format = 'alias';

	/**
	 * @var  Fuel\Alias\Manager  alias manager
	 */
	protected $manager;

	/**
	 * Constructor
	 *
	 * @param  string   $path      path to cache file
	 */
	public function __construct($path)
	{
		$this->setPath($path);
	}

	/**
	 * Set the formatting type.
	 *
	 * @param   string  $format  cache format
	 * @return  $this
	 * @throws  InvalidArgumentException
	 */
	public function format($format)
	{
		if ( ! method_exists($this, 'render'.ucfirst($format)))
		{
			throw new \InvalidArgumentException('Fuel\\Alias\\Cache rendering method "'.$format.'" does not exist.');
		}

		$this->format = $format;

		return $this;
	}

	/**
	 * Set the cache path
	 *
	 * @param   string  $path  cache path
	 * @return  $this
	 */
	public function setPath($path)
	{
		// ensure a php extension
		if (substr($path, -4) !== '.php')
		{
			$path .= '.php';
		}

		$this->path = $path;

		return $this;
	}

	/**
	 * Set the alias manager.
	 *
	 * @param   Fuel\Alias\Manager  $manager
	 * @return  $this
	 */
	public function setManager(Manager $manager)
	{
		$this->manager = $manager;

		return $this;
	}

	/**
	 * Prepares an alias to be cached
	 *
	 * @param   string  $original  original class name
	 * @param   string  $alias     class alias
	 * @return  $this
	 */
	public function cache($original, $alias)
	{
		$this->aliases[] = array($original, $alias);

		return $this;
	}

	/**
	 * Loads the alias cache when available
	 *
	 * @return  $this
	 */
	public function load()
	{
		if (file_exists($this->path) and is_file($this->path))
		{
			$manager = $this->manager;
			include $this->path;
		}

		return $this;
	}

	/**
	 * Read the cache file and return it's contents.
	 * Will return the template for a new file if not
	 * available.
	 *
	 * @return  string  cache file contents
	 */
	public function read()
	{
		$path = $this->path;

		if ( ! file_exists($path) or ! is_file($path))
		{
			$path = __DIR__.'/../../../resources/cache.template.php';
		}

		return file_get_contents($path);
	}

	/**
	 * Update the cache file with new aliases
	 *
	 * @return  void
	 */
	public function update()
	{
		// Fetch current cache as string
		$contents = $this->read();

		// format the render method
		$method = 'render'.ucfirst($this->format);

		// Get the formatted contents
		$contents = $this->{$method}($contents);

		$this->write($contents);
	}

	/**
	 * Delete the cache file when exists.
	 *
	 * @return  boolean  delete success
	 */
	public function delete()
	{
		if (file_exists($this->path) and is_file($this->path))
		{
			return unlink($this->path);
		}

		return true;
	}

	/**
	 * Writes the cache to disc.
	 *
	 * @return  void
	 */
	public function write($contents)
	{
		$dir = pathinfo($this->path, PATHINFO_DIRNAME);

		if ( ! file_exists($dir))
		{
			mkdir($dir, 0777, true);
		}

		file_put_contents($this->path, $contents);
	}

	/**
	 * Format an alias for caching
	 *
	 * @param   array   $contents  rendered contents
	 * @return  string  formatted class aliases
	 */
	protected function renderClass($contents)
	{
		foreach ($this->aliases as $entry)
		{
			$entry = array_map(function($class) {
				return str_replace('\\\\', '\\', $class);
			}, $entry);

			list($class, $alias) = $entry;

			// Seperate the namespace from the class
			$segments = explode('\\', $alias);
			$alias = array_pop($segments);
			$namespace = join('\\', $segments);
			$contents .= <<<FORMATTED

namespace {$namespace} { class {$alias} extends \\{$class} {} }
FORMATTED;
		}

		return $contents;
	}

	/**
	 * Format an alias for caching
	 *
	 * @param   array   $contents  rendered contents
	 * @return  string  formatted class aliases
	 */
	protected function renderAlias($contents)
	{
		foreach ($this->aliases as $entry)
		{
			list($class, $alias) = $entry;

			$contents .= <<<FORMATTED

class_alias('{$class}', '{$alias}');
FORMATTED;
		}

		return $contents;
	}

	/**
	 * Format an alias for caching
	 *
	 * @return  string  formatted class aliases
	 */
	protected function renderUnwind($contents)
	{
		// Strip old code.
		if (stripos($contents, '$manager') !== false)
		{
			$parts = explode('$manager', $contents);

			$contents = trim(reset($parts));
		}

		$entries = '';

		foreach ($this->aliases as $entry)
		{
			list($class, $alias) = $entry;

			$entries .= <<<FORMATTED

	'{$class}' => '{$alias}',
FORMATTED;
		}

		return <<<CONTENTS
{$contents}
\$manager->alias(array({$entries}
));
CONTENTS;
	}

	/**
	 * Register the shutdown function.
	 *
	 * @return  $this
	 */
	public function register()
	{
		$shutdown_function = array($this, 'update');
		register_shutdown_function($shutdown_function);

		return $this;
	}
}
