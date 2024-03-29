<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Loader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Loader\FileLoader;

/**
 * PhpFileLoader loads routes from a PHP file.
 *
 * The file must return a RouteCollection instance.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class PhpFileLoader extends FileLoader {
	/**
	 * Loads a PHP file.
	 *
	 * @param mixed  $file A PHP file path
	 * @param string $type The resource type
	 *
	 * @api
	 */
	public function load($file, $type = null) {
		// the loader variable is exposed to the included file below
		$loader = $this;

		$path = $this->locator->locate($file);
		$this->setCurrentDir(dirname($path));

		$collection = include $path;
		$collection->addResource(new FileResource($path));

		return $collection;
	}

	/**
	 * Returns true if this class supports the given resource.
	 *
	 * @param mixed  $resource A resource
	 * @param string $type     The resource type
	 *
	 * @return Boolean True if this class supports the given resource, false otherwise
	 *
	 * @api
	 */
	public function supports($resource, $type = null) {
		return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'php' === $type);
	}
}
