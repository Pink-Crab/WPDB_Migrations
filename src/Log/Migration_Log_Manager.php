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

namespace PinkCrab\DB_Migration\Log;

use InvalidArgumentException;
use PinkCrab\Table_Builder\Schema;
use PinkCrab\DB_Migration\Log\Migration_Log;

class Migration_Log_Manager {

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
	 * @var array<Migration_Log>
	 */
	protected $migration_details = array();

	public function __construct( string $option_key = null ) {
		$this->option_key = $option_key ?? 'pinkcrab_migration_log';
		$this->set_migration_details();
	}

	/**
	 * Sets the migration details held in optiosn
	 *
	 * @return void
	 * @throws InvalidArgumentException If can not unserialize
	 */
	protected function set_migration_details(): void {
		$migrations = get_option( $this->option_key, null );

		if ( $migrations === null ) {
			return;
		}

		// Handle errors as exceptions.
		set_error_handler( // phpcs:ignore
			function ( int $errno, string $errstr ) { // phpcs:ignore
				throw new InvalidArgumentException( 'Migration details as unserialize from options, failed to be decoded.' );
			}
		);
		$migrations = \unserialize( $migrations ); // phpcs:ignore
		restore_error_handler();

		$this->migration_details = $migrations;
	}

	/**
	 * Checks if a table has been migrated
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return boolean
	 */
	public function has_migration( Schema $schema ): bool {
		return array_key_exists( $schema->get_table_name(), $this->migration_details );
	}

	/**
	 * Gets a migration log based on its name
	 *
	 * @return \PinkCrab\DB_Migration\Log\Migration_Log|null
	 */
	public function get_migration( Schema $schema ): ?Migration_Log {
		return $this->has_migration( $schema )
			? $this->migration_details[ $schema->get_table_name() ]
			: null;
	}

	/**
	 * Check if a table can be migrated.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return bool
	 */
	public function can_migrate( Schema $schema ): bool {
		return ! $this->check_hash( $schema );
	}

	/**
	 * Checks if the define table has been seeded.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return bool
	 */
	public function is_seeded( Schema $schema ): bool {
		return $this->has_migration( $schema )
		&& $this->migration_details[ $schema->get_table_name() ]->is_seeded();
	}

	/**
	 * Checks if the passed schema's hash matches the existing schema hash.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return bool
	 */
	public function check_hash( Schema $schema ): bool {
		// If table doesnt exist, return false;
		if ( ! $this->has_migration( $schema ) ) {
			return false;
		}

		$schema_hash = Migration_Log::compose_column_hash( $schema );
		$migration   = $this->migration_details[ $schema->get_table_name() ];
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
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return self
	 */
	public function upsert_migration( Schema $schema ): self {
		// Update if table exists and we have a new schema defined.
		if ( $this->has_migration( $schema )
		&& ! $this->check_hash( $schema ) ) {
			$this->migration_details[ $schema->get_table_name() ] =
				$this->migration_details[ $schema->get_table_name() ]->as_updated( $schema );

			$this->save();
		}

		// If a new hash.
		if ( $this->has_migration( $schema ) === false ) {
			$this->migration_details[ $schema->get_table_name() ] =
				Migration_Log::new_from_schema( $schema );

			$this->save();
		}

		return $this;
	}

	/**
	 * Removes a migration from the log based on its schema.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return self
	 */
	public function remove_migration( Schema $schema ): self {
		if ( $this->has_migration( $schema ) ) {
			unset( $this->migration_details[ $schema->get_table_name() ] );

			$this->save();
		}
		return $this;
	}

	/**
	 * If the table has not already been seeded, update the to denote it has.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @return self
	 */
	public function mark_table_seeded( Schema $schema ): self {
		if ( ! $this->is_seeded( $schema ) ) {
			$this->migration_details[ $schema->get_table_name() ] =
				$this->migration_details[ $schema->get_table_name() ]->as_seeded();

			$this->save();
		}

		return $this;
	}

	/**
	 * Saves the current migration details
	 *
	 * @return void
	 */
	protected function save(): void {
		\update_option( $this->option_key, serialize( $this->migration_details ) ); // phpcs:ignore
	}

	/**
	 * Returns the migration log key
	 *
	 * @return string
	 */
	public function get_log_key(): string {
		return $this->option_key;
	}
}
