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

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * UsernamePasswordFormAuthenticationListener is the default implementation of
 * an authentication via a simple form composed of a username and a password.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class UsernamePasswordFormAuthenticationListener extends AbstractAuthenticationListener {
	private $csrfProvider;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, SessionAuthenticationStrategyInterface $sessionStrategy, HttpUtils $httpUtils, $providerKey, array $options = array(),
			AuthenticationSuccessHandlerInterface $successHandler = null, AuthenticationFailureHandlerInterface $failureHandler = null, LoggerInterface $logger = null, EventDispatcherInterface $dispatcher = null, CsrfProviderInterface $csrfProvider = null) {
		parent::__construct($securityContext, $authenticationManager, $sessionStrategy, $httpUtils, $providerKey,
				array_merge(array('username_parameter' => '_username', 'password_parameter' => '_password', 'csrf_parameter' => '_csrf_token', 'intention' => 'authenticate', 'post_only' => true,), $options), $successHandler, $failureHandler, $logger, $dispatcher);

		$this->csrfProvider = $csrfProvider;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function attemptAuthentication(Request $request) {
		if ($this->options['post_only'] && 'post' !== strtolower($request->getMethod())) {
			if (null !== $this->logger) {
				$this->logger->debug(sprintf('Authentication method not supported: %s.', $request->getMethod()));
			}

			return null;
		}

		if (null !== $this->csrfProvider) {
			$csrfToken = $request->get($this->options['csrf_parameter'], null, true);

			if (false === $this->csrfProvider->isCsrfTokenValid($this->options['intention'], $csrfToken)) {
				throw new InvalidCsrfTokenException('Invalid CSRF token.');
			}
		}

		$username = trim($request->get($this->options['username_parameter'], null, true));
		$password = $request->get($this->options['password_parameter'], null, true);

		$request->getSession()->set(SecurityContextInterface::LAST_USERNAME, $username);

		return $this->authenticationManager->authenticate(new UsernamePasswordToken($username, $password, $this->providerKey));
	}
}
