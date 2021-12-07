<?php

declare(strict_types=1);

/**
 * Manager for handling migrations.
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

use wpdb;
use PinkCrab\Table_Builder\Builder;
use PinkCrab\DB_Migration\Database_Migration;
use PinkCrab\DB_Migration\Log\Migration_Log_Manager;

class Migration_Manager {

	/**
	 * All Schemas to be migrated/seeded
	 *
	 * @var array<Database_Migration>
	 */
	protected $migrations = array();

	/**
	 * The table builder.
	 *
	 * @var Builder
	 */
	protected $builder;

	/**
	 * Access to wpdb
	 *
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * The log keeps for the migrations.
	 *
	 * @var Migration_Log_Manager
	 */
	protected $migration_log;

	public function __construct( Builder $builder, wpdb $wpdb, ?string $migration_log_key = null ) {
		$this->builder       = $builder;
		$this->wpdb          = $wpdb;
		$this->migration_log = new Migration_Log_Manager( $migration_log_key );

	}

	/**
	 * Returns access to the migration log.
	 *
	 * @return \PinkCrab\DB_Migration\Log\Migration_Log_Manager
	 */
	public function migation_log(): Migration_Log_Manager {
		return $this->migration_log;
	}

	/**
	 * Adds a migration to the collection.
	 *
	 * @param  \PinkCrab\DB_Migration\Database_Migration $migration
	 * @return self
	 */
	public function add_migration( Database_Migration $migration ): self {
		$this->migrations[ $migration->get_table_name() ] = $migration;
		return $this;
	}

	/**
	 * Returns all the migrations held in collection.
	 *
	 * @return array<Database_Migration>
	 */
	public function get_migrations(): array {
		return $this->migrations;
	}

	/**
	 * Creates all the tables in collection excepct those passed as exlcuded
	 *
	 * @param string ...$exlcude_table Table names to exclude.
	 * @return self
	 */
	public function create_tables( string ...$exlcude_table ): self {

		// Remove exlcluded tables.
		$to_create = array_filter(
			$this->migrations,
			function( Database_Migration $migration ) use ( $exlcude_table ): bool {
				return ! in_array( $migration->get_table_name(), $exlcude_table, true )
				&& $this->migration_log->can_migrate( $migration->get_schema() );
			}
		);

		// Upsert Tables.
		foreach ( $to_create as $migration ) {
			$result = $this->builder->create_table( $migration->get_schema() );
			if ( $result === true ) {
				$this->migration_log->upsert_migration( $migration->get_schema() );
			}
		}

		return $this;

	}

	/**
	 * Seeds all the tables in collection excepct those passed as exlcuded
	 *
	 * @param string ...$exlcude_table Table names to exclude.
	 * @return self
	 */
	public function seed_tables( string ...$exlcude_table ): self {

		// Remove exlcluded tables.
		$to_seed = array_filter(
			$this->migrations,
			function( Database_Migration $migration ) use ( $exlcude_table ): bool {
				return ! in_array( $migration->get_table_name(), $exlcude_table, true )
				&& ! $this->migration_log->is_seeded( $migration->get_schema() );
			}
		);

		$seeder = new Migration_Seeder( $this->wpdb );

		foreach ( $to_seed as $migration ) {
			$row = $seeder->seed( $migration->get_schema(), $migration->get_seeds() );
			if ( count( $row ) !== 0 ) {
				$this->migration_log->mark_table_seeded( $migration->get_schema() );
			}
		}

		return $this;
	}

	/**
	 * Removes all the tables in collection excepct those passed as exlcuded
	 *
	 * @param string ...$exlcude_table Table names to exclude.
	 * @return self
	 */
	public function drop_tables( string ...$exlcude_table ): self {
		// Remove exlcluded tables.
		$to_seed = array_filter(
			$this->migrations,
			function( Database_Migration $migration ) use ( $exlcude_table ): bool {
				return ! in_array( $migration->get_table_name(), $exlcude_table, true );
			}
		);

		foreach ( $to_seed as $migration ) {

			$result = $this->builder->drop_table( $migration->get_schema() );

			// Throw exception if fails.
			if ( $result === false ) {
				throw Migration_Exception::failed_to_drop_table( $migration->get_table_name() );
			}

			// Remove mitation from log.
			$this->migration_log->remove_migration( $migration->get_schema() );
		}

		return $this;
	}
}
