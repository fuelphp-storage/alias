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

use Closure;

class Resolver
{
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
		// Resolve replacement translations
		else
		{
			// Make sure namespace seperators are escaped
			$translation = str_replace('\\', '\\\\', $translation);

			// Resolve the replacement
			$class = preg_replace($this->regex, $translation, $alias);
		}

		// Check wether the class exists
		if ( ! $class or ! class_exists($class, true))
		{
			return false;
		}

		return $class;
	}

	/**
	 * Returns wether the resolver matches a given pattern and optional translation
	 *
	 * @param string          $pattern
	 * @param string|callable $translation
	 *
	 * @return  boolean  wether the resolver matches
	 */
	public function  matches($pattern, $translation = null)
	{
		if ($this->pattern !== $pattern or ($translation and $translation !== $this->translation))
		{
			return false;
		}

		return true;
	}
}
