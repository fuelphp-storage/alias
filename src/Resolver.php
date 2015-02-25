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

class Resolver
{
	use ObjectTester;

	/**
	 * @var string
	 */
	protected $regex;

	/**
	 * @var string $pattern
	 */
	protected $pattern;

	/**
	 * @var string|callable $translation
	 */
	protected $translation;

	/**
	 * @var boolean
	 */
	protected $active = false;

	/**
	 * @param string          $pattern
	 * @param string|callable $translation
	 */
	public function __construct($pattern, $translation)
	{
		$regex = preg_quote($pattern, '#');
		$this->regex = '#^'.str_replace('\\*', '(.*)', $regex).'$#uD';
		$this->pattern = $pattern;
		$this->translation = $translation;
	}

	/**
	 * Resolves an alias
	 *
	 * @param string $alias
	 *
	 * @return string|boolean
	 */
	public function resolve($alias)
	{
		// Check wether the alias matches the pattern
		if ( ! preg_match($this->regex, $alias, $matches))
		{
			return false;
		}

		// Get the translation
		$translation = $this->translation;

		if (strpos($translation, '$') === false)
		{
			$class = $translation;
		}
		else
		{
			// Make sure namespace seperators are escaped
			$translation = str_replace('\\', '\\\\', $translation);

			// Resolve the replacement
			$class = preg_replace($this->regex, $translation, $alias);
		}

		// Check wether the class exists
		if ($class and $this->objectExists($class, true))
		{
			return $class;
		}

		return false;
	}

	/**
	 * Checks whether the resolver matches a given pattern and optional translation
	 *
	 * @param string          $pattern
	 * @param string|callable $translation
	 *
	 * @return boolean
	 */
	public function matches($pattern, $translation = null)
	{
		return $this->pattern === $pattern and (!$translation or $translation === $this->translation);
	}
}
