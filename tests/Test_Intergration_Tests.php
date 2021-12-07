<?php

declare(strict_types=1);

/**
 * Intergration tests for the Migaration Manager Service
 *
 * Run Create, Seed and Drop
 *
 * @since 0.3.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests;

use WP_UnitTestCase;
use PinkCrab\Table_Builder\Builder;
use PinkCrab\DB_Migration\Migration_Manager;
use PinkCrab\DB_Migration\Tests\Stubs\Stub_Migration_Bar;
use PinkCrab\DB_Migration\Tests\Stubs\Stub_Migration_Foo;
use PinkCrab\Table_Builder\Engines\WPDB_DB_Delta\DB_Delta_Engine;

class Test_Intergration_Tests extends WP_UnitTestCase {

	/** @var \wpdb */
	protected $wpdb;

	/** @var \PinkCrab\Table_Builder\Builder */
	protected $wpdb_builder;

	public function setup(): void {
		parent::setUp();
		global $wpdb;

		$this->wpdb = $wpdb;

		$this->wpdb_builder = new Builder( new DB_Delta_Engine( $wpdb ) );
	}


	/** @testdox [INT] Migrations defined in the manager should be created when called. If called again, the tables should not be created again (even if they have been removed from the database) */
	public function test_can_create_tables(): void {
		// Setup.
		$manager       = new Migration_Manager( $this->wpdb_builder, $this->wpdb, 'test_can_create_tables' );
		$foo_migration = new Stub_Migration_Foo;
		$bar_migration = new Stub_Migration_Bar;
		$manager->add_migration( $foo_migration );
		$manager->add_migration( $bar_migration );

		// Create table.
		$manager->create_tables();

		// Check foo table created.
		$foo_details = $this->wpdb->get_results( "SHOW COLUMNS FROM {$foo_migration->get_table_name()};" );
		$this->assertCount( 2, $foo_details );

		// Check foo table has fields.
		$this->assertNotEmpty(
			array_filter(
				$foo_details,
				function( \stdClass $col ): bool {
					return $col->Field === 'foo';
				}
			)
		);
		$this->assertNotEmpty(
			array_filter(
				$foo_details,
				function( \stdClass $col ): bool {
					return $col->Field === 'number';
				}
			)
		);

		// Check bar table created.
		$bar_details = $this->wpdb->get_results( "SHOW COLUMNS FROM {$bar_migration->get_table_name()};" );
		$this->assertCount( 1, $bar_details );

		// Check bar table has field.
		$this->assertNotEmpty(
			array_filter(
				$bar_details,
				function( \stdClass $col ): bool {
					return $col->Field === 'bar';
				}
			)
		);

		// Drop tables
		$this->wpdb->get_results( "DROP TABLE IF EXISTS {$foo_migration->get_table_name()};" );
		$this->wpdb->get_results( "DROP TABLE IF EXISTS {$bar_migration->get_table_name()};" );

		// Attmept the create again
		$manager->create_tables();
		$this->wpdb->suppress_errors();

		$foo_details_2 = $this->wpdb->get_results( "SHOW COLUMNS FROM {$foo_migration->get_table_name()};" );
		$bar_details_2 = $this->wpdb->get_results( "SHOW COLUMNS FROM {$bar_migration->get_table_name()};" );
		$this->assertEmpty( $foo_details_2 );
		$this->assertEmpty( $bar_details_2 );

		// Allow errors
		$this->wpdb->suppress_errors( false );
	}

	/** @testdox [INT] Any migrations which have seed data should see that data added to the table. Once seed data has been added to the database it should not be possible to add it again. */
	public function test_can_seed_table(): void {
		// Setup.
		$manager       = new Migration_Manager( $this->wpdb_builder, $this->wpdb, 'test_can_seed_table' );
		$foo_migration = new Stub_Migration_Foo;

		$manager->add_migration( $foo_migration )
			->create_tables()
			->seed_tables();

		// Check the seed data has been added (2 rows)
		$seeded_data = $this->wpdb->get_results( "SELECT * FROM {$foo_migration->get_table_name()};" );
		$this->assertCount( 2, $seeded_data );
		$this->assertNotEmpty(
			array_filter(
				$seeded_data,
				function( \stdClass $row ): bool {
					return $row->foo === 'text1' && $row->number === '1';
				}
			)
		);
		$this->assertNotEmpty(
			array_filter(
				$seeded_data,
				function( \stdClass $row ): bool {
					return $row->foo === 'text2' && $row->number === '2';
				}
			)
		);

		// Truncate the table and run seeder again.
		$this->wpdb->get_results( "TRUNCATE TABLE {$foo_migration->get_table_name()};" );
		$manager->seed_tables();

		$seeded_data2 = $this->wpdb->get_results( "SELECT * FROM {$foo_migration->get_table_name()};" );
		$this->assertCount( 0, $seeded_data2 );
	}

    /** @testdox [INT] Migtations defined in the migration manager, can be dropped via the manager. */
	public function test_drop_tables(): void {
		// Setup.
		$manager       = new Migration_Manager( $this->wpdb_builder, $this->wpdb, 'test_can_drop_tables' );
		$foo_migration = new Stub_Migration_Foo;
		$bar_migration = new Stub_Migration_Bar;
		$manager->add_migration( $foo_migration );
		$manager->add_migration( $bar_migration );

		// Create table.
		$manager->create_tables();

		// If we dont have table, abort test.
		$foo_details = $this->wpdb->get_results( "SHOW COLUMNS FROM {$foo_migration->get_table_name()};" );
		$bar_details = $this->wpdb->get_results( "SHOW COLUMNS FROM {$bar_migration->get_table_name()};" );
		if ( empty( $foo_details ) || empty( $bar_details ) ) {
            $this->fail('Failed to create either Foo or Bar table in drop table test, could not finish test.');
		}

		// Drop the tables and check the no longer exist.
		$manager->drop_tables();

		$this->wpdb->suppress_errors();

		$foo_details_2 = $this->wpdb->get_results( "SHOW COLUMNS FROM {$foo_migration->get_table_name()};" );
		$bar_details_2 = $this->wpdb->get_results( "SHOW COLUMNS FROM {$bar_migration->get_table_name()};" );
        $this->assertEmpty( $foo_details_2 );
		$this->assertEmpty( $bar_details_2 );

		// Allow errors
		$this->wpdb->suppress_errors( false );
	}
}
