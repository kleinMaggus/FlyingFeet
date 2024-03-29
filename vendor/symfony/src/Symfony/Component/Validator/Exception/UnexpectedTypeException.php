<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Exception;

class UnexpectedTypeException extends ValidatorException {
	public function __construct($value, $expectedType) {
		parent::__construct(sprintf('Expected argument of type %s, %s given', $expectedType, gettype($value)));
	}
}
