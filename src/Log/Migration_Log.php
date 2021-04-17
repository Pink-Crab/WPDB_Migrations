<?php

declare(strict_types=1);

/**
 * Holds a log of the last table constructed
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

namespace PinkCrab\DB_Migration;

use stdClass;
use Exception;
use PinkCrab\Table_Builder\Schema;
use PinkCrab\DB_Migration\Log\Migration;

class Migration_Log {

	/**
	 * The key used to hold all migration dates
	 * Can be shared between multiple plugins.
	 *
	 * @var string
	 */
	protected $option_key;

	/**
	 * Current migration details
	 *
	 * @var array<Migration>
	 */
	protected $migration_details = array();

	public function __construct( string $option_key = null ) {
		$this->option_key = $option_key ?? 'pink_migration_log';
		$this->set_migration_details();
	}

	/**
	 * Sets the migration details held in optiosn
	 *
	 * @return void
	 */
	protected function set_migration_details(): void {
		$migrations = get_option( $this->option_key, null );

		if ( $migrations === null ) {
			return;
		}

		try {
			$migrations = \unserialize( $migrations );
		} catch ( \Throwable $th ) {
			throw new Exception( 'Migration details as unserialize from options, failed to be decoded: ' . $th->getMessage() );
		}

		$this->migration_details = $migrations;
	}

	/**
	 * Checks if a table has been migrated
	 *
	 * @param string $table_name
	 * @return boolean
	 */
	public function has_migration( string $table_name ): bool {
		return array_key_exists( $table_name, $this->migration_details );
	}

	/**
	 * Checks if the define table has been seeded.
	 *
	 * @param string $table_name
	 * @return bool
	 */
	public function is_seeded( string $table_name ): bool {
		return $this->has_migration( $table_name )
		&& $this->migration_details[ $table_name ]->is_seeded();
	}

	/**
	 * Checks if the passed schema's hash matches the existing schema hash.
	 *
	 * @param string $table_name
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return bool
	 */
	public function check_hash( string $table_name, Schema $schema ): bool {
		// If table doesnt exist, return false;
		if ( $this->has_migration( $table_name ) ) {
			return false;
		}

		$schema_hash = Migration::compose_column_hash( $schema );
		$migration   = $this->migration_details[ $table_name ];

		return strcmp( $schema_hash, $migration->schema_hash() ) === 0;
	}

	/**
	 * Upserts a migration based on its schema.
	 *
	 * - Upates if Migration exists, but schema is different.
	 * - Creates if Migration doesnt exist.
	 *
	 * Only updates the schema is actually upserted.
	 *
	 * @param string $table_name
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return self
	 */
	public function upsert_migration( string $table_name, Schema $schema ): self {
		// Update if table exists and we have a new schema defined.
		if ( $this->has_migration( $table_name )
		&& ! $this->check_hash( $table_name, $schema ) ) {
			$this->migration_details[ $table_name ] =
				$this->migration_details[ $table_name ]->as_updated( $schema );

			$this->save();
		}

		// If a new hash.
		if ( $this->has_migration( $table_name ) === false ) {
			$this->migration_details[ $table_name ] =
				Migration::new_from_schema( $table_name, $schema );

			$this->save();
		}

		return $this;
	}

	/**
	 * If the table has not already been seeded, update the to denote it has.
	 *
	 * @param string $table_name
	 * @return self
	 */
	public function mark_table_seeded( string $table_name ): self {

		if ( ! $this->is_seeded( $table_name ) ) {
			$this->migration_details[ $table_name ] =
				$this->migration_details[ $table_name ]->as_seeded();

			$this->save();
		}

		return $this;
	}

	/**
	 * Saves the current migration details
	 *
	 * @return void
	 */
	public function save(): void {
		\update_option( $this->option_key, serialize( $this->migration_details ) );
	}



}
