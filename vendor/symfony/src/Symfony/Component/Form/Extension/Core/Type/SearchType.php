<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\Type;

use Symfony\Component\Form\AbstractType;

class SearchType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function getParent(array $options) {
		return 'text';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'search';
	}
}
