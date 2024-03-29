<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\EventListener\TestSessionListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * SessionListenerTest.
 *
 * Tests SessionListener.
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class TestSessionListenerTest extends \PHPUnit_Framework_TestCase {
	private $listener;
	private $session;

	public function setUp() {
		$this->listener = new TestSessionListener($this->getMock('Symfony\Component\DependencyInjection\ContainerInterface'));
		$this->session = $this->getSession();
	}

	protected function tearDown() {
		$this->listener = null;
		$this->session = null;
	}

	public function testShouldSaveMasterRequestSession() {
		$this->sessionMustBeSaved();

		$this->filterResponse(new Request());
	}

	public function testShouldNotSaveSubRequestSession() {
		$this->sessionMustNotBeSaved();

		$this->filterResponse(new Request(), HttpKernelInterface::SUB_REQUEST);
	}

	public function testDoesNotDeleteCookieIfUsingSessionLifetime() {
		$params = session_get_cookie_params();
		session_set_cookie_params(0, $params['path'], $params['domain'], $params['secure'], $params['httponly']);

		$response = $this->filterResponse(new Request(), HttpKernelInterface::MASTER_REQUEST);
		$cookies = $response->headers->getCookies();

		$this->assertEquals(0, reset($cookies)->getExpiresTime());
	}

	private function filterResponse(Request $request, $type = HttpKernelInterface::MASTER_REQUEST) {
		$request->setSession($this->session);
		$response = new Response();
		$kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
		$event = new FilterResponseEvent($kernel, $request, $type, $response);

		$this->listener->onKernelResponse($event);

		$this->assertSame($response, $event->getResponse());

		return $response;
	}

	private function sessionMustNotBeSaved() {
		$this->session->expects($this->never())->method('save');
	}

	private function sessionMustBeSaved() {
		$this->session->expects($this->once())->method('save');
	}

	private function getSession() {
		return $this->getMockBuilder('Symfony\Component\HttpFoundation\Session')->disableOriginalConstructor()->getMock();
	}
}
