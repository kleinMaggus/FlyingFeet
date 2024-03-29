<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2011 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Asset;

use Assetic\Filter\FilterInterface;

/**
 * Represents an asset loaded from a file.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class FileAsset extends BaseAsset {
	private $source;

	/**
	 * Constructor.
	 *
	 * @param string $source     An absolute path
	 * @param array  $filters    An array of filters
	 * @param string $sourceRoot The source asset root directory
	 * @param string $sourcePath The source asset path
	 *
	 * @throws InvalidArgumentException If the supplied root doesn't match the source when guessing the path
	 */
	public function __construct($source, $filters = array(), $sourceRoot = null, $sourcePath = null) {
		if (null === $sourceRoot) {
			$sourceRoot = dirname($source);
			if (null === $sourcePath) {
				$sourcePath = basename($source);
			}
		} elseif (null === $sourcePath) {
			if (0 !== strpos($source, $sourceRoot)) {
				throw new \InvalidArgumentException(sprintf('The source "%s" is not in the root directory "%s"', $source, $sourceRoot));
			}

			$sourcePath = substr($source, strlen($sourceRoot) + 1);
		}

		$this->source = $source;

		parent::__construct($filters, $sourceRoot, $sourcePath);
	}

	public function load(FilterInterface $additionalFilter = null) {
		$this->doLoad(file_get_contents($this->source), $additionalFilter);
	}

	public function getLastModified() {
		return filemtime($this->source);
	}
}
