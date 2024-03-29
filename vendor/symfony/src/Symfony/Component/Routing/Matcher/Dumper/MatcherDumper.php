<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Matcher\Dumper;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * MatcherDumper is the abstract class for all built-in matcher dumpers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class MatcherDumper implements MatcherDumperInterface {
	private $routes;

	/**
	 * Constructor.
	 *
	 * @param RouteCollection $routes The RouteCollection to dump
	 */
	public function __construct(RouteCollection $routes) {
		$this->routes = $routes;
	}

	/**
	 * Gets the routes to dump.
	 *
	 * @return RouteCollection A RouteCollection instance
	 */
	public function getRoutes() {
		return $this->routes;
	}
}
