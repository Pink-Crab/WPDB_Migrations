<?php

declare(strict_types=1);
/**
 * An abstract class for defninig migrations
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
use PinkCrab\Table_Builder\Schema;
use PinkCrab\Table_Builder\Builder;


use wpdb;

abstract class Database_Migration {


	/**
	 * The table builder.
	 *
	 * @var Builder
	 */
	protected $builder;

	/**
	 * The tables schema.
	 *
	 * @var Schema
	 */
	protected $schema;

	/**
	 * Access to wpdb
	 *
	 * @var wpdb
	 */
	protected $wpdb;

	public function __construct( Builder $builder, wpdb $wpdb ) {
		$this->builder = $builder;
		$this->wpdb    = $wpdb;

		// Set the schema.
		$this->set_schema();
	}

	/**
	 * Used to either import or define the schema.
	 *
	 * @return void
	 */
	abstract public function set_schema(): void;

	/**
	 * Method is called after the table is created.
	 * Can be overwritten to insert inital data etc.
	 *
	 * @return void
	 */
	protected function post_up(): void {}

	/**
	 * Used to create the table.
	 *
	 * @return void
	 */
	final public function up(): void {

		if ( ! is_a( $this->schema, Schema::class ) ) {
			throw new Exception( 'No valid schema suppled' );
		}

		// Run table through builder.
		$this->builder->create_table( $this->schema );

		// Allow hook in create inital data.
		$this->post_up();
	}

	/**
	 * Called on taredown.
	 *
	 * @return void
	 */
	final public function down(): void {

		if ( ! is_a( $this->schema, Schema::class ) ) {
			throw new Exception( 'No valid schema suppled' );
		}

		$this->wpdb->get_results(
			"DROP TABLE IF EXISTS {$this->schema->get_table_name()};" // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}


}
