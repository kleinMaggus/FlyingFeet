<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\DataTransformer;

use Symfony\Component\Form\Util\FormUtil;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ArrayToChoicesTransformer implements DataTransformerInterface {
	/**
	 * @param array $array
	 *
	 * @return array
	 *
	 * @throws UnexpectedTypeException if the given value is not an array
	 */
	public function transform($array) {
		if (null === $array) {
			return array();
		}

		if (!is_array($array)) {
			throw new UnexpectedTypeException($array, 'array');
		}

		return FormUtil::toArrayKeys($array);
	}

	/**
	 * @param array $array
	 *
	 * @return array
	 *
	 * @throws UnexpectedTypeException if the given value is not an array
	 */
	public function reverseTransform($array) {
		if (null === $array) {
			return array();
		}

		if (!is_array($array)) {
			throw new UnexpectedTypeException($array, 'array');
		}

		return $array;
	}
}
