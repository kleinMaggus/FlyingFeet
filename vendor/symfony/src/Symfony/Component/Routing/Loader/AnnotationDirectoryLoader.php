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

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Resource\DirectoryResource;

/**
 * AnnotationDirectoryLoader loads routing information from annotations set
 * on PHP classes and methods.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AnnotationDirectoryLoader extends AnnotationFileLoader {
	/**
	 * Loads from annotations from a directory.
	 *
	 * @param string $path A directory path
	 * @param string $type The resource type
	 *
	 * @return RouteCollection A RouteCollection instance
	 *
	 * @throws \InvalidArgumentException When the directory does not exist or its routes cannot be parsed
	 */
	public function load($path, $type = null) {
		$dir = $this->locator->locate($path);

		$collection = new RouteCollection();
		$collection->addResource(new DirectoryResource($dir, '/\.php$/'));
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
			if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
				continue;
			}

			if ($class = $this->findClass($file)) {
				$refl = new \ReflectionClass($class);
				if ($refl->isAbstract()) {
					continue;
				}

				$collection->addCollection($this->loader->load($class, $type));
			}
		}

		return $collection;
	}

	/**
	 * Returns true if this class supports the given resource.
	 *
	 * @param mixed  $resource A resource
	 * @param string $type     The resource type
	 *
	 * @return Boolean True if this class supports the given resource, false otherwise
	 */
	public function supports($resource, $type = null) {
		try {
			$path = $this->locator->locate($resource);
		} catch (\Exception $e) {
			return false;
		}

		return is_string($resource) && is_dir($path) && (!$type || 'annotation' === $type);
	}
}
