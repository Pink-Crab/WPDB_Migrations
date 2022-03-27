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

use Exception;
use Throwable;
use PinkCrab\Table_Builder\Schema;

class Migration_Exception extends Exception {

	/**
	 * Schema definition
	 *
	 * @var Schema
	 */
	protected $schema;

	/**
	 * WPDB Error
	 *
	 * @var string
	 */
	protected $wpdb_error;

	/**
	 * Create instance of Migration_Exception
	 *
	 * @param \PinkCrab\Table_Builder\Schema $schema
	 * @param string $wpdb_error
	 * @param string $message
	 * @param int $code
	 * @param Throwable$previous
	 */
	public function __construct( Schema $schema, string $wpdb_error = '', $message = '', $code = 0, Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
		$this->schema     = $schema;
		$this->wpdb_error = $wpdb_error;
	}


	/**
	 * Exception for column in seed data not existing in schema.
	 *
	 * @param Schema $schema
	 * @param string $column
	 * @return Migration_Exception
	 * @code 1
	 */
	public static function seed_column_doesnt_exist( Schema $schema, string $column ): Migration_Exception {
		return new Migration_Exception(
			$schema,
			'',
			\sprintf(
				'Could not find column %s in %s schema definition',
				$column,
				$schema->get_table_name()
			),
			1
		);
	}

	/**
	 * Exception for failure to insert seed data.
	 *
	 * @param Schema $schema
	 * @param string $wpdb_error
	 * @return Migration_Exception
	 * @code 2
	 */
	public static function failed_to_insert_seed( Schema $schema, string $wpdb_error ): Migration_Exception {
		return new Migration_Exception(
			$schema,
			$wpdb_error,
			\sprintf( 'Could not insert seed into %s, failed with error: %s', $schema->get_table_name(), $wpdb_error ),
			2
		);
	}

	/**
	 * Exception for failure to drop a table
	 *
	 * @param Schema $schema
	 * @param string $wpdb_error
	 * @return Migration_Exception
	 * @code 3
	 */
	public static function failed_to_drop_table( Schema $schema, string $wpdb_error ): Migration_Exception {
		return new Migration_Exception(
			$schema,
			$wpdb_error,
			\sprintf( 'Failed to drop %s', $schema->get_table_name() ),
			3
		);
	}

	/**
	 * Get schema definition
	 *
	 * @return Schema
	 */
	public function get_schema(): Schema {
		return $this->schema;
	}

	/**
	 * Get WPDB Error
	 *
	 * @return string
	 */
	public function get_wpdb_error(): string {
		return $this->wpdb_error;
	}
}
