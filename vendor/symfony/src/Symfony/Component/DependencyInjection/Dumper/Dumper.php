<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Dumper;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Dumper is the abstract class for all built-in dumpers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
abstract class Dumper implements DumperInterface {
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param ContainerBuilder $container The service container to dump
	 *
	 * @api
	 */
	public function __construct(ContainerBuilder $container) {
		$this->container = $container;
	}
}
