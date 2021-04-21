<?php

declare(strict_types=1);

/**
 * Unit tests for the Log\Migration class
 *
 * @since 0.3.0
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests\Log;

use WP_UnitTestCase;
use DateTimeImmutable;
use PinkCrab\Table_Builder\Schema;
use PinkCrab\DB_Migration\Log\Migration_Log as Log;

class Test_Migration_Log extends WP_UnitTestCase {

	/** @testdox It should be possible to get the table name, hash, seeded status and the dates created/updated, from a Migration log. */
	public function test_getters(): void {

		$now = new DateTimeImmutable();

		$log = new Log( 'table', 'hash', true, $now, $now );

		$this->assertSame( 'table', $log->table_name() );
		$this->assertSame( 'hash', $log->schema_hash() );
		$this->assertSame( true, $log->is_seeded() );
		$this->assertSame( $now, $log->created_on() );
		$this->assertSame( $now, $log->updated_on() );
	}

	/** @testdox It should be possible to create a migration log from a schema definition. */
	public function test_new_from_schema(): void {
		$schema = new Schema(
			'table',
			function( Schema $schema ): void {
				$schema->column( 'id' )->int( 1 );
			}
		);

		$export               = array(
			'name'         => $schema->get_table_name(),
			'columns'      => $schema->get_columns(),
			'indexes'      => $schema->get_indexes(),
			'foreign_keys' => $schema->get_foreign_keys(),
		);
		$expected_schema_hash = md5( \serialize( $export ) );

		$log = Log::new_from_schema( $schema );

		$this->assertSame( 'table', $log->table_name() );
		$this->assertSame( $expected_schema_hash, $log->schema_hash() );
		$this->assertSame( false, $log->is_seeded() );
	}

	public function test_update_migration(): void {
		$updated_time = '2011-01-01 15:03:01';

		$schema_a    = new Schema(
			'table',
			function( Schema $schema ): void {
				$schema->column( 'id' )->int( 1 );
			}
		);
		$initial_log = Log::new_from_schema( $schema_a );

		$schema_b = new Schema(
			'table',
			function( Schema $schema ): void {
				$schema->column( 'id' )->int( 1 );
				$schema->column( 'other' )->text();
			}
		);

		$update_log = $initial_log->as_updated( $schema_b, new DateTimeImmutable( $updated_time ) );

		$this->assertSame( 'table', $update_log->table_name() );
		$this->assertSame( $updated_time, $update_log->updated_on()->format( 'Y-m-d H:i:s' ) );

		// Ensure immuteable
		$this->assertNotSame( $schema_a, $schema_b );
	}

	/** @testdox It should be possible to create a new log which denotes the migration has been seeded.*/
	public function test_as_seeded(): void {
		$schema_a    = new Schema(
			'table',
			function( Schema $schema ): void {
				$schema->column( 'id' )->int( 1 );
			}
		);
		$initial_log = Log::new_from_schema( $schema_a );
		$this->assertSame( false, $initial_log->is_seeded() );

		$updated_time = '2011-01-01 15:03:01';
		$update_log   = $initial_log->as_seeded( new DateTimeImmutable( $updated_time ) );

		$this->assertSame( 'table', $update_log->table_name() );
		$this->assertSame( $updated_time, $update_log->updated_on()->format( 'Y-m-d H:i:s' ) );
		$this->assertSame( true, $update_log->is_seeded() );

		// Ensure immuteable
		$this->assertNotSame( $initial_log, $update_log );
	}
}
