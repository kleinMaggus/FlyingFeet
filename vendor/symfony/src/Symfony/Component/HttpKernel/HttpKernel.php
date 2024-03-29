<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel;

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * HttpKernel notifies events to convert a Request object to a Response one.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class HttpKernel implements HttpKernelInterface {
	private $dispatcher;
	private $resolver;

	/**
	 * Constructor
	 *
	 * @param EventDispatcherInterface    $dispatcher An EventDispatcherInterface instance
	 * @param ControllerResolverInterface $resolver   A ControllerResolverInterface instance
	 *
	 * @api
	 */
	public function __construct(EventDispatcherInterface $dispatcher, ControllerResolverInterface $resolver) {
		$this->dispatcher = $dispatcher;
		$this->resolver = $resolver;
	}

	/**
	 * Handles a Request to convert it to a Response.
	 *
	 * When $catch is true, the implementation must catch all exceptions
	 * and do its best to convert them to a Response instance.
	 *
	 * @param  Request $request A Request instance
	 * @param  integer $type    The type of the request
	 *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
	 * @param  Boolean $catch   Whether to catch exceptions or not
	 *
	 * @return Response A Response instance
	 *
	 * @throws \Exception When an Exception occurs during processing
	 *
	 * @api
	 */
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
		try {
			return $this->handleRaw($request, $type);
		} catch (\Exception $e) {
			if (false === $catch) {
				throw $e;
			}

			return $this->handleException($e, $request, $type);
		}
	}

	/**
	 * Handles a request to convert it to a response.
	 *
	 * Exceptions are not caught.
	 *
	 * @param Request $request A Request instance
	 * @param integer $type    The type of the request (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
	 *
	 * @return Response A Response instance
	 *
	 * @throws \LogicException If one of the listener does not behave as expected
	 * @throws NotFoundHttpException When controller cannot be found
	 */
	private function handleRaw(Request $request, $type = self::MASTER_REQUEST) {
		// request
		$event = new GetResponseEvent($this, $request, $type);
		$this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

		if ($event->hasResponse()) {
			return $this->filterResponse($event->getResponse(), $request, $type);
		}

		// load controller
		if (false === $controller = $this->resolver->getController($request)) {
			throw new NotFoundHttpException(sprintf('Unable to find the controller for path "%s". Maybe you forgot to add the matching route in your routing configuration?', $request->getPathInfo()));
		}

		$event = new FilterControllerEvent($this, $controller, $request, $type);
		$this->dispatcher->dispatch(KernelEvents::CONTROLLER, $event);
		$controller = $event->getController();

		// controller arguments
		$arguments = $this->resolver->getArguments($request, $controller);

		// call controller
		$response = call_user_func_array($controller, $arguments);

		// view
		if (!$response instanceof Response) {
			$event = new GetResponseForControllerResultEvent($this, $request, $type, $response);
			$this->dispatcher->dispatch(KernelEvents::VIEW, $event);

			if ($event->hasResponse()) {
				$response = $event->getResponse();
			}

			if (!$response instanceof Response) {
				$msg = sprintf('The controller must return a response (%s given).', $this->varToString($response));

				// the user may have forgotten to return something
				if (null === $response) {
					$msg .= ' Did you forget to add a return statement somewhere in your controller?';
				}
				throw new \LogicException($msg);
			}
		}

		return $this->filterResponse($response, $request, $type);
	}

	/**
	 * Filters a response object.
	 *
	 * @param Response $response A Response instance
	 * @param Request  $request  A error message in case the response is not a Response object
	 * @param integer  $type     The type of the request (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
	 *
	 * @return Response The filtered Response instance
	 *
	 * @throws \RuntimeException if the passed object is not a Response instance
	 */
	private function filterResponse(Response $response, Request $request, $type) {
		$event = new FilterResponseEvent($this, $request, $type, $response);

		$this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

		return $event->getResponse();
	}

	/**
	 * Handles and exception by trying to convert it to a Response.
	 *
	 * @param  \Exception $e       An \Exception instance
	 * @param  Request    $request A Request instance
	 * @param  integer    $type    The type of the request
	 *
	 * @return Response A Response instance
	 */
	private function handleException(\Exception $e, $request, $type) {
		$event = new GetResponseForExceptionEvent($this, $request, $type, $e);
		$this->dispatcher->dispatch(KernelEvents::EXCEPTION, $event);

		if (!$event->hasResponse()) {
			throw $e;
		}

		try {
			return $this->filterResponse($event->getResponse(), $request, $type);
		} catch (\Exception $e) {
			return $event->getResponse();
		}
	}

	private function varToString($var) {
		if (is_object($var)) {
			return sprintf('Object(%s)', get_class($var));
		}

		if (is_array($var)) {
			$a = array();
			foreach ($var as $k => $v) {
				$a[] = sprintf('%s => %s', $k, $this->varToString($v));
			}

			return sprintf("Array(%s)", implode(', ', $a));
		}

		if (is_resource($var)) {
			return sprintf('Resource(%s)', get_resource_type($var));
		}

		if (null === $var) {
			return 'null';
		}

		if (false === $var) {
			return 'false';
		}

		if (true === $var) {
			return 'true';
		}

		return (string) $var;
	}
}
