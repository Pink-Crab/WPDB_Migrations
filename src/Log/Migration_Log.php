<?php

declare(strict_types=1);

/**
 * Model for migration log.
 *
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
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\DB_Migration
 */

namespace PinkCrab\DB_Migration\Log;

use DateTimeImmutable;
use PinkCrab\Table_Builder\Schema;

class Migration_Log {


	/**
	 * The table name
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * Has of the table columns
	 *
	 * @var string
	 */
	protected $schema_hash;

	/**
	 * Denotes if the table has been seeded with data.
	 *
	 * @var bool
	 */
	protected $seeded = false;

	/**
	 * Date the migration was created on
	 *
	 * @var DateTimeImmutable
	 */
	protected $created_on;

	/**
	 * Date the migration was last updated
	 *
	 * @var DateTimeImmutable
	 */
	protected $updated_on;

	public function __construct(
		string $table_name,
		string $schema_hash,
		bool $seeded,
		DateTimeImmutable $created_on,
		?DateTimeImmutable $updated_on = null
	) {
		$this->table_name  = $table_name;
		$this->schema_hash = $schema_hash;
		$this->seeded      = $seeded;
		$this->created_on  = $created_on;
		$this->updated_on  = $updated_on ?? $created_on;
	}

	/** NAMED CONSTRUCTORS */

	/**
	 * Creates a new Migration record.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return self
	 */
	public static function new_from_schema( Schema $schema ): self {
		return new self(
			$schema->get_table_name(),
			self::compose_column_hash( $schema ),
			false,
			new DateTimeImmutable(),
			new DateTimeImmutable()
		);
	}

	/**
	 * Returns a new instance with the defined updated schema and updated data
	 *
	 * @param \DateTimeImmutable|null $updated_on
	 * @return self
	 */
	public function as_updated( Schema $schema, ?DateTimeImmutable $updated_on = null ): self {
		return new self(
			$this->table_name(),
			self::compose_column_hash( $schema ),
			$this->is_seeded(),
			$this->created_on(),
			$updated_on ?? new DateTimeImmutable()
		);
	}

	/**
	 * Returns a new instace of itself, marked as seeded.
	 *
	 * @param \DateTimeImmutable|null $updated_on
	 * @return self
	 */
	public function as_seeded( ?DateTimeImmutable $updated_on = null ): self {
		return new self(
			$this->table_name(),
			$this->schema_hash(),
			true,
			$this->created_on(),
			$updated_on ?? new DateTimeImmutable()
		);
	}

	/**
	 * Generates the column hash from the tables schema.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return string
	 */
	public static function compose_column_hash( Schema $schema ): string {
		$export = array(
			'name'         => $schema->get_table_name(),
			'columns'      => $schema->get_columns(),
			'indexes'      => $schema->get_indexes(),
			'foreign_keys' => $schema->get_foreign_keys(),
		);

		return md5( \serialize( $export ) ?: $schema->get_table_name() );  // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize, Serialised to preserve types
	}

	/** GETTERS */

	/**
	 * Get the table name
	 *
	 * @return string
	 */
	public function table_name(): string {
		return $this->table_name;
	}

	/**
	 * Get has of the table columns
	 *
	 * @return string
	 */
	public function schema_hash(): string {
		return $this->schema_hash;
	}

	/**
	 * Get date the migration was last updated
	 *
	 * @return DateTimeImmutable
	 */
	public function updated_on(): DateTimeImmutable {
		return $this->updated_on;
	}

	/**
	 * Get date the migration was created on
	 *
	 * @return DateTimeImmutable
	 */
	public function created_on(): DateTimeImmutable {
		return $this->created_on;
	}

	/**
	 * Get denotes if the table has been seeded with data.
	 *
	 * @return bool
	 */
	public function is_seeded(): bool {
		return $this->seeded;
	}
}
