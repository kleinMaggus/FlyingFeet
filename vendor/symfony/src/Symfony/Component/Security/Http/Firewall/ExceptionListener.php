<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Http\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * ExceptionListener catches authentication exception and converts them to
 * Response instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExceptionListener {
	private $context;
	private $accessDeniedHandler;
	private $authenticationEntryPoint;
	private $authenticationTrustResolver;
	private $errorPage;
	private $logger;
	private $httpUtils;

	public function __construct(SecurityContextInterface $context, AuthenticationTrustResolverInterface $trustResolver, HttpUtils $httpUtils, AuthenticationEntryPointInterface $authenticationEntryPoint = null, $errorPage = null, AccessDeniedHandlerInterface $accessDeniedHandler = null,
			LoggerInterface $logger = null) {
		$this->context = $context;
		$this->accessDeniedHandler = $accessDeniedHandler;
		$this->httpUtils = $httpUtils;
		$this->authenticationEntryPoint = $authenticationEntryPoint;
		$this->authenticationTrustResolver = $trustResolver;
		$this->errorPage = $errorPage;
		$this->logger = $logger;
	}

	/**
	 * Registers a onKernelException listener to take care of security exceptions.
	 *
	 * @param EventDispatcherInterface $dispatcher An EventDispatcherInterface instance
	 */
	public function register(EventDispatcherInterface $dispatcher) {
		$dispatcher->addListener(KernelEvents::EXCEPTION, array($this, 'onKernelException'));
	}

	/**
	 * Handles security related exceptions.
	 *
	 * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
	 */
	public function onKernelException(GetResponseForExceptionEvent $event) {
		$exception = $event->getException();
		$request = $event->getRequest();

		// determine the actual cause for the exception
		while (null !== $previous = $exception->getPrevious()) {
			$exception = $previous;
		}

		if ($exception instanceof AuthenticationException) {
			if (null !== $this->logger) {
				$this->logger->info(sprintf('Authentication exception occurred; redirecting to authentication entry point (%s)', $exception->getMessage()));
			}

			try {
				$response = $this->startAuthentication($request, $exception);
			} catch (\Exception $e) {
				$event->setException($e);

				return;
			}
		} elseif ($exception instanceof AccessDeniedException) {
			$token = $this->context->getToken();
			if (!$this->authenticationTrustResolver->isFullFledged($token)) {
				if (null !== $this->logger) {
					$this->logger->debug('Access denied (user is not fully authenticated); redirecting to authentication entry point');
				}

				try {
					$response = $this->startAuthentication($request, new InsufficientAuthenticationException('Full authentication is required to access this resource.', $token, 0, $exception));
				} catch (\Exception $e) {
					$event->setException($e);

					return;
				}
			} else {
				if (null !== $this->logger) {
					$this->logger->debug('Access is denied (and user is neither anonymous, nor remember-me)');
				}

				try {
					if (null !== $this->accessDeniedHandler) {
						$response = $this->accessDeniedHandler->handle($request, $exception);

						if (!$response instanceof Response) {
							return;
						}
					} elseif (null !== $this->errorPage) {
						$subRequest = $this->httpUtils->createRequest($request, $this->errorPage);
						$subRequest->attributes->set(SecurityContextInterface::ACCESS_DENIED_ERROR, $exception);

						$response = $event->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST, true);
						$response->setStatusCode(403);
					} else {
						$event->setException(new AccessDeniedHttpException($exception->getMessage(), $exception));

						return;
					}
				} catch (\Exception $e) {
					if (null !== $this->logger) {
						$this->logger->err(sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage()));
					}

					$event->setException(new \RuntimeException('Exception thrown when handling an exception.', 0, $e));

					return;
				}
			}
		} else {
			return;
		}

		$event->setResponse($response);
	}

	private function startAuthentication(Request $request, AuthenticationException $authException) {
		if (null === $this->authenticationEntryPoint) {
			throw $authException;
		}

		if (null !== $this->logger) {
			$this->logger->debug('Calling Authentication entry point');
		}

		$this->setTargetPath($request);

		return $this->authenticationEntryPoint->start($request, $authException);
	}

	protected function setTargetPath(Request $request) {
		// session isn't required when using http basic authentication mechanism for example
		if ($request->hasSession()) {
			$request->getSession()->set('_security.target_path', $request->getUri());
		}
	}
}
