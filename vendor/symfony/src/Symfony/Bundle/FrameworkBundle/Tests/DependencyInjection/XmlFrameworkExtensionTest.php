<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class XmlFrameworkExtensionTest extends FrameworkExtensionTest {
	protected function loadFromFile(ContainerBuilder $container, $file) {
		$loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Fixtures/xml'));
		$loader->load($file . '.xml');
	}
}
