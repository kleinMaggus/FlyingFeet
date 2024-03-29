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

namespace Doctrine\ORM\Tools\Console\Command;

use Doctrine\ORM\Mapping\MappingException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Show information about mapped entities
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.1
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 */
class InfoCommand extends Command {
	protected function configure() {
		$this->setName('orm:info')->setDescription('Show basic information about all mapped entities')->setHelp(<<<EOT
The <info>doctrine:mapping:info</info> shows basic information about which
entities exist and possibly if their mapping information contains errors or
not.
EOT
				);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		/* @var $entityManager Doctrine\ORM\EntityManager */
		$entityManager = $this->getHelper('em')->getEntityManager();

		$entityClassNames = $entityManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();

		if (!$entityClassNames) {
			throw new \Exception('You do not have any mapped Doctrine ORM entities according to the current configuration. ' . 'If you have entities or mapping files you should check your mapping configuration for errors.');
		}

		$output->writeln(sprintf("Found <info>%d</info> mapped entities:", count($entityClassNames)));

		foreach ($entityClassNames as $entityClassName) {
			try {
				$cm = $entityManager->getClassMetadata($entityClassName);
				$output->writeln(sprintf("<info>[OK]</info>   %s", $entityClassName));
			} catch (MappingException $e) {
				$output->writeln("<error>[FAIL]</error> " . $entityClassName);
				$output->writeln(sprintf("<comment>%s</comment>", $e->getMessage()));
				$output->writeln('');
			}
		}
	}
}
