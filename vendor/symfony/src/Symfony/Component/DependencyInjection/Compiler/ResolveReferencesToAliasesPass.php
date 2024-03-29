<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Replaces all references to aliases with references to the actual service.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ResolveReferencesToAliasesPass implements CompilerPassInterface {
	private $container;

	/**
	 * Processes the ContainerBuilder to replace references to aliases with actual service references.
	 *
	 * @param ContainerBuilder $container
	 */
	public function process(ContainerBuilder $container) {
		$this->container = $container;

		foreach ($container->getDefinitions() as $definition) {
			if ($definition->isSynthetic() || $definition->isAbstract()) {
				continue;
			}

			$definition->setArguments($this->processArguments($definition->getArguments()));
			$definition->setMethodCalls($this->processArguments($definition->getMethodCalls()));
			$definition->setProperties($this->processArguments($definition->getProperties()));
		}

		foreach ($container->getAliases() as $id => $alias) {
			$aliasId = (string) $alias;
			if ($aliasId !== $defId = $this->getDefinitionId($aliasId)) {
				$container->setAlias($id, new Alias($defId, $alias->isPublic()));
			}
		}
	}

	/**
	 * Processes the arguments to replace aliases
	 *
	 * @param array $arguments An array of References
	 * @return array An array of References
	 */
	private function processArguments(array $arguments) {
		foreach ($arguments as $k => $argument) {
			if (is_array($argument)) {
				$arguments[$k] = $this->processArguments($argument);
			} elseif ($argument instanceof Reference) {
				$defId = $this->getDefinitionId($id = (string) $argument);

				if ($defId !== $id) {
					$arguments[$k] = new Reference($defId, $argument->getInvalidBehavior(), $argument->isStrict());
				}
			}
		}

		return $arguments;
	}

	/**
	 * Resolve an alias into a definition id
	 *
	 * @param string $id The definition or alias id to resolve
	 * @return string The definition id with aliases resolved
	 */
	private function getDefinitionId($id) {
		while ($this->container->hasAlias($id)) {
			$id = (string) $this->container->getAlias($id);
		}

		return $id;
	}
}
