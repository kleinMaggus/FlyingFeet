<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation;

/**
 * ParameterBag is a container for key/value pairs.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class ParameterBag {
	protected $parameters;

	/**
	 * Constructor.
	 *
	 * @param array $parameters An array of parameters
	 *
	 * @api
	 */
	public function __construct(array $parameters = array()) {
		$this->parameters = $parameters;
	}

	/**
	 * Returns the parameters.
	 *
	 * @return array An array of parameters
	 *
	 * @api
	 */
	public function all() {
		return $this->parameters;
	}

	/**
	 * Returns the parameter keys.
	 *
	 * @return array An array of parameter keys
	 *
	 * @api
	 */
	public function keys() {
		return array_keys($this->parameters);
	}

	/**
	 * Replaces the current parameters by a new set.
	 *
	 * @param array $parameters An array of parameters
	 *
	 * @api
	 */
	public function replace(array $parameters = array()) {
		$this->parameters = $parameters;
	}

	/**
	 * Adds parameters.
	 *
	 * @param array $parameters An array of parameters
	 *
	 * @api
	 */
	public function add(array $parameters = array()) {
		$this->parameters = array_replace($this->parameters, $parameters);
	}

	/**
	 * Returns a parameter by name.
	 *
	 * @param string  $path    The key
	 * @param mixed   $default The default value
	 * @param boolean $deep
	 *
	 * @api
	 */
	public function get($path, $default = null, $deep = false) {
		if (!$deep || false === $pos = strpos($path, '[')) {
			return array_key_exists($path, $this->parameters) ? $this->parameters[$path] : $default;
		}

		$root = substr($path, 0, $pos);
		if (!array_key_exists($root, $this->parameters)) {
			return $default;
		}

		$value = $this->parameters[$root];
		$currentKey = null;
		for ($i = $pos, $c = strlen($path); $i < $c; $i++) {
			$char = $path[$i];

			if ('[' === $char) {
				if (null !== $currentKey) {
					throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "[" at position %d.', $i));
				}

				$currentKey = '';
			} else if (']' === $char) {
				if (null === $currentKey) {
					throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "]" at position %d.', $i));
				}

				if (!is_array($value) || !array_key_exists($currentKey, $value)) {
					return $default;
				}

				$value = $value[$currentKey];
				$currentKey = null;
			} else {
				if (null === $currentKey) {
					throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "%s" at position %d.', $char, $i));
				}

				$currentKey .= $char;
			}
		}

		if (null !== $currentKey) {
			throw new \InvalidArgumentException(sprintf('Malformed path. Path must end with "]".'));
		}

		return $value;
	}

	/**
	 * Sets a parameter by name.
	 *
	 * @param string $key   The key
	 * @param mixed  $value The value
	 *
	 * @api
	 */
	public function set($key, $value) {
		$this->parameters[$key] = $value;
	}

	/**
	 * Returns true if the parameter is defined.
	 *
	 * @param string $key The key
	 *
	 * @return Boolean true if the parameter exists, false otherwise
	 *
	 * @api
	 */
	public function has($key) {
		return array_key_exists($key, $this->parameters);
	}

	/**
	 * Removes a parameter.
	 *
	 * @param string $key The key
	 *
	 * @api
	 */
	public function remove($key) {
		unset($this->parameters[$key]);
	}

	/**
	 * Returns the alphabetic characters of the parameter value.
	 *
	 * @param string  $key     The parameter key
	 * @param mixed   $default The default value
	 * @param boolean $deep
	 *
	 * @return string The filtered value
	 *
	 * @api
	 */
	public function getAlpha($key, $default = '', $deep = false) {
		return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default, $deep));
	}

	/**
	 * Returns the alphabetic characters and digits of the parameter value.
	 *
	 * @param string  $key     The parameter key
	 * @param mixed   $default The default value
	 * @param boolean $deep
	 *
	 * @return string The filtered value
	 *
	 * @api
	 */
	public function getAlnum($key, $default = '', $deep = false) {
		return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default, $deep));
	}

	/**
	 * Returns the digits of the parameter value.
	 *
	 * @param string  $key     The parameter key
	 * @param mixed   $default The default value
	 * @param boolean $deep
	 *
	 * @return string The filtered value
	 *
	 * @api
	 */
	public function getDigits($key, $default = '', $deep = false) {
		return preg_replace('/[^[:digit:]]/', '', $this->get($key, $default, $deep));
	}

	/**
	 * Returns the parameter value converted to integer.
	 *
	 * @param string  $key     The parameter key
	 * @param mixed   $default The default value
	 * @param boolean $deep
	 *
	 * @return string The filtered value
	 *
	 * @api
	 */
	public function getInt($key, $default = 0, $deep = false) {
		return (int) $this->get($key, $default, $deep);
	}
}
