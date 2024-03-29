<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Acl\Model;

/**
 * AclCache Interface
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface AclCacheInterface {
	/**
	 * Removes an ACL from the cache
	 *
	 * @param string $primaryKey a serialized primary key
	 * @return void
	 */
	function evictFromCacheById($primaryKey);

	/**
	 * Removes an ACL from the cache
	 *
	 * The ACL which is returned, must reference the passed object identity.
	 *
	 * @param ObjectIdentityInterface $oid
	 * @return void
	 */
	function evictFromCacheByIdentity(ObjectIdentityInterface $oid);

	/**
	 * Retrieves an ACL for the given object identity primary key from the cache
	 *
	 * @param integer $primaryKey
	 * @return AclInterface
	 */
	function getFromCacheById($primaryKey);

	/**
	 * Retrieves an ACL for the given object identity from the cache
	 *
	 * @param ObjectIdentityInterface $oid
	 * @return AclInterface
	 */
	function getFromCacheByIdentity(ObjectIdentityInterface $oid);

	/**
	 * Stores a new ACL in the cache
	 *
	 * @param AclInterface $acl
	 * @return void
	 */
	function putInCache(AclInterface $acl);

	/**
	 * Removes all ACLs from the cache
	 *
	 * @return void
	 */
	function clearCache();
}
