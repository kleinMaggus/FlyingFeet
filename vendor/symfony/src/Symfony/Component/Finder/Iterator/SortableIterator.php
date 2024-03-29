<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Finder\Iterator;

/**
 * SortableIterator applies a sort on a given Iterator.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SortableIterator implements \IteratorAggregate {
	const SORT_BY_NAME = 1;
	const SORT_BY_TYPE = 2;

	private $iterator;
	private $sort;

	/**
	 * Constructor.
	 *
	 * @param \Traversable     $iterator The Iterator to filter
	 * @param integer|callback $sort     The sort type (SORT_BY_NAME, SORT_BY_TYPE, or a PHP callback)
	 */
	public function __construct(\Traversable $iterator, $sort) {
		$this->iterator = $iterator;

		if (self::SORT_BY_NAME === $sort) {
			$this->sort = function ($a, $b) {
				return strcmp($a->getRealpath(), $b->getRealpath());
			};
		} elseif (self::SORT_BY_TYPE === $sort) {
			$this->sort = function ($a, $b) {
				if ($a->isDir() && $b->isFile()) {
					return -1;
				} elseif ($a->isFile() && $b->isDir()) {
					return 1;
				}

				return strcmp($a->getRealpath(), $b->getRealpath());
			};
		} elseif (is_callable($sort)) {
			$this->sort = $sort;
		} else {
			throw new \InvalidArgumentException('The SortableIterator takes a PHP callback or a valid built-in sort algorithm as an argument.');
		}
	}

	public function getIterator() {
		$array = iterator_to_array($this->iterator, true);
		uasort($array, $this->sort);

		return new \ArrayIterator($array);
	}
}
