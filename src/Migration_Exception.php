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

class Migration_Exception extends Exception {

	/**
	 * Exception for column in seed data not existing in schema.
	 *
	 * @param string $column
	 * @param string $table_name
	 * @return Migration_Exception
	 * @code 1
	 */
	public static function seed_column_doesnt_exist( string $column, string $table_name ): Migration_Exception {
		return new Migration_Exception(
			\sprintf( 'Could not find column %s in %s schema definition', $column, $table_name ),
			1
		);
	}

	/**
	 * Exception for failure to insert seed data.
	 *
	 * @param string $wpdb_error
	 * @param string $table_name
	 * @return Migration_Exception
	 * @code 2
	 */
	public static function failed_to_insert_seed( string $wpdb_error, string $table_name ): Migration_Exception {
		return new Migration_Exception(
			\sprintf( 'Could not insert seed into %s, failed with error: %s', $table_name, $wpdb_error ),
			2
		);
	}

	/**
	 * Exception for failure to drop a table
	 *
	 * @param string $table_name
	 * @return Migration_Exception
	 * @code 3
	 */
	public static function failed_to_drop_table( string $table_name ): Migration_Exception {
		return new Migration_Exception(
			\sprintf( 'Failed to drop %d', $table_name ),
			3
		);
	}
}
