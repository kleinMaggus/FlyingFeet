<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Monolog\Processor;

use Monolog\Processor\WebProcessor as BaseWebProcessor;
use Symfony\Component\HttpFoundation\Request;

/**
 * WebProcessor override to read from the HttpFoundation's Request
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class WebProcessor extends BaseWebProcessor {
	public function __construct(Request $request) {
		parent::__construct($request->server->all());
	}
}
