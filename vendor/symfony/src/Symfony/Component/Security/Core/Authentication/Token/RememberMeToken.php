<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Authentication Token for "Remember-Me".
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class RememberMeToken extends AbstractToken {
	private $key;
	private $providerKey;

	/**
	 * Constructor.
	 *
	 * @param UserInterface $user
	 * @param string        $providerKey
	 * @param string        $key
	 */
	public function __construct(UserInterface $user, $providerKey, $key) {
		parent::__construct($user->getRoles());

		if (empty($key)) {
			throw new \InvalidArgumentException('$key must not be empty.');
		}

		if (empty($providerKey)) {
			throw new \InvalidArgumentException('$providerKey must not be empty.');
		}

		$this->providerKey = $providerKey;
		$this->key = $key;

		$this->setUser($user);
		parent::setAuthenticated(true);
	}

	public function setAuthenticated($authenticated) {
		if ($authenticated) {
			throw new \RuntimeException('You cannot set this token to authenticated after creation.');
		}

		parent::setAuthenticated(false);
	}

	public function getProviderKey() {
		return $this->providerKey;
	}

	public function getKey() {
		return $this->key;
	}

	public function getCredentials() {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function serialize() {
		return serialize(array($this->key, $this->providerKey, parent::serialize(),));
	}

	/**
	 * {@inheritdoc}
	 */
	public function unserialize($serialized) {
		list($this->key, $this->providerKey, $parentStr) = unserialize($serialized);
		parent::unserialize($parentStr);
	}
}
