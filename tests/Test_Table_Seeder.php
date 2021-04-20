<?php

declare(strict_types=1);

/**
 * Tests for the Migration seeder.
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests;

use wpdb;
use WP_UnitTestCase;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\DB_Migration\Migration_Seeder;
use PinkCrab\DB_Migration\Migration_Exception;
use PinkCrab\DB_Migration\Tests\Stubs\Stub_Migration_Foo;

class Test_Migration_Seeder extends WP_UnitTestCase {

	/** Returns a mocked version of the table seeder. */
	public function seeder_provider(): Migration_Seeder {
		return new Migration_Seeder( $this->createMock( wpdb::class ) );
	}

	/** @testdox It should be possible to use any valid string type when getting the format placeholder for wpdb prepare. */
	public function test_column_type_format_strings(): void {
		$mock_seeder = $this->seeder_provider();

		$strings = array(
			'CHAR',
			'VARCHAR',
			'BINARY',
			'VARBINARY',
			'TINYBLOB',
			'TINYTEXT',
			'TEXT',
			'BLOB',
			'MEDIUMTEXT',
			'MEDIUMBLOB',
			'LONGTEXT',
			'LONGBLOB',
			'DATE',
			'DATETIME',
			'TIMESTAMP',
			'TIME',
			'YEAR',
		);

		foreach ( $strings as $type ) {
			$this->assertEquals( '%s', Objects::invoke_method( $mock_seeder, 'column_type_format', array( $type ) ) );
		}
	}

	/** @testdox It should be possible to use any valid int type when getting the format placeholder for wpdb prepare. */
	public function test_column_type_format_int(): void {
		$mock_seeder = $this->seeder_provider();

		$ints = array(
			'BIT',
			'TINYINT',
			'BOOL',
			'BOOLEAN',
			'SMALLINT',
			'MEDIUMINT',
			'INT',
			'INTEGER',
			'BIGINT',
		);

		foreach ( $ints as $type ) {
			$this->assertEquals( '%d', Objects::invoke_method( $mock_seeder, 'column_type_format', array( $type ) ) );
		}

	}

	/** @testdox It should be possible to use any valid float type when getting the format placeholder for wpdb prepare. */
	public function test_column_type_format_float(): void {
		$mock_seeder = $this->seeder_provider();

		$floats = array(
			'FLOAT',
			'DOUBLE',
			'DOUBLE PRECISION',
			'DECIMAL',
			'DEC',
		);

		foreach ( $floats as $type ) {
			$this->assertEquals( '%f', Objects::invoke_method( $mock_seeder, 'column_type_format', array( $type ) ) );
		}
	}

	/** @testdox It should be possible to use any valid string type when getting the format placeholder for wpdb prepare. And the type is not defined, catches Timestamp and DateTime types. */
	public function test_column_type_format_fallback(): void {
		$mock_seeder = $this->seeder_provider();

		$others = array( 'TIMESTAMP', 'DATE', 'BANANA' );

		foreach ( $others as $type ) {
			$this->assertEquals( '%s', Objects::invoke_method( $mock_seeder, 'column_type_format', array( $type ) ) );
		}
	}

	/** @testdox When inserting seed data, any errors from wpbd should result in an error. */
	public function test_throws_exception_if_fails_to_create_seed(): void {
		$wpdb_with_error             = $this->createMock( wpdb::class );
		$wpdb_with_error->last_error = 'MOCK ERROR';
		$wpdb_with_error->method( 'insert' )->willReturn( 1 );

		$seeder = new Migration_Seeder( $wpdb_with_error );
		$foo    = new Stub_Migration_Foo();

		$this->expectExceptionCode( 2 );
		$this->expectException( Migration_Exception::class );

		$seeder->seed( $foo->get_schema(), $foo->get_seeds() );
	}

	/** @testdox When inserting seed data, any column in seed data which is not defined in schema should result in an error */
	public function test_throws_exception_getting_none_existing_column_from_schema(): void {
		$mock_seeder = $this->seeder_provider();
		$foo_schema  = ( new Stub_Migration_Foo() )->get_schema();

		$this->expectExceptionCode( 1 );
		$this->expectException( Migration_Exception::class );

		Objects::invoke_method( $mock_seeder, 'column_type', array( $foo_schema, 'FAKE_COLUMN' ) );
	}
}
