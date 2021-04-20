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
use PinkCrab\Table_Builder\Builders\DB_Delta;
use PinkCrab\DB_Migration\Tests\Stubs\Stub_Migration_Bar;
use PinkCrab\DB_Migration\Tests\Stubs\Stub_Migration_Foo;
use PinkCrab\Table_Builder\Engines\WPDB_DB_Delta\DB_Delta_Engine;

class Test_Intergration_Tests extends WP_UnitTestCase {

	/** @testdox [INT] Migrations defined in the manager should be created when called. If called again, the tables should not be created again (even if they have been removed from the database) */
	public function test_can_create_tables(): void {
		// Setup.
		global $wpdb;
		$builder       = new Builder( new DB_Delta_Engine( $wpdb ) );
		$manager       = new Migration_Manager( $builder, $wpdb, 'test_can_create_tables' );
		$foo_migration = new Stub_Migration_Foo;
		$bar_migration = new Stub_Migration_Bar;
		$manager->add_migration( $foo_migration );
		$manager->add_migration( $bar_migration );

		// Create table.
		$manager->create_tables();

		// Check foo table created.
		$foo_details = $wpdb->get_results( "SHOW COLUMNS FROM {$foo_migration->get_table_name()};" );
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
		$bar_details = $wpdb->get_results( "SHOW COLUMNS FROM {$bar_migration->get_table_name()};" );
		$this->assertCount( 1, $bar_details );

		// Check bar table has fields.
		$this->assertNotEmpty(
			array_filter(
				$bar_details,
				function( \stdClass $col ): bool {
					return $col->Field === 'bar';
				}
			)
		);

		// Drop tables and attempt again (should not recreate them)
		$wpdb->get_results( "DROP TABLE IF EXISTS {$foo_migration->get_table_name()};" );
		$wpdb->get_results( "DROP TABLE IF EXISTS {$bar_migration->get_table_name()};" );

		// Attmept the create again
		$manager->create_tables();
		$wpdb->suppress_errors();

		$foo_details_2 = $wpdb->get_results( "SHOW COLUMNS FROM {$foo_migration->get_table_name()};" );
		$bar_details_2 = $wpdb->get_results( "SHOW COLUMNS FROM {$bar_migration->get_table_name()};" );
		$this->assertEmpty( $foo_details_2 );
		$this->assertEmpty( $bar_details_2 );

		// Allow errors
		$wpdb->suppress_errors( \false );
	}
}
