<?php

declare(strict_types=1);

/**
 * Seeds the table with the defined data.
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
use PinkCrab\Table_Builder\Schema;

class Migration_Seeder {

	/**
	 * WPDB isntance
	 *
	 * @var wpdb
	 */
	protected $wpdb;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * Seeds a specific table with the data passed.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @param array<array<string, mixed>> $seed_data
	 * @return array<string, int>
	 */
	public function seed( Schema $schema, array $seed_data ): array {
		$results = array();

		foreach ( $seed_data as $key => $seed ) {
			$results[ $key ] = $this->insert_seed( $schema, $seed );
		}

		return $results;
	}

	/**
	 * Inserts a row of seed data.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @param array<array<string, mixed>> $seed
	 * @return int The new row ID.
	 * @throws Migration_Exception
	 */
	protected function insert_seed( Schema $schema, array $seed ): int {
		// Get format for each column based on the type.
		$format = array_map(
			function( string $column_name ) use ( $schema ): string {
				return $this->column_type( $schema, $column_name );
			},
			array_keys( $seed )
		);

		$this->wpdb->insert(
			$schema->get_table_name(),
			$seed,
			$format
		);

		// Check any errors inserting.
		if ( $this->wpdb->last_error !== '' ) {
			throw Migration_Exception::failed_to_insert_seed( $this->wpdb->last_error, $schema->get_table_name() );
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Gets the column data type from schema.
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @param string $column
	 * @return string
	 * @throws Migration_Exception If a column cant be found.
	 */
	protected function column_type( Schema $schema, string $column ): string {
		$schema_columns = $schema->get_columns();

		// If colum doesnt exist, thorw exception.
		if ( ! array_key_exists( $column, $schema_columns ) ) {
			throw Migration_Exception::seed_column_doesnt_exist( $column, $schema->get_table_name() );
		}

		return $this->column_type_format( $schema_columns[ $column ]->get_type() ?? '' );
	}

	/**
	 * Returns the WPDB prepare format based on column type.
	 *
	 * @param string $type
	 * @return string
	 */
	protected function column_type_format( string $type ): string {
		switch ( \strtoupper( $type ) ) {
			case 'CHAR':
			case 'VARCHAR':
			case 'BINARY':
			case 'VARBINARY':
			case 'TINYBLOB':
			case 'TINYTEXT':
			case 'TEXT':
			case 'BLOB':
			case 'MEDIUMTEXT':
			case 'MEDIUMBLOB':
			case 'LONGTEXT':
			case 'LONGBLOB':
			case 'DATE':
			case 'DATETIME':
			case 'TIMESTAMP':
			case 'TIME':
			case 'YEAR':
				return '%s';

			case 'BIT':
			case 'TINYINT':
			case 'BOOL':
			case 'BOOLEAN':
			case 'SMALLINT':
			case 'MEDIUMINT':
			case 'INT':
			case 'INTEGER':
			case 'BIGINT':
				return '%d';

			case 'FLOAT':
			case 'DOUBLE':
			case 'DOUBLE PRECISION':
			case 'DECIMAL':
			case 'DEC':
				return '%f';

			default:
				return '%s';
		}
	}
}
