<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Templating\Loader;

use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class TemplateLocatorTest extends TestCase {
	public function testLocateATemplate() {
		$template = new TemplateReference('bundle', 'controller', 'name', 'format', 'engine');

		$fileLocator = $this->getFileLocator();

		$fileLocator->expects($this->once())->method('locate')->with($template->getPath())->will($this->returnValue('/path/to/template'));

		$locator = new TemplateLocator($fileLocator);

		$this->assertEquals('/path/to/template', $locator->locate($template));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testThrowsExceptionWhenTemplateNotFound() {
		$template = new TemplateReference('bundle', 'controller', 'name', 'format', 'engine');

		$fileLocator = $this->getFileLocator();

		$fileLocator->expects($this->once())->method('locate')->will($this->throwException(new \InvalidArgumentException()));

		$locator = new TemplateLocator($fileLocator);

		$locator->locate($template);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testThrowsAnExceptionWhenTemplateIsNotATemplateReferenceInterface() {
		$locator = new TemplateLocator($this->getFileLocator());
		$locator->locate('template');
	}

	protected function getFileLocator() {
		return $this->getMockBuilder('Symfony\Component\Config\FileLocator')->setMethods(array('locate'))->setConstructorArgs(array('/path/to/fallback'))->getMock();
	}
}
