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
 * This interface represents an access control list (ACL) for a domain object.
 * Each domain object can have exactly one associated ACL.
 *
 * An ACL contains all access control entries (ACE) for a given domain object.
 * In order to avoid needing references to the domain object itself, implementations
 * use ObjectIdentity implementations as an additional level of indirection.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface AclInterface extends \Serializable {
	/**
	 * Returns all class-based ACEs associated with this ACL
	 *
	 * @return array
	 */
	function getClassAces();

	/**
	 * Returns all class-field-based ACEs associated with this ACL
	 *
	 * @param string $field
	 * @return array
	 */
	function getClassFieldAces($field);

	/**
	 * Returns all object-based ACEs associated with this ACL
	 *
	 * @return array
	 */
	function getObjectAces();

	/**
	 * Returns all object-field-based ACEs associated with this ACL
	 *
	 * @param string $field
	 * @return array
	 */
	function getObjectFieldAces($field);

	/**
	 * Returns the object identity associated with this ACL
	 *
	 * @return ObjectIdentityInterface
	 */
	function getObjectIdentity();

	/**
	 * Returns the parent ACL, or null if there is none.
	 *
	 * @return AclInterface|null
	 */
	function getParentAcl();

	/**
	 * Whether this ACL is inheriting ACEs from a parent ACL.
	 *
	 * @return Boolean
	 */
	function isEntriesInheriting();

	/**
	 * Determines whether field access is granted
	 *
	 * @param string  $field
	 * @param array   $masks
	 * @param array   $securityIdentities
	 * @param Boolean $administrativeMode
	 * @return Boolean
	 */
	function isFieldGranted($field, array $masks, array $securityIdentities, $administrativeMode = false);

	/**
	 * Determines whether access is granted
	 *
	 * @throws NoAceFoundException when no ACE was applicable for this request
	 * @param array   $masks
	 * @param array   $securityIdentities
	 * @param Boolean $administrativeMode
	 * @return Boolean
	 */
	function isGranted(array $masks, array $securityIdentities, $administrativeMode = false);

	/**
	 * Whether the ACL has loaded ACEs for all of the passed security identities
	 *
	 * @param mixed $securityIdentities an implementation of SecurityIdentityInterface, or an array thereof
	 * @return Boolean
	 */
	function isSidLoaded($securityIdentities);
}
