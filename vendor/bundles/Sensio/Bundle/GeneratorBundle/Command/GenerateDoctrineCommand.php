<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Command;

use Symfony\Bundle\DoctrineBundle\Mapping\MetadataFactory;
use Symfony\Bundle\DoctrineBundle\Command\DoctrineCommand;

abstract class GenerateDoctrineCommand extends DoctrineCommand {
	protected function parseShortcutNotation($shortcut) {
		$entity = str_replace('/', '\\', $shortcut);

		if (false === $pos = strpos($entity, ':')) {
			throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
		}

		return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
	}

	protected function getEntityMetadata($entity) {
		$factory = new MetadataFactory($this->getContainer()->get('doctrine'));

		return $factory->getClassMetadata($entity)->getMetadata();
	}
}
