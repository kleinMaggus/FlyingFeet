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

namespace Doctrine\DBAL\Query\Expression;

use Doctrine\DBAL\Connection;

/**
 * ExpressionBuilder class is responsible to dynamically create SQL query parts.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       2.1
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Benjamin Eberlei <kontakt@beberlei.de>
 */
class ExpressionBuilder {
	const EQ = '=';
	const NEQ = '<>';
	const LT = '<';
	const LTE = '<=';
	const GT = '>';
	const GTE = '>=';

	/**
	 * @var Doctrine\DBAL\Connection DBAL Connection
	 */
	private $connection = null;

	/**
	 * Initializes a new <tt>ExpressionBuilder</tt>.
	 *
	 * @param Doctrine\DBAL\Connection $connection DBAL Connection
	 */
	public function __construct(Connection $connection) {
		$this->connection = $connection;
	}

	/**
	 * Creates a conjunction of the given boolean expressions.
	 *
	 * Example:
	 *
	 *     [php]
	 *     // (u.type = ?) AND (u.role = ?)
	 *     $expr->andX('u.type = ?', 'u.role = ?'));
	 *
	 * @param mixed $x Optional clause. Defaults = null, but requires
	 *                 at least one defined when converting to string.
	 * @return CompositeExpression
	 */
	public function andX($x = null) {
		return new CompositeExpression(CompositeExpression::TYPE_AND, func_get_args());
	}

	/**
	 * Creates a disjunction of the given boolean expressions.
	 *
	 * Example:
	 *
	 *     [php]
	 *     // (u.type = ?) OR (u.role = ?)
	 *     $qb->where($qb->expr()->orX('u.type = ?', 'u.role = ?'));
	 *
	 * @param mixed $x Optional clause. Defaults = null, but requires
	 *                 at least one defined when converting to string.
	 * @return CompositeExpression
	 */
	public function orX($x = null) {
		return new CompositeExpression(CompositeExpression::TYPE_OR, func_get_args());
	}

	/**
	 * Creates a comparison expression.
	 * 
	 * @param mixed $x Left expression
	 * @param string $operator One of the ExpressionBuikder::* constants.
	 * @param mixed $y Right expression
	 * @return string
	 */
	public function comparison($x, $operator, $y) {
		return $x . ' ' . $operator . ' ' . $y;
	}

	/**
	 * Creates an equality comparison expression with the given arguments.
	 *
	 * First argument is considered the left expression and the second is the right expression.
	 * When converted to string, it will generated a <left expr> = <right expr>. Example:
	 *
	 *     [php]
	 *     // u.id = ?
	 *     $expr->eq('u.id', '?');
	 *
	 * @param mixed $x Left expression
	 * @param mixed $y Right expression
	 * @return string
	 */
	public function eq($x, $y) {
		return $this->comparison($x, self::EQ, $y);
	}

	/**
	 * Creates a non equality comparison expression with the given arguments.
	 * First argument is considered the left expression and the second is the right expression.
	 * When converted to string, it will generated a <left expr> <> <right expr>. Example:
	 *
	 *     [php]
	 *     // u.id <> 1
	 *     $q->where($q->expr()->neq('u.id', '1'));
	 *
	 * @param mixed $x Left expression
	 * @param mixed $y Right expression
	 * @return string
	 */
	public function neq($x, $y) {
		return $this->comparison($x, self::NEQ, $y);
	}

	/**
	 * Creates a lower-than comparison expression with the given arguments.
	 * First argument is considered the left expression and the second is the right expression.
	 * When converted to string, it will generated a <left expr> < <right expr>. Example:
	 *
	 *     [php]
	 *     // u.id < ?
	 *     $q->where($q->expr()->lt('u.id', '?'));
	 *
	 * @param mixed $x Left expression
	 * @param mixed $y Right expression
	 * @return string
	 */
	public function lt($x, $y) {
		return $this->comparison($x, self::LT, $y);
	}

	/**
	 * Creates a lower-than-equal comparison expression with the given arguments.
	 * First argument is considered the left expression and the second is the right expression.
	 * When converted to string, it will generated a <left expr> <= <right expr>. Example:
	 *
	 *     [php]
	 *     // u.id <= ?
	 *     $q->where($q->expr()->lte('u.id', '?'));
	 *
	 * @param mixed $x Left expression
	 * @param mixed $y Right expression
	 * @return string
	 */
	public function lte($x, $y) {
		return $this->comparison($x, self::LTE, $y);
	}

	/**
	 * Creates a greater-than comparison expression with the given arguments.
	 * First argument is considered the left expression and the second is the right expression.
	 * When converted to string, it will generated a <left expr> > <right expr>. Example:
	 *
	 *     [php]
	 *     // u.id > ?
	 *     $q->where($q->expr()->gt('u.id', '?'));
	 *
	 * @param mixed $x Left expression
	 * @param mixed $y Right expression
	 * @return string
	 */
	public function gt($x, $y) {
		return $this->comparison($x, self::GT, $y);
	}

	/**
	 * Creates a greater-than-equal comparison expression with the given arguments.
	 * First argument is considered the left expression and the second is the right expression.
	 * When converted to string, it will generated a <left expr> >= <right expr>. Example:
	 *
	 *     [php]
	 *     // u.id >= ?
	 *     $q->where($q->expr()->gte('u.id', '?'));
	 *
	 * @param mixed $x Left expression
	 * @param mixed $y Right expression
	 * @return string
	 */
	public function gte($x, $y) {
		return $this->comparison($x, self::GTE, $y);
	}

	/**
	 * Creates an IS NULL expression with the given arguments.
	 *
	 * @param string $x Field in string format to be restricted by IS NULL
	 * 
	 * @return string
	 */
	public function isNull($x) {
		return $x . ' IS NULL';
	}

	/**
	 * Creates an IS NOT NULL expression with the given arguments.
	 *
	 * @param string $x Field in string format to be restricted by IS NOT NULL
	 * 
	 * @return string
	 */
	public function isNotNull($x) {
		return $x . ' IS NOT NULL';
	}

	/**
	 * Creates a LIKE() comparison expression with the given arguments.
	 *
	 * @param string $x Field in string format to be inspected by LIKE() comparison.
	 * @param mixed $y Argument to be used in LIKE() comparison.
	 * 
	 * @return string
	 */
	public function like($x, $y) {
		return $this->comparison($x, 'LIKE', $y);
	}

	/**
	 * Quotes a given input parameter.
	 * 
	 * @param mixed $input Parameter to be quoted.
	 * @param string $type Type of the parameter.
	 * 
	 * @return string
	 */
	public function literal($input, $type = null) {
		return $this->connection->quote($input, $type);
	}
}
