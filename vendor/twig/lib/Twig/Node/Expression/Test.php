<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Node_Expression_Test extends Twig_Node_Expression {
	public function __construct(Twig_NodeInterface $node, $name, Twig_NodeInterface $arguments = null, $lineno) {
		parent::__construct(array('node' => $node, 'arguments' => $arguments), array('name' => $name), $lineno);
	}

	public function compile(Twig_Compiler $compiler) {
		$testMap = $compiler->getEnvironment()->getTests();
		if (!isset($testMap[$this->getAttribute('name')])) {
			throw new Twig_Error_Syntax(sprintf('The test "%s" does not exist', $this->getAttribute('name')), $this->getLine());
		}

		$name = $this->getAttribute('name');
		$node = $this->getNode('node');

		// defined is a special case
		if ('defined' === $name) {
			if ($node instanceof Twig_Node_Expression_Name || $node instanceof Twig_Node_Expression_GetAttr) {
				$node->setAttribute('is_defined_test', true);
				$compiler->subcompile($node);
				$node->removeAttribute('is_defined_test');
			} else {
				throw new Twig_Error_Syntax('The "defined" test only works with simple variables', $this->getLine());
			}
			return;
		}

		$compiler->raw($testMap[$name]->compile() . '(')->subcompile($node);

		if (null !== $this->getNode('arguments')) {
			$compiler->raw(', ');

			$max = count($this->getNode('arguments')) - 1;
			foreach ($this->getNode('arguments') as $i => $arg) {
				$compiler->subcompile($arg);

				if ($i != $max) {
					$compiler->raw(', ');
				}
			}
		}

		$compiler->raw(')');
	}
}
