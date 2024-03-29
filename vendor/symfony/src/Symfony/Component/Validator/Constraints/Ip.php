<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Constraint;

/**
 * Validates that a value is a valid IP address
 *
 * @Annotation
 * @author Bernhard Schussek <bernhard.schussek@symfony.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 *
 * @api
 */
class Ip extends Constraint {
	const V4 = '4';
	const V6 = '6';
	const ALL = 'all';

	// adds FILTER_FLAG_NO_PRIV_RANGE flag (skip private ranges)
	const V4_NO_PRIV = '4_no_priv';
	const V6_NO_PRIV = '6_no_priv';
	const ALL_NO_PRIV = 'all_no_priv';

	// adds FILTER_FLAG_NO_RES_RANGE flag (skip reserved ranges)
	const V4_NO_RES = '4_no_res';
	const V6_NO_RES = '6_no_res';
	const ALL_NO_RES = 'all_no_res';

	// adds FILTER_FLAG_NO_PRIV_RANGE and FILTER_FLAG_NO_RES_RANGE flags (skip both)
	const V4_ONLY_PUBLIC = '4_public';
	const V6_ONLY_PUBLIC = '6_public';
	const ALL_ONLY_PUBLIC = 'all_public';

	static protected $versions = array(self::V4, self::V6, self::ALL, self::V4_NO_PRIV, self::V6_NO_PRIV, self::ALL_NO_PRIV, self::V4_NO_RES, self::V6_NO_RES, self::ALL_NO_RES, self::V4_ONLY_PUBLIC, self::V6_ONLY_PUBLIC, self::ALL_ONLY_PUBLIC,);

	public $version = self::V4;

	public $message = 'This is not a valid IP address';

	/**
	 * {@inheritDoc}
	 */
	public function __construct($options = null) {
		parent::__construct($options);

		if (!in_array($this->version, self::$versions)) {
			throw new ConstraintDefinitionException(sprintf('The option "version" must be one of "%s"', implode('", "', self::$versions)));
		}
	}
}
