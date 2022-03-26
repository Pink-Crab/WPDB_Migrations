<?php

declare(strict_types=1);

/**
 * Unit tests for the Migration Log manager
 *
 * @since 0.3.0
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests\Log;

use WP_UnitTestCase;
use DateTimeImmutable;
use InvalidArgumentException;
use PinkCrab\Table_Builder\Schema;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\DB_Migration\Log\Migration;
use PinkCrab\DB_Migration\Log\Migration_Log;
use PinkCrab\DB_Migration\Log\Migration_Log as Log;
use PinkCrab\DB_Migration\Log\Migration_Log_Manager;
use PinkCrab\DB_Migration\Tests\Stubs\Schema_Provider;

class Test_Migaration_Log_Manager extends WP_UnitTestCase {

	/** @testdox It should be possible to use a custom option key for all migration details, with a defaut if not set. */
	public function test_can_use_custom_log_key(): void {
		$default_logger = new Migration_Log_Manager();
		$this->assertEquals(
			'pinkcrab_migration_log',
			Objects::get_property( $default_logger, 'option_key' )
		);

		$custom_logger = new Migration_Log_Manager( 'custom' );
		$this->assertEquals(
			'custom',
			Objects::get_property( $custom_logger, 'option_key' )
		);
	}

	/** @testdox When the Migration log is constrcuted, it should be populated with all existing migration details. */
	public function test_prepopulated_migration_log_data_on_construct(): void {

		// Create the mock log.
		$mock_log = $this->createMock( Migration_Log::class );
		$mock_log->method( 'schema_hash' )->willReturn( 'mock_hash' );

		// Add to options and create Migratuion Log.
		$custom = array( 'key' => $mock_log );
		add_option( 'custom_log', \serialize( $custom ) );
		$custom_logger = new Migration_Log_Manager( 'custom_log' );

		// Check same instance.
		$this->assertSame(
			$mock_log->schema_hash(),
			Objects::get_property( $custom_logger, 'migration_details' )['key']->schema_hash()
		);
	}

	/** @testdox If no migrations have been logged, the internal state should be empty after instantiated. */
	public function test_set_blank_array_to_migration_details_if_unset() {
		$custom_logger = new Migration_Log_Manager( 'custom_log' );
		$this->assertSame( array(), Objects::get_property( $custom_logger, 'migration_details' ) );
	}

	/** @testdox If the cached state of migrations is invalid, an error should be produced while tryign to construct the Migaration log. */
	public function test_throw_exception_if_internal_migration_log_doesnt_unserialise_correctly(): void {
		$this->expectException( InvalidArgumentException::class );

		add_option( 'invalid_custom_log', 'Im not a valid serlised bit of data, im just a string.' );
		$invalid_custom_log = new Migration_Log_Manager( 'invalid_custom_log' );
	}

	/** @testdox It should be possible to check is a table has already been migrated. */
	public function test_has_migration() {
		// Mock the options table value.
		$custom = array( 'mock_migration' => $this->createMock( Migration_Log::class ) );
		add_option( 'custom_log', \serialize( $custom ) );

		$custom_logger = new Migration_Log_Manager( 'custom_log' );

		$this->assertTrue( $custom_logger->has_migration( new Schema( 'mock_migration' ) ) );
	}

	/** @testdox It should be possible to check if a table name has already been seeded */
	public function test_is_seeded(): void {
		// Create the mock log.
		$mock_log = $this->createMock( Migration_Log::class );
		$mock_log->method( 'is_seeded' )->willReturn( true );

		// Add to options and create Migratuion Log.
		$custom = array( 'seeded_mock' => $mock_log );
		add_option( 'custom_log', \serialize( $custom ) );
		$custom_logger = new Migration_Log_Manager( 'custom_log' );

		$this->assertTrue( $custom_logger->is_seeded( new Schema( 'seeded_mock' ) ) );

		// Doesnt exists, so should be false.
		$this->assertFalse( $custom_logger->is_seeded( new Schema( 'unset_mock' ) ) );
	}

	/** @testdox When checking if a schema has changed, it should return false if the schema has not been logged. */
	public function test_check_hash_fails_if_table_not_already_created(): void {
		$schema = Schema_Provider::migration_log_schema();
		add_option(
			'test_check_hash_fails_if_table_not_already_created',
			\serialize(
				array( 'test_table' => Log::new_from_schema( $schema ) )
			)
		);

		$log = new Migration_Log_Manager( 'test_check_hash_fails_if_table_not_already_created' );
		$this->assertTrue( $log->can_migrate( new Schema( 'Failed' ) ) );
	}

	/** @testdox If attempting to upsert a migratuion with new details, the new schema details should be updated in the migration log only if changed. */
	public function test_upsert_migration_edit(): void {
		// Set initial
		$schema = Schema_Provider::migration_log_schema();
		add_option(
			'test_upsert_migration',
			\serialize(
				array( 'test_table' => Log::new_from_schema( $schema ) )
			)
		);
		$log = new Migration_Log_Manager( 'test_upsert_migration' );

		$inital_updated_on  = $log->get_migration( $schema )->updated_on();
		$inital_schema_hash = $log->get_migration( $schema )->schema_hash();

		// Upsert the new updated schema.
		$updated_schema = clone $schema;
		$updated_schema->column( 'upserted_update' )->int( 11 )->default( '1' );
		$log->upsert_migration( $updated_schema );

		// Check the updated datetime differs
		$new_updated_on  = $log->get_migration( $schema )->updated_on();
		$new_schema_hash = $log->get_migration( $schema )->schema_hash();

		$this->assertNotEquals( $inital_updated_on, $new_updated_on );
		$this->assertNotEquals( $inital_schema_hash, $new_schema_hash );
	}

	/** @testdox If attempting to upsert a schema which is not been added to the log, will be added as new. */
	public function test_upsert_migration_create() {
		$log = new Migration_Log_Manager( 'test_upsert_min_migration' );

		// Ensure no log made
		$this->assertFalse( $log->has_migration( new Schema( 'test_table' ) ) );

		// Add log
		$schema = Schema_Provider::migration_log_schema();
		$log->upsert_migration( $schema );
		$this->assertTrue( $log->has_migration( new Schema( 'test_table' ) ) );
	}

	/** @testdox When a table has been seeded, it should be possible to mark it as seeded in the migration log. */
	public function test_mark_seeded(): void {
		$log    = new Migration_Log_Manager( 'test_upsert_min_migration' );
		$schema = Schema_Provider::migration_log_schema();
		$log->upsert_migration( $schema );

		$this->assertFalse( $log->get_migration( $schema )->is_seeded() );

		$log->mark_table_seeded( $schema );
		$this->assertTrue( $log->get_migration( $schema )->is_seeded() );
	}

	/** @testdox It should be possible to remove a migration from the migration log. */
	public function test_can_remove_migration_from_log() {
		$log    = new Migration_Log_Manager( 'test_upsert_min_migration' );
		$schema = Schema_Provider::migration_log_schema();
		$log->upsert_migration( $schema );
		$this->assertTrue( $log->has_migration( $schema ) );

		// Remove.
		$log->remove_migration( $schema );
		$this->assertFalse( $log->has_migration( $schema ) );
	}

	/** @testdox It should be possible to access the migration log key. */
	public function test_can_get_migration_log_key(): void {
		// With a set value.
		$log = new Migration_Log_Manager( 'test_can_get_migration_log_key' );
		$this->assertEquals( 'test_can_get_migration_log_key', $log->get_log_key() );
	}

	/** @testdox It should be possible to clear the log from the log manager. */
	public function test_can_clear_log(): void {
		$log    = new Migration_Log_Manager( 'test_can_clear_log' );
		$schema = Schema_Provider::migration_log_schema();
		$log->upsert_migration( $schema );

		$this->assertTrue( '' !== get_option( 'test_can_clear_log' ) );
		$log->clear_log();
		$this->assertNull( \get_option( 'test_can_clear_log', null ) );
	}
}
