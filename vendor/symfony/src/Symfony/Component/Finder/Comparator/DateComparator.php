<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Finder\Comparator;

/**
 * DateCompare compiles date comparisons.
 *
 * @author Fabien Potencier <fabien@symfony.com> PHP port
 */
class DateComparator extends Comparator {

	/**
	 * Constructor.
	 *
	 * @param string $test A comparison string
	 *
	 * @throws \InvalidArgumentException If the test is not understood
	 */
	public function __construct($test) {
		if (!preg_match('#^\s*([<>=]=?|after|since|before|until)?\s*(.+?)\s*$#i', $test, $matches)) {
			throw new \InvalidArgumentException(sprintf('Don\'t understand "%s" as a date test.', $test));
		}

		if (false === $target = @strtotime($matches[2])) {
			throw new \InvalidArgumentException(sprintf('"%s" is not a valid date.', $matches[2]));
		}

		$operator = isset($matches[1]) ? $matches[1] : '==';
		if ('since' === $operator || 'after' === $operator) {
			$operator = '>';
		}

		if ('until' === $operator || 'before' === $operator) {
			$operator = '<';
		}

		$this->setOperator($operator);
		$this->setTarget($target);
	}
}
