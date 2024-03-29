<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Monolog\Handler;

use Monolog\Handler\FirePHPHandler as BaseFirePHPHandler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * FirePHPHandler.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class FirePHPHandler extends BaseFirePHPHandler {
	/**
	 * @var array
	 */
	private $headers = array();

	/**
	 * @var Response
	 */
	private $response;

	/**
	 * Adds the headers to the response once it's created
	 */
	public function onKernelResponse(FilterResponseEvent $event) {
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}

		$this->response = $event->getResponse();
		foreach ($this->headers as $header => $content) {
			$this->response->headers->set($header, $content);
		}
		$this->headers = array();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function sendHeader($header, $content) {
		if ($this->response) {
			$this->response->headers->set($header, $content);
		} else {
			$this->headers[$header] = $content;
		}
	}
}
