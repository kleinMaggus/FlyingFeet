<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\EventDispatcher;

/**
 * Event is the base class for classes containing event data.
 *
 * This class contains no event data. It is used by events that do not pass
 * state information to an event handler when an event is raised.
 *
 * You can call the method stopPropagation() to abort the execution of
 * further listeners in your event listener.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision: 3938 $
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Event {
	/**
	 * @var Boolean Whether no further event listeners should be triggered
	 */
	private $propagationStopped = false;

	/**
	 * Returns whether further event listeners should be triggered.
	 *
	 * @see Event::stopPropagation
	 * @return Boolean Whether propagation was already stopped for this event.
	 *
	 * @api
	 */
	public function isPropagationStopped() {
		return $this->propagationStopped;
	}

	/**
	 * Stops the propagation of the event to further event listeners.
	 *
	 * If multiple event listeners are connected to the same event, no
	 * further event listener will be triggered once any trigger calls
	 * stopPropagation().
	 *
	 * @api
	 */
	public function stopPropagation() {
		$this->propagationStopped = true;
	}
}
