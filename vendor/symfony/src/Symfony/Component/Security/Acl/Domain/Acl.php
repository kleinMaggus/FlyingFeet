<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Acl\Domain;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\AuditableAclInterface;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * An ACL implementation.
 *
 * Each object identity has exactly one associated ACL. Each ACL can have four
 * different types of ACEs (class ACEs, object ACEs, class field ACEs, object field
 * ACEs).
 *
 * You should not iterate over the ACEs yourself, but instead use isGranted(),
 * or isFieldGranted(). These will utilize an implementation of PermissionGrantingStrategy
 * internally.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Acl implements AuditableAclInterface, NotifyPropertyChanged {
	private $parentAcl;
	private $permissionGrantingStrategy;
	private $objectIdentity;
	private $classAces;
	private $classFieldAces;
	private $objectAces;
	private $objectFieldAces;
	private $id;
	private $loadedSids;
	private $entriesInheriting;
	private $listeners;

	/**
	 * Constructor
	 *
	 * @param integer                             $id
	 * @param ObjectIdentityInterface             $objectIdentity
	 * @param PermissionGrantingStrategyInterface $permissionGrantingStrategy
	 * @param array                               $loadedSids
	 * @param Boolean                             $entriesInheriting
	 * @return void
	 */
	public function __construct($id, ObjectIdentityInterface $objectIdentity, PermissionGrantingStrategyInterface $permissionGrantingStrategy, array $loadedSids = array(), $entriesInheriting) {
		$this->id = $id;
		$this->objectIdentity = $objectIdentity;
		$this->permissionGrantingStrategy = $permissionGrantingStrategy;
		$this->loadedSids = $loadedSids;
		$this->entriesInheriting = $entriesInheriting;
		$this->parentAcl = null;
		$this->classAces = array();
		$this->classFieldAces = array();
		$this->objectAces = array();
		$this->objectFieldAces = array();
		$this->listeners = array();
	}

	/**
	 * Adds a property changed listener
	 *
	 * @param PropertyChangedListener $listener
	 * @return void
	 */
	public function addPropertyChangedListener(PropertyChangedListener $listener) {
		$this->listeners[] = $listener;
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteClassAce($index) {
		$this->deleteAce('classAces', $index);
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteClassFieldAce($index, $field) {
		$this->deleteFieldAce('classFieldAces', $index, $field);
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteObjectAce($index) {
		$this->deleteAce('objectAces', $index);
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteObjectFieldAce($index, $field) {
		$this->deleteFieldAce('objectFieldAces', $index, $field);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClassAces() {
		return $this->classAces;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClassFieldAces($field) {
		return isset($this->classFieldAces[$field]) ? $this->classFieldAces[$field] : array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getObjectAces() {
		return $this->objectAces;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getObjectFieldAces($field) {
		return isset($this->objectFieldAces[$field]) ? $this->objectFieldAces[$field] : array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getObjectIdentity() {
		return $this->objectIdentity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParentAcl() {
		return $this->parentAcl;
	}

	/**
	 * {@inheritDoc}
	 */
	public function insertClassAce(SecurityIdentityInterface $sid, $mask, $index = 0, $granting = true, $strategy = null) {
		$this->insertAce('classAces', $index, $mask, $sid, $granting, $strategy);
	}

	/**
	 * {@inheritDoc}
	 */
	public function insertClassFieldAce($field, SecurityIdentityInterface $sid, $mask, $index = 0, $granting = true, $strategy = null) {
		$this->insertFieldAce('classFieldAces', $index, $field, $mask, $sid, $granting, $strategy);
	}

	/**
	 * {@inheritDoc}
	 */
	public function insertObjectAce(SecurityIdentityInterface $sid, $mask, $index = 0, $granting = true, $strategy = null) {
		$this->insertAce('objectAces', $index, $mask, $sid, $granting, $strategy);
	}

	/**
	 * {@inheritDoc}
	 */
	public function insertObjectFieldAce($field, SecurityIdentityInterface $sid, $mask, $index = 0, $granting = true, $strategy = null) {
		$this->insertFieldAce('objectFieldAces', $index, $field, $mask, $sid, $granting, $strategy);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isEntriesInheriting() {
		return $this->entriesInheriting;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isFieldGranted($field, array $masks, array $securityIdentities, $administrativeMode = false) {
		return $this->permissionGrantingStrategy->isFieldGranted($this, $field, $masks, $securityIdentities, $administrativeMode);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isGranted(array $masks, array $securityIdentities, $administrativeMode = false) {
		return $this->permissionGrantingStrategy->isGranted($this, $masks, $securityIdentities, $administrativeMode);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSidLoaded($sids) {
		if (!$this->loadedSids) {
			return true;
		}

		if (!is_array($sids)) {
			$sids = array($sids);
		}

		foreach ($sids as $sid) {
			if (!$sid instanceof SecurityIdentityInterface) {
				throw new \InvalidArgumentException('$sid must be an instance of SecurityIdentityInterface.');
			}

			foreach ($this->loadedSids as $loadedSid) {
				if ($loadedSid->equals($sid)) {
					continue 2;
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Implementation for the \Serializable interface
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize(
				array(null === $this->parentAcl ? null : $this->parentAcl->getId(), $this->objectIdentity, $this->classAces, $this->classFieldAces, $this->objectAces, $this->objectFieldAces, $this->id, $this->loadedSids, $this->entriesInheriting,));
	}

	/**
	 * Implementation for the \Serializable interface
	 *
	 * @param string $serialized
	 * @return void
	 */
	public function unserialize($serialized) {
		list($this->parentAcl, $this->objectIdentity, $this->classAces, $this->classFieldAces, $this->objectAces, $this->objectFieldAces, $this->id, $this->loadedSids, $this->entriesInheriting) = unserialize($serialized);

		$this->listeners = array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setEntriesInheriting($boolean) {
		if ($this->entriesInheriting !== $boolean) {
			$this->onPropertyChanged('entriesInheriting', $this->entriesInheriting, $boolean);
			$this->entriesInheriting = $boolean;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function setParentAcl(AclInterface $acl) {
		if (null === $acl->getId()) {
			throw new \InvalidArgumentException('$acl must have an ID.');
		}

		if ($this->parentAcl !== $acl) {
			$this->onPropertyChanged('parentAcl', $this->parentAcl, $acl);
			$this->parentAcl = $acl;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateClassAce($index, $mask, $strategy = null) {
		$this->updateAce('classAces', $index, $mask, $strategy);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateClassFieldAce($index, $field, $mask, $strategy = null) {
		$this->updateFieldAce('classFieldAces', $index, $field, $mask, $strategy);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateObjectAce($index, $mask, $strategy = null) {
		$this->updateAce('objectAces', $index, $mask, $strategy);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateObjectFieldAce($index, $field, $mask, $strategy = null) {
		$this->updateFieldAce('objectFieldAces', $index, $field, $mask, $strategy);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateClassAuditing($index, $auditSuccess, $auditFailure) {
		$this->updateAuditing($this->classAces, $index, $auditSuccess, $auditFailure);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateClassFieldAuditing($index, $field, $auditSuccess, $auditFailure) {
		if (!isset($this->classFieldAces[$field])) {
			throw new \InvalidArgumentException(sprintf('There are no ACEs for field "%s".', $field));
		}

		$this->updateAuditing($this->classFieldAces[$field], $index, $auditSuccess, $auditFailure);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateObjectAuditing($index, $auditSuccess, $auditFailure) {
		$this->updateAuditing($this->objectAces, $index, $auditSuccess, $auditFailure);
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateObjectFieldAuditing($index, $field, $auditSuccess, $auditFailure) {
		if (!isset($this->objectFieldAces[$field])) {
			throw new \InvalidArgumentException(sprintf('There are no ACEs for field "%s".', $field));
		}

		$this->updateAuditing($this->objectFieldAces[$field], $index, $auditSuccess, $auditFailure);
	}

	/**
	 * Deletes an ACE
	 *
	 * @param string $property
	 * @param integer $index
	 * @throws \OutOfBoundsException
	 * @return void
	 */
	private function deleteAce($property, $index) {
		$aces = &$this->$property;
		if (!isset($aces[$index])) {
			throw new \OutOfBoundsException(sprintf('The index "%d" does not exist.', $index));
		}

		$oldValue = $this->$property;
		unset($aces[$index]);
		$this->$property = array_values($this->$property);
		$this->onPropertyChanged($property, $oldValue, $this->$property);

		for ($i = $index, $c = count($this->$property); $i < $c; $i++) {
			$this->onEntryPropertyChanged($aces[$i], 'aceOrder', $i + 1, $i);
		}
	}

	/**
	 * Deletes a field-based ACE
	 *
	 * @param string $property
	 * @param integer $index
	 * @param string $field
	 * @throws \OutOfBoundsException
	 * @return void
	 */
	private function deleteFieldAce($property, $index, $field) {
		$aces = &$this->$property;
		if (!isset($aces[$field][$index])) {
			throw new \OutOfBoundsException(sprintf('The index "%d" does not exist.', $index));
		}

		$oldValue = $this->$property;
		unset($aces[$field][$index]);
		$aces[$field] = array_values($aces[$field]);
		$this->onPropertyChanged($property, $oldValue, $this->$property);

		for ($i = $index, $c = count($aces[$field]); $i < $c; $i++) {
			$this->onEntryPropertyChanged($aces[$field][$i], 'aceOrder', $i + 1, $i);
		}
	}

	/**
	 * Inserts an ACE
	 *
	 * @param string                    $property
	 * @param integer                   $index
	 * @param integer                   $mask
	 * @param SecurityIdentityInterface $sid
	 * @param Boolean                   $granting
	 * @param string                    $strategy
	 * @throws \OutOfBoundsException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	private function insertAce($property, $index, $mask, SecurityIdentityInterface $sid, $granting, $strategy = null) {
		if ($index < 0 || $index > count($this->$property)) {
			throw new \OutOfBoundsException(sprintf('The index must be in the interval [0, %d].', count($this->$property)));
		}

		if (!is_int($mask)) {
			throw new \InvalidArgumentException('$mask must be an integer.');
		}

		if (null === $strategy) {
			if (true === $granting) {
				$strategy = PermissionGrantingStrategy::ALL;
			} else {
				$strategy = PermissionGrantingStrategy::ANY;
			}
		}

		$aces = &$this->$property;
		$oldValue = $this->$property;
		if (isset($aces[$index])) {
			$this->$property = array_merge(array_slice($this->$property, 0, $index), array(true), array_slice($this->$property, $index));

			for ($i = $index, $c = count($this->$property) - 1; $i < $c; $i++) {
				$this->onEntryPropertyChanged($aces[$i + 1], 'aceOrder', $i, $i + 1);
			}
		}

		$aces[$index] = new Entry(null, $this, $sid, $strategy, $mask, $granting, false, false);
		$this->onPropertyChanged($property, $oldValue, $this->$property);
	}

	/**
	 * Inserts a field-based ACE
	 *
	 * @param string                    $property
	 * @param integer                   $index
	 * @param string                    $field
	 * @param integer                   $mask
	 * @param SecurityIdentityInterface $sid
	 * @param Boolean                   $granting
	 * @param string                    $strategy
	 * @throws \InvalidArgumentException
	 * @throws \OutOfBoundsException
	 * @return void
	 */
	private function insertFieldAce($property, $index, $field, $mask, SecurityIdentityInterface $sid, $granting, $strategy = null) {
		if (0 === strlen($field)) {
			throw new \InvalidArgumentException('$field cannot be empty.');
		}

		if (!is_int($mask)) {
			throw new \InvalidArgumentException('$mask must be an integer.');
		}

		if (null === $strategy) {
			if (true === $granting) {
				$strategy = PermissionGrantingStrategy::ALL;
			} else {
				$strategy = PermissionGrantingStrategy::ANY;
			}
		}

		$aces = &$this->$property;
		if (!isset($aces[$field])) {
			$aces[$field] = array();
		}

		if ($index < 0 || $index > count($aces[$field])) {
			throw new \OutOfBoundsException(sprintf('The index must be in the interval [0, %d].', count($this->$property)));
		}

		$oldValue = $aces;
		if (isset($aces[$field][$index])) {
			$aces[$field] = array_merge(array_slice($aces[$field], 0, $index), array(true), array_slice($aces[$field], $index));

			for ($i = $index, $c = count($aces[$field]) - 1; $i < $c; $i++) {
				$this->onEntryPropertyChanged($aces[$field][$i + 1], 'aceOrder', $i, $i + 1);
			}
		}

		$aces[$field][$index] = new FieldEntry(null, $this, $field, $sid, $strategy, $mask, $granting, false, false);
		$this->onPropertyChanged($property, $oldValue, $this->$property);
	}

	/**
	 * Updates an ACE
	 *
	 * @param string $property
	 * @param integer $index
	 * @param integer $mask
	 * @param string $strategy
	 * @throws \OutOfBoundsException
	 * @return void
	 */
	private function updateAce($property, $index, $mask, $strategy = null) {
		$aces = &$this->$property;
		if (!isset($aces[$index])) {
			throw new \OutOfBoundsException(sprintf('The index "%d" does not exist.', $index));
		}

		$ace = $aces[$index];
		if ($mask !== $oldMask = $ace->getMask()) {
			$this->onEntryPropertyChanged($ace, 'mask', $oldMask, $mask);
			$ace->setMask($mask);
		}
		if (null !== $strategy && $strategy !== $oldStrategy = $ace->getStrategy()) {
			$this->onEntryPropertyChanged($ace, 'strategy', $oldStrategy, $strategy);
			$ace->setStrategy($strategy);
		}
	}

	/**
	 * Updates auditing for an ACE
	 *
	 * @param array $aces
	 * @param integer $index
	 * @param Boolean $auditSuccess
	 * @param Boolean $auditFailure
	 * @throws \OutOfBoundsException
	 * @return void
	 */
	private function updateAuditing(array &$aces, $index, $auditSuccess, $auditFailure) {
		if (!isset($aces[$index])) {
			throw new \OutOfBoundsException(sprintf('The index "%d" does not exist.', $index));
		}

		if ($auditSuccess !== $aces[$index]->isAuditSuccess()) {
			$this->onEntryPropertyChanged($aces[$index], 'auditSuccess', !$auditSuccess, $auditSuccess);
			$aces[$index]->setAuditSuccess($auditSuccess);
		}

		if ($auditFailure !== $aces[$index]->isAuditFailure()) {
			$this->onEntryPropertyChanged($aces[$index], 'auditFailure', !$auditFailure, $auditFailure);
			$aces[$index]->setAuditFailure($auditFailure);
		}
	}

	/**
	 * Updates a field-based ACE
	 *
	 * @param string $property
	 * @param integer $index
	 * @param string $field
	 * @param integer $mask
	 * @param string $strategy
	 * @throws \InvalidArgumentException
	 * @throws \OutOfBoundsException
	 * @return void
	 */
	private function updateFieldAce($property, $index, $field, $mask, $strategy = null) {
		if (0 === strlen($field)) {
			throw new \InvalidArgumentException('$field cannot be empty.');
		}

		$aces = &$this->$property;
		if (!isset($aces[$field][$index])) {
			throw new \OutOfBoundsException(sprintf('The index "%d" does not exist.', $index));
		}

		$ace = $aces[$field][$index];
		if ($mask !== $oldMask = $ace->getMask()) {
			$this->onEntryPropertyChanged($ace, 'mask', $oldMask, $mask);
			$ace->setMask($mask);
		}
		if (null !== $strategy && $strategy !== $oldStrategy = $ace->getStrategy()) {
			$this->onEntryPropertyChanged($ace, 'strategy', $oldStrategy, $strategy);
			$ace->setStrategy($strategy);
		}
	}

	/**
	 * Called when a property of the ACL changes
	 *
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $newValue
	 * @return void
	 */
	private function onPropertyChanged($name, $oldValue, $newValue) {
		foreach ($this->listeners as $listener) {
			$listener->propertyChanged($this, $name, $oldValue, $newValue);
		}
	}

	/**
	 * Called when a property of an ACE associated with this ACL changes
	 *
	 * @param EntryInterface $entry
	 * @param string         $name
	 * @param mixed          $oldValue
	 * @param mixed          $newValue
	 * @return void
	 */
	private function onEntryPropertyChanged(EntryInterface $entry, $name, $oldValue, $newValue) {
		foreach ($this->listeners as $listener) {
			$listener->propertyChanged($entry, $name, $oldValue, $newValue);
		}
	}
}
