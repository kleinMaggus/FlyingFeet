<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Role;

/**
 * RoleHierarchyInterface is the interface for a role hierarchy.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface RoleHierarchyInterface {
	/**
	 * Returns an array of all reachable roles.
	 *
	 * Reachable roles are the roles directly assigned but also all roles that
	 * are transitively reachable from them in the role hierarchy.
	 *
	 * @param array $roles An array of directly assigned roles
	 *
	 * @return array An array of all reachable roles
	 */
	function getReachableRoles(array $roles);
}
