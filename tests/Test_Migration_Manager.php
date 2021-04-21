<?php

declare(strict_types=1);

/**
 * Tests for the Migration manager.
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests;

use wpdb;
use Exception;
use WP_UnitTestCase;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\Table_Builder\Builder;
use PinkCrab\DB_Migration\Migration_Manager;
use PinkCrab\DB_Migration\Database_Migration;
use PinkCrab\DB_Migration\Migration_Exception;
use PinkCrab\DB_Migration\Tests\Stubs\Stub_Migration_Bar;
use PinkCrab\DB_Migration\Tests\Stubs\Stub_Migration_Foo;

class Test_Migration_Manager extends WP_UnitTestCase {


	/** Create a mock manager, uses mock version of builder and wpdb. */
	protected function mock_manager_provider( ?string $log_key = null ): Migration_Manager {

		// Mock builder to always return true for create and drop.
		$mock_builder = $this->createMock( Builder::class );
		$mock_builder->method( 'create_table' )
			 ->willReturn( true );

		$mock_builder->method( 'drop_table' )
			 ->willReturn( true );

		$mock_wpdb = $this->createMock( wpdb::class );
		$mock_wpdb->method( 'insert' )->willReturn( 1 );
		$mock_wpdb->insert_id = 24;

		return new Migration_Manager( $mock_builder, $mock_wpdb, $log_key );
	}

	/** @testdox When a migration manager is created it should be populated with its intenral classes */
	public function test_can_create_populated_manager(): void {
		$manager_with_default_log_key = $this->mock_manager_provider();
		$this->assertInstanceOf(
			Builder::class,
			Objects::get_property( $manager_with_default_log_key, 'builder' )
		);

		$this->assertInstanceOf(
			wpdb::class,
			Objects::get_property( $manager_with_default_log_key, 'wpdb' )
		);

		$log = Objects::get_property( $manager_with_default_log_key, 'migration_log' );
		$this->assertEquals(
			'pinkcrab_migration_log',
			Objects::get_property( $log, 'option_key' )
		);

		$manager_with_custom_log_key = $this->mock_manager_provider( 'custom_log_key' );
		$log                         = Objects::get_property( $manager_with_custom_log_key, 'migration_log' );
		$this->assertEquals(
			'custom_log_key',
			Objects::get_property( $log, 'option_key' )
		);
	}

	/** @testdox It should be possible to add migratiosn to the manager and export them as an array of migrations with tablename as key. */
	public function test_add_and_get_migration(): void {
		$manager = $this->mock_manager_provider();

		$migration = $this->createMock( Database_Migration::class );
		$migration->method( 'get_table_name' )->willReturn( 'mock_table' );

		$manager->add_migration( $migration );
		$export = $manager->get_migrations();
		$this->assertNotEmpty( $export );
		$this->assertArrayHasKey( 'mock_table', $export );
		$this->assertInstanceOf( Database_Migration::class, $export['mock_table'] );
	}

	/** @testdox It should be possible to create tabes based on there migration objects and also manually choose to ignore some based on table name. */
	public function test_can_create_tables() {
		$manager = $this->mock_manager_provider( 'bar' );

		$foo_migration = new Stub_Migration_Foo();
		$bar_migration = new Stub_Migration_Bar();

		$manager->add_migration( $foo_migration );
		$manager->add_migration( $bar_migration );

		// Skip bar migration
		$manager->create_tables( $bar_migration->get_table_name() );

		// Get log and check all are set.
		$log = $manager->migation_log();
		$this->assertTrue( $log->has_migration( $foo_migration->get_schema() ) );
		$this->assertFalse( $log->has_migration( $bar_migration->get_schema() ) );
	}

	/** @testdox It should be possible to seed a table once it has been created and not already seeded. */
	public function test_can_seed_tables() {
		$manager       = $this->mock_manager_provider( 'seed' );
		$foo_migration = new Stub_Migration_Foo();
		$manager->add_migration( $foo_migration );

		$manager->create_tables();
		$manager->seed_tables();

		$log = $manager->migation_log();
		$this->assertTrue( $log->is_seeded( $foo_migration->get_schema() ) );
	}

	/** @testdox It should be possible to drop a table and have its record removed from the log. */
	public function test_drop_tables(): void {
		$manager       = $this->mock_manager_provider( 'drop_tables' );
		$foo_migration = new Stub_Migration_Foo();
		$manager->add_migration( $foo_migration );

		$manager->create_tables();
		// Check table is marked as created.
		if ( ! $manager->migation_log()->has_migration( $foo_migration->get_schema() ) ) {
			$this->fail( 'Migration wasnt marked as created, so cant test if removed. FAILED' );
		}

		$manager->drop_tables();
		$this->assertFalse( $manager->migation_log()->has_migration( $foo_migration->get_schema() ) );
	}

	/** @testdox It should be possible to add table names to be ignored when dropping tables */
	public function test_can_skip_tables_when_dropping(): void {
		$manager       = $this->mock_manager_provider( 'drop_tables' );
		$foo_migration = new Stub_Migration_Foo();
		$bar_migration = new Stub_Migration_Bar();
		$manager->add_migration( $foo_migration );
		$manager->add_migration( $bar_migration );
		$manager->create_tables();

		$manager->drop_tables( $bar_migration->get_table_name() );

		$log = $manager->migation_log();
		$this->assertTrue( $log->has_migration( $bar_migration->get_schema() ) );
		$this->assertFalse( $log->has_migration( $foo_migration->get_schema() ) );
	}

	/** @testdox When dropping a table and there is a failure and error should be produced. */
	public function test_throws_exception_failed_drop_table(): void {
		$mock_builder = $this->createMock( Builder::class );
		$mock_builder->method( 'drop_table' )
			 ->willReturn( false );

		$mock_wpdb             = $this->createMock( wpdb::class );
		$mock_wpdb->last_error = 'MOCK ERROR';
		$mock_wpdb->method( 'get_results' )->willReturn( 1 );

		$manager       = new Migration_Manager( $mock_builder, $mock_wpdb, 'log_key' );
		$foo_migration = new Stub_Migration_Foo();
		$manager->add_migration( $foo_migration );
		$manager->create_tables();

		$this->expectExceptionCode( 3 );
		$this->getExpectedException( Migration_Exception::class );
		$manager->drop_tables();
	}
}
