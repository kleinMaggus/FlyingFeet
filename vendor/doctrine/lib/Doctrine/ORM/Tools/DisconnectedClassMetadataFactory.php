<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Tools;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * The DisconnectedClassMetadataFactory is used to create ClassMetadataInfo objects
 * that do not require the entity class actually exist. This allows us to 
 * load some mapping information and use it to do things like generate code
 * from the mapping information.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class DisconnectedClassMetadataFactory extends ClassMetadataFactory {
	/**
	 * @override
	 */
	protected function newClassMetadataInstance($className) {
		$metadata = new ClassMetadataInfo($className);
		if (strpos($className, "\\") !== false) {
			$metadata->namespace = strrev(substr(strrev($className), strpos(strrev($className), "\\") + 1));
		} else {
			$metadata->namespace = "";
		}
		return $metadata;
	}

	/**
	 * Validate runtime metadata is correctly defined.
	 *
	 * @param ClassMetadata $class
	 * @param ClassMetadata $parent
	 */
	protected function validateRuntimeMetadata($class, $parent) {
		// validate nothing
	}

	/**
	 * @override
	 */
	protected function getParentClasses($name) {
		return array();
	}
}
