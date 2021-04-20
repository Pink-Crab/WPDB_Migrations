<?php

declare(strict_types=1);

/**
 * Base test case.
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests;

use Exception;
use WP_UnitTestCase;
use PinkCrab\Table_Builder\Builder;
use PinkCrab\PHPUnit_Helpers\Arrays;
use PinkCrab\FunctionConstructors\Arrays as Arr;
use PinkCrab\DB_Migration\Tests\Stubs\Stub_Migration;
use PinkCrab\Table_Builder\Engines\WPDB_DB_Delta\DB_Delta_Engine;


class Test_Migration extends WP_UnitTestCase {

	protected $table_name = 'test_table';

	/**
	 * WPDB
	 *
	 * @var wpdb
	 */
	protected $wpdb;


	/**
	 * Populate with wpdb
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setup();

		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Drops the test table
	 *
	 * @return void
	 */
	public function drop_test_table(): void {
		// Always drop the table.
		$this->wpdb->get_results( "DROP TABLE IF EXISTS {$this->table_name};" );
	}

	/**
	 * Returns the current rows in the table.
	 *
	 * @return array
	 */
	public function check_has_values_created_on_post_up(): array {
		return  $this->wpdb->get_results( "SELECT * FROM {$this->table_name};" );
	}

	/**
	 * Test that a table is created from a migration.
	 *
	 * @successful test
	 * @return void
	 */
	public function test_table_is_created(): void {
		// Build table on UP
		$migration = new Stub_Migration( 
			new Builder(new DB_Delta_Engine($this->wpdb)), 
			$this->wpdb 
		);
		$migration->up();

		$this->assertNotEmpty( $this->check_has_values_created_on_post_up() );

		$table_details = $this->wpdb->get_results( "SHOW COLUMNS FROM {$this->table_name};" );

		// Check we have the 5 columns expected.
		$this->assertCount( 5, $table_details );

		// Check all columns exists.
		$expected = array( 'id', 'user', 'filters', 'date_created', 'last_update' );
		foreach ( $expected as $column_name ) {
			$this->assertNotEmpty(
				Arr\filterFirst(
					function( $col ) use ( $column_name ) {
						return $col->Field === $column_name;
					}
				)
				($table_details)
			);
		}

		$this->drop_test_table();
	}

	/**
	 * Test new columns are added if schema has changed.
	 *
	 * @return void
	 */
	public function test_table_is_updated() {
		$migration = new Stub_Migration( 
			new Builder(new DB_Delta_Engine($this->wpdb)), 
			$this->wpdb 
		);
		$migration->up();

		$this->assertNotEmpty( $this->check_has_values_created_on_post_up() );

		$table_details = $this->wpdb->get_results( "SHOW COLUMNS FROM {$this->table_name};" );
		// Check we have the 5 columns expected.
		$this->assertCount( 5, $table_details );

		// Now run again wtih different schema.
		// This renames 2 columns, but as DB_Delta cant do change or remove, we should have 7!
		$migration->change_schema(); // THIS IS A TEST METHOD!
		$migration->up();
		$table_details = $this->wpdb->get_results( "SHOW COLUMNS FROM {$this->table_name};" );
		$this->assertCount( 7, $table_details );

		$expected = array( 'id', 'user', 'filters', 'date_created', 'last_update', 'username', 'foo' );
		foreach ( $expected as $column_name ) {
			$this->assertNotEmpty(
				Arr\filterFirst(
					function( $col ) use ( $column_name ) {
						return $col->Field === $column_name;
					}
				)
				($table_details)
			);
		}
		$this->drop_test_table();
	}

	/**
	 * Test throws an exception if not valid schema set.
	 *
	 * @return void
	 */
	public function test_throws_exception_with_no_schema(): void {
		$this->expectException( Exception::class );
		$migration = new Stub_Migration( 
			new Builder(new DB_Delta_Engine($this->wpdb)), 
			$this->wpdb 
		);

		// Set the schema to null.
		$migration->edit_schema(
			function( $e ) {
				return null; // Set the schema to null
			}
		);

		// Attempt to build.
		$migration->up();
	}

	/**
	 * Check table is dropped when down() is run.
	 *
	 * @return void
	 */
	public function test_table_droppd_on_down(): void {
		$migration = new Stub_Migration( 
			new Builder(new DB_Delta_Engine($this->wpdb)), 
			$this->wpdb 
		);
		$migration->up();

		$this->assertNotEmpty( $this->check_has_values_created_on_post_up() );

		$this->assertNotEmpty( $this->wpdb->get_results( "SHOW COLUMNS FROM {$this->table_name};" ) );

		// Now run it down.
		$migration->down();
		$this->assertEmpty( $this->wpdb->get_results( "SHOW TABLES LIKE '{$this->table_name}';" ) );
	}

	/**
	 * Test throws an exception if not valid schema set.
	 *
	 * @return void
	 */
	public function test_down_throws_exception_with_no_schema(): void {
		$this->expectException( Exception::class );

		// Create the table.
		$migration = new Stub_Migration( 
			new Builder(new DB_Delta_Engine($this->wpdb)), 
			$this->wpdb 
		);
		$migration->up();

		$this->assertNotEmpty( $this->check_has_values_created_on_post_up() );

		// Set the schema to null.
		$migration->edit_schema(
			function( $e ) {
				return null;
			}
		);
		// Attempt to call down with no schema should throw
		$migration->down();
	}
}
